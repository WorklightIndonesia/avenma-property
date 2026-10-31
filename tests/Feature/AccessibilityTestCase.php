<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Listing;
use App\Models\User;
use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Structure every screen must keep: one h1, a language, a skip link, and an
 * accessible name for each control. Layout and contrast need eyes; these do not.
 */
abstract class AccessibilityTestCase extends TestCase
{
    use RefreshDatabase;

    protected function assertAccessible(string $url): void
    {
        $response = $this->get($url)->assertOk();
        $xpath = $this->xpath($response->getContent());

        $this->assertSame(1, $xpath->query('//h1')->length, "{$url}: exactly one h1");
        $this->assertSame('id', $xpath->evaluate('string(//html/@lang)'), "{$url}: html lang");

        $this->assertGreaterThan(0, $xpath->query('//a[@href="#main"]')->length, "{$url}: skip link");
        $this->assertGreaterThan(0, $xpath->query('//*[@id="main"]')->length, "{$url}: skip target");

        foreach ($xpath->query('//img[not(@alt)]') as $image) {
            $this->fail("{$url}: image without alt — ".$image->getAttribute('src'));
        }

        foreach ($this->namedControls($xpath) as $node) {
            $this->assertNotSame('', $this->accessibleName($node), "{$url}: unnamed <{$node->nodeName}>");
        }
    }

    /**
     * Controls a person can reach: no hidden fields, no template placeholders,
     * no fallback markup inside noscript.
     *
     * @return list<\DOMElement>
     */
    private function namedControls(\DOMXPath $xpath): array
    {
        $query = '//button | //a[@href] | //input[not(@type="hidden")] | //select | //textarea | //*[@tabindex]';
        $nodes = [];

        foreach ($xpath->query($query) as $node) {
            if (! $node instanceof \DOMElement) {
                continue;
            }

            $parent = $node->parentNode;

            while ($parent instanceof \DOMElement) {
                if (in_array(strtolower($parent->nodeName), ['template', 'noscript'], true)) {
                    continue 2;
                }

                $parent = $parent->parentNode;
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    private function accessibleName(\DOMElement $node): string
    {
        $name = trim($node->getAttribute('aria-label'));

        if ($name === '') {
            $name = trim($node->getAttribute('title'));
        }

        if ($name === '') {
            $name = trim(preg_replace('/\s+/', ' ', $node->textContent));
        }

        if ($name === '') {
            $id = $node->getAttribute('id');

            if ($id !== '') {
                $document = $node->ownerDocument;
                foreach ($document->getElementsByTagName('label') as $label) {
                    if ($label->getAttribute('for') === $id) {
                        $name = trim($label->textContent);
                        break;
                    }
                }
            }
        }

        if ($name === '') {
            $parent = $node->parentNode;

            while ($parent instanceof \DOMElement) {
                if (strtolower($parent->nodeName) === 'label') {
                    $name = trim(preg_replace('/\s+/', ' ', $parent->textContent));
                    break;
                }

                $parent = $parent->parentNode;
            }
        }

        return $name;
    }

    protected function xpath(string $html): \DOMXPath
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new \DOMXPath($document);
    }

    protected function seedContent(): void
    {
        $this->seed(PageContentSeeder::class);

        $listing = Listing::create([
            'location_id' => $this->location()->id,
            'title' => 'Rumah Uji',
            'slug' => 'rumah-uji',
            'description' => 'Deskripsi uji.',
            'listing_type' => 'primary',
            'property_type' => 'rumah_siap_huni',
            'status' => 'dijual',
            'price' => 1_000_000_000,
        ]);

        $listing->images()->create([
            'path' => 'images/properties/foto-1.webp',
            'alt' => 'Tampak depan',
            'sort_order' => 0,
        ]);

        BlogPost::create([
            'created_by' => User::factory()->create(['role' => User::ROLE_AGEN, 'phone' => '089630999996'])->id,
            'title' => 'Artikel Uji',
            'slug' => 'artikel-uji',
            'excerpt' => 'Ringkasan.',
            'content' => '<p>Isi.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }
}
