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
            ->assertSee('کارها را از توی چت بیرون بیاورید.')
            ->assertSee('رایگان در نسخه بتا شروع کنید')
            ->assertSee('امروز من')
            ->assertSee('سه بخش، یک تصویر مشترک')
            ->assertSee('شاید این‌ها را بپرسید.')
            ->assertSee('نئووا رایگان است؟')
            ->assertSee(route('auth'), false);
    }
}
