<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_public_homepage_is_available(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('همه می‌دانند قدم بعدی چیست.')
            ->assertSee('رایگان شروع کنید')
            ->assertSee('تیم محصول')
            ->assertSee('پیام نئووا')
            ->assertSee('سؤال‌های متداول')
            ->assertSee(route('auth'), false);
    }
}
