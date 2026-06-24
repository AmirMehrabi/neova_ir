@extends('emails.layout')

@section('content')
    <h1 style="margin: 0 0 16px; font-size: 18px; font-weight: 700; color: #0b1b2b; line-height: 1.5;">
        تغییراتی در «{{ $project->name }}» اعمال شد
    </h1>

    <p style="margin: 0 0 8px; font-size: 14px; color: #5f6b73; line-height: 1.7;">
        سلام {{ $user->first_name ?? $user->name }}،
    </p>

    <p style="margin: 0 0 24px; font-size: 14px; color: #5f6b73; line-height: 1.7;">
        {{ $actor }} تنظیمات پروژه «{{ $project->name }}» را به‌روز کرد.
        برای دیدن تغییرات جدید روی دکمه زیر کلیک کنید.
    </p>

    <a href="{{ $url }}" class="btn-primary" style="color: #ffffff;">مشاهده پروژه →</a>

    <p style="margin: 24px 0 0; font-size: 12px; color: #94a3b8; line-height: 1.6;">
        اگر می‌خواهید اعلان‌های ایمیلی را غیرفعال کنید،
        <a href="{{ route('profile') }}" style="color: #0069FF; text-decoration: underline;">تنظیمات اعلان‌ها</a>
        را بررسی کنید.
    </p>
@endsection
