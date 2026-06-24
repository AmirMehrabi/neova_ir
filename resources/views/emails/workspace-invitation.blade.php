@extends('emails.layout')

@section('content')
    <h1 style="margin: 0 0 16px; font-size: 18px; font-weight: 700; color: #0b1b2b; line-height: 1.5;">
        {{ $inviter }} شما را به «{{ $workspace->name }}» دعوت کرد
    </h1>

    <p style="margin: 0 0 8px; font-size: 14px; color: #5f6b73; line-height: 1.7;">
        سلام {{ $user->first_name ?? $user->name }}،
    </p>

    <p style="margin: 0 0 24px; font-size: 14px; color: #5f6b73; line-height: 1.7;">
        {{ $inviter }} شما را برای همکاری در فضای کاری «{{ $workspace->name }}» دعوت کرده است.
        شما به عنوان «{{ $role === 'admin' ? 'مدیر' : 'کاربر' }}» اضافه خواهید شد.
    </p>

    <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
        <tr>
            <td style="background-color: #f8fafc; border-radius: 10px; padding: 16px 20px; border: 1px solid #e2e8f0;">
                <p style="margin: 0 0 4px; font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">فضای کاری</p>
                <p style="margin: 0; font-size: 13px; font-weight: 700; color: #0b1b2b;">{{ $workspace->name }}</p>
                <p style="margin: 4px 0 0; font-size: 11px; color: #94a3b8;">دعوت‌کننده: {{ $inviter }} · نقش: {{ $role === 'admin' ? 'مدیر' : 'کاربر' }}</p>
            </td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td style="padding-left: 8px;">
                <a href="{{ route('invitations.show', $invitationCode) }}" class="btn-primary" style="color: #ffffff;">پذیرش دعوت →</a>
            </td>
            <td>
                <a href="{{ route('dashboard') }}" class="btn-secondary">مشاهده داشبورد</a>
            </td>
        </tr>
    </table>

    <p style="margin: 24px 0 0; font-size: 12px; color: #94a3b8; line-height: 1.6;">
        این دعوت تا {{ $expiresAt }} معتبر است.
    </p>
@endsection
