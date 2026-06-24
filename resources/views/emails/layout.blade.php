<!DOCTYPE html>
<html dir="rtl" lang="fa" style="direction: rtl; text-align: right;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $subject ?? 'نئووا' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Estedad:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; direction: rtl; text-align: right; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; direction: rtl; text-align: right; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; background-color: #f4f0e7; font-family: 'Estedad', 'Tahoma', 'Arial', sans-serif; }
        .email-wrapper { width: 100%; background-color: #f4f0e7; padding: 40px 20px; direction: rtl; text-align: right; }
        .email-container { max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(11, 27, 43, 0.08); direction: rtl; text-align: right; }
        .email-header { padding: 32px 40px 24px; text-align: center; border-bottom: 1px solid #f1f5f9; direction: rtl; }
        .email-body { padding: 32px 40px; direction: rtl; text-align: right; }
        .email-footer { padding: 24px 40px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; text-align: center; direction: rtl; }
        .btn-primary { display: inline-block; background-color: #0069FF; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-weight: 700; font-size: 13px; font-family: 'Estedad', 'Tahoma', 'Arial', sans-serif; direction: rtl; }
        .btn-secondary { display: inline-block; background-color: #f1f5f9; color: #475569 !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-weight: 700; font-size: 13px; font-family: 'Estedad', 'Tahoma', 'Arial', sans-serif; direction: rtl; }
        p, h1, h2, h3, span, a, td { font-family: 'Estedad', 'Tahoma', 'Arial', sans-serif; direction: rtl; text-align: right; }
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; border-radius: 0 !important; }
            .email-header, .email-body, .email-footer { padding-left: 24px !important; padding-right: 24px !important; }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <a href="{{ url('/') }}" style="text-decoration: none; direction: rtl;">
                    <img src="{{ url('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا" width="140" style="display: inline-block; height: auto; direction: rtl;">
                </a>
            </div>
            <div class="email-body">
                @yield('content')
            </div>
            <div class="email-footer">
                <p style="margin: 0 0 8px; font-size: 11px; color: #94a3b8; line-height: 1.6; direction: rtl; text-align: center;">
                    این ایمیل از طرف نئووا برای شما ارسال شده است.
                </p>
                <p style="margin: 0; font-size: 10px; color: #cbd5e1; line-height: 1.6; direction: rtl; text-align: center;">
                    <a href="{{ route('profile') }}" style="color: #94a3b8; text-decoration: underline;">تنظیمات اعلان‌ها</a>
                    &nbsp;·&nbsp;
                    <a href="{{ route('dashboard') }}" style="color: #94a3b8; text-decoration: underline;">داشبورد</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
