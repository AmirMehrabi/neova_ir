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
            ->assertSee('نرم افزار مدیریت پروژه برای تیم‌های کوچک')
            ->assertSee('رایگان در نسخه بتا شروع کنید')
            ->assertSee('امروز من')
            ->assertSee('برنامه‌ریزی و پیگیری پروژه')
            ->assertSee('پرسش‌های نرم افزار مدیریت پروژه')
            ->assertSee('آیا نئووا رایگان است؟')
            ->assertSee(route('auth'), false);
    }
}
