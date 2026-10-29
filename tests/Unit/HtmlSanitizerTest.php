<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_quill_bullets_become_an_unordered_list(): void
    {
        $html = '<ol><li data-list="bullet">Satu</li><li data-list="bullet">Dua</li></ol>';

        $this->assertSame('<ul><li>Satu</li><li>Dua</li></ul>', HtmlSanitizer::clean($html));
    }

    public function test_numbered_lists_survive(): void
    {
        $html = '<ol><li data-list="ordered">Satu</li><li data-list="ordered">Dua</li></ol>';

        $this->assertSame('<ol><li>Satu</li><li>Dua</li></ol>', HtmlSanitizer::clean($html));
    }

    public function test_scripts_and_inline_styles_are_removed(): void
    {
        $html = '<p style="color:red" class="x">Aman<script>alert(1)</script></p>';

        $this->assertSame('<p>Aman</p>', HtmlSanitizer::clean($html));
    }

    public function test_images_inside_the_text_are_removed(): void
    {
        $html = '<p>Sebelum <img src="data:image/png;base64,AAAA"> sesudah</p>';

        $this->assertSame('<p>Sebelum  sesudah</p>', HtmlSanitizer::clean($html));
    }

    public function test_first_level_headings_become_section_headings(): void
    {
        $html = '<h1>Judul besar</h1>';

        $this->assertSame('<h2>Judul besar</h2>', HtmlSanitizer::clean($html));
    }

    public function test_javascript_links_lose_their_target(): void
    {
        $html = '<p><a href="javascript:alert(1)">Klik</a></p>';

        $this->assertSame('<p><a>Klik</a></p>', HtmlSanitizer::clean($html));
    }

    public function test_safe_links_get_rel_and_target(): void
    {
        $html = '<p><a href="https://avenma.id" onclick="x()">Situs</a></p>';

        $this->assertSame(
            '<p><a href="https://avenma.id" rel="noopener" target="_blank">Situs</a></p>',
            HtmlSanitizer::clean($html),
        );
    }

    public function test_empty_paragraphs_are_dropped(): void
    {
        $html = '<p>Isi</p><p><br></p><p> </p>';

        $this->assertSame('<p>Isi</p>', HtmlSanitizer::clean($html));
    }

    public function test_unknown_wrappers_are_unwrapped_not_deleted(): void
    {
        $html = '<div><span>Teks penting</span></div>';

        $this->assertSame('Teks penting', HtmlSanitizer::clean($html));
    }

    public function test_bold_and_lists_are_kept(): void
    {
        $html = '<p><strong>Tebal</strong> dan <em>miring</em></p><ul><li>Butir</li></ul>';

        $this->assertSame('<p><strong>Tebal</strong> dan <em>miring</em></p><ul><li>Butir</li></ul>', HtmlSanitizer::clean($html));
    }

    public function test_empty_input_stays_empty(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean('   '));
    }
}
