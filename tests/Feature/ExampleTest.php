<?php

namespace Tests\Feature;

use Database\Seeders\PageContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(PageContentSeeder::class);

        $this->get('/')->assertOk();
    }

    public function test_design_system_page_renders(): void
    {
        $this->get('/design-system')->assertOk();
    }
}
