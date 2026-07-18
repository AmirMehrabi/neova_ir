@extends('layouts.app')
@section('body')
<div class="auth-page">
    <div class="auth-shell auth-shell--profile">
        <a href="{{ url('/') }}" class="auth-brand" aria-label="صفحه اصلی نئووا">
            <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا">
        </a>

        <div class="auth-heading">
            <p class="auth-eyebrow">یک قدم تا فضای کار شما</p>
            <h1>اطلاعات‌تان را کامل کنید.</h1>
            <p>این اطلاعات فقط برای ساختن حساب کاربری و شروع کار شماست.</p>
        </div>

        <div class="auth-card">
            <form action="{{ route('auth.profile.store') }}" method="POST" class="auth-form">
                @csrf

                @if (session('success'))
                    <div class="auth-alert auth-alert--success">{{ session('success') }}</div>
                @endif

                <div class="auth-field">
                    <label for="first_name">نام</label>
                    <input id="first_name" name="first_name" type="text" required value="{{ old('first_name') }}" class="auth-input" placeholder="نام خود را وارد کنید" autocomplete="given-name">
                    @error('first_name')<p class="auth-error">{{ $message }}</p>@enderror
                </div>

                <div class="auth-field">
                    <label for="last_name">نام خانوادگی</label>
                    <input id="last_name" name="last_name" type="text" required value="{{ old('last_name') }}" class="auth-input" placeholder="نام خانوادگی خود را وارد کنید" autocomplete="family-name">
                    @error('last_name')<p class="auth-error">{{ $message }}</p>@enderror
                </div>

                <div class="auth-field">
                    <label for="national_code">کد ملی <span>(اختیاری)</span></label>
                    <input id="national_code" name="national_code" type="text" maxlength="10" value="{{ old('national_code') }}" class="auth-input" placeholder="۱۲۳۴۵۶۷۸۹۰" dir="ltr" inputmode="numeric">
                    @error('national_code')<p class="auth-error">{{ $message }}</p>@enderror
                </div>

                <div class="auth-field">
                    <label for="email">ایمیل <span>(اختیاری)</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" class="auth-input" placeholder="email@example.com" dir="ltr" autocomplete="email">
                    <p class="auth-hint">ایمیل برای ارسال اعلان‌ها استفاده می‌شود.</p>
                    @error('email')<p class="auth-error">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="auth-submit">تکمیل و ورود <span aria-hidden="true">←</span></button>
            </form>
        </div>
    </div>
</div>
@endsection
