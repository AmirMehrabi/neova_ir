@extends('emails.layout')

@section('content')
    <h1 style="margin: 0 0 16px; font-size: 18px; font-weight: 700; color: #0b1b2b; line-height: 1.5;">
        {{ $actor }} شما را به «{{ $task->title }}» اضافه کرد
    </h1>

    <p style="margin: 0 0 8px; font-size: 14px; color: #5f6b73; line-height: 1.7;">
        سلام {{ $user->first_name ?? $user->name }}،
    </p>

    <p style="margin: 0 0 24px; font-size: 14px; color: #5f6b73; line-height: 1.7;">
        {{ $actor }} شما را به وظیفه «{{ $task->title }}» در پروژه «{{ $project->name }}» اضافه کرد.
        حالا می‌توانید این وظیفه را در تخته پروژه ببینید و مدیریت کنید.
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td style="background-color: #f8fafc; border-radius: 10px; padding: 16px 20px; border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 4px; font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">پروژه</p>
                <p style="margin: 0; font-size: 13px; font-weight: 700; color: #0b1b2b;">{{ $project->name }}</p>
            </td>
        </tr>
    </table>

    <a href="{{ $url }}" class="btn-primary" style="color: #ffffff;">دیدن وظیفه →</a>

    <p style="margin: 24px 0 0; font-size: 12px; color: #94a3b8; line-height: 1.6;">
        اگر می‌خواهید اعلان‌های ایمیلی را غیرفعال کنید،
        <a href="{{ route('profile') }}" style="color: #0069FF; text-decoration: underline;">تنظیمات اعلان‌ها</a>
        را بررسی کنید.
    </p>
@endsection
