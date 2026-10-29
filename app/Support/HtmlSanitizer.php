<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Turns whatever Quill (or a paste from Word) produced into the small set of
 * tags the design allows. Runs on save, so the database only ever holds clean
 * HTML and views can render it without guessing.
 */
class HtmlSanitizer
{
    private const ALLOWED = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h2', 'h3', 'ul', 'ol', 'li', 'a', 'blockquote'];

    /** Removed with their contents; unwrapping them would keep the payload or the base64 blob. */
    private const DROPPED = ['script', 'style', 'iframe', 'object', 'embed', 'img', 'video', 'audio', 'form', 'input', 'table', 'svg', 'math', 'link', 'meta'];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementsByTagName('div')->item(0);

        if ($root === null) {
            return '';
        }

        self::bulletsToUnordered($root);
        self::walk($root, $document);
        self::dropEmpty($root);

        $out = '';

        foreach ($root->childNodes as $child) {
            $out .= $document->saveHTML($child);
        }

        return trim($out);
    }

    /**
     * Quill 2 keeps both list types in an <ol> and tells them apart with
     * data-list. Without this the bullets would render as numbers.
     */
    private static function bulletsToUnordered(DOMElement $root): void
    {
        $document = $root->ownerDocument;

        foreach (iterator_to_array($root->getElementsByTagName('ol')) as $list) {
            $items = iterator_to_array($list->childNodes);

            $bullets = $items !== [] && array_filter($items, fn (DOMNode $item) => $item instanceof DOMElement
                && $item->tagName === 'li'
                && $item->getAttribute('data-list') === 'bullet') === $items;

            if (! $bullets) {
                continue;
            }

            $unordered = $document->createElement('ul');

            foreach ($items as $item) {
                $unordered->appendChild($item);
            }

            $list->parentNode->replaceChild($unordered, $list);
        }
    }

    private static function walk(DOMNode $node, DOMDocument $document): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROPPED, true)) {
                $node->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED, true)) {
                // A heading typed into a paste becomes a section heading, never
                // a second page title.
                if (in_array($tag, ['h1', 'h4', 'h5', 'h6'], true)) {
                    $child = self::rename($child, 'h2');
                    self::keepAttributes($child, 'h2');
                    self::walk($child, $document);

                    continue;
                }

                foreach (iterator_to_array($child->childNodes) as $grandchild) {
                    $node->insertBefore($grandchild, $child);
                }

                $node->removeChild($child);
                self::walk($node, $document);

                continue;
            }

            self::keepAttributes($child, $tag);
            self::walk($child, $document);
        }
    }

    private static function keepAttributes(DOMElement $element, string $tag): void
    {
        $href = $tag === 'a' ? $element->getAttribute('href') : '';

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttribute($attribute->name);
        }

        if ($tag !== 'a') {
            return;
        }

        if (preg_match('/^(https?:|mailto:|tel:)/i', $href) === 1) {
            $element->setAttribute('href', $href);
            $element->setAttribute('rel', 'noopener');
            $element->setAttribute('target', '_blank');
        }
    }

    private static function rename(DOMElement $element, string $tag): DOMElement
    {
        $replacement = $element->ownerDocument->createElement($tag);

        foreach (iterator_to_array($element->childNodes) as $child) {
            $replacement->appendChild($child);
        }

        $element->parentNode->replaceChild($replacement, $element);

        return $replacement;
    }

    private static function dropEmpty(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            self::dropEmpty($child);

            if (in_array(strtolower($child->tagName), ['p', 'h2', 'h3', 'li'], true)
                && trim($child->textContent) === '') {
                $node->removeChild($child);
            }
        }
    }
}
