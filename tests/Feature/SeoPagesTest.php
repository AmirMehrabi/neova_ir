<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoPagesTest extends TestCase
{
    public function test_every_configured_seo_page_is_public_and_has_unique_metadata(): void
    {
        foreach (config('seo_pages') as $slug => $page) {
            $this->get('/'.$slug)
                ->assertOk()
                ->assertSee('<title>'.$page['title'].'</title>', false)
                ->assertSee('<h1>'.$page['h1'].'</h1>', false)
                ->assertSee('rel="canonical"', false)
                ->assertSee('application/ld+json', false)
                ->assertSee('"@context":"https://schema.org"', false);
        }
    }

    public function test_sitemap_lists_homepage_and_every_seo_page(): void
    {
        $response = $this->get('/sitemap.xml')->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');

        foreach (array_keys(config('seo_pages')) as $slug) {
            $response->assertSee(url($slug), false);
        }
    }

    public function test_unknown_seo_page_is_not_found(): void
    {
        $this->get('/features/not-a-real-page')->assertNotFound();
    }
}
