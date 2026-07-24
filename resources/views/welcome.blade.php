<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f8f6">
    <title>نئووا | کار تیمی، واضح و منظم</title>
    <meta name="description" content="نئووا فضای مشترکی برای مدیریت پروژه، وظایف و همکاری تیم شماست.">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:title" content="نئووا | کار تیمی، واضح و منظم">
    <meta property="og:description" content="پروژه‌ها و وظایف تیم را در یک فضای مشترک مدیریت کنید.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta name="twitter:card" content="summary">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'نئووا',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'description' => 'ابزار مدیریت پروژه و کار تیمی با فضای کاری، تخته پروژه و وظایف.',
            'url' => url('/'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
</head>
<body class="neova-home landing-page">
    <a href="#main-content" class="neova-skip-link">رفتن به محتوای اصلی</a>

    <header class="landing-shell landing-header" aria-label="سربرگ">
        <a href="{{ url('/') }}" class="landing-logo" aria-label="نئووا، صفحه اصلی">
            <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا">
        </a>
        <nav class="landing-nav" aria-label="ناوبری اصلی">
            <a href="#product">محصول</a>
            <a href="#workflow">نحوه کار</a>
            <a href="#faq">سؤال‌های متداول</a>
        </nav>
        <div class="landing-header-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="landing-text-link">داشبورد</a>
                <a href="{{ route('dashboard') }}" class="landing-button landing-button--small">ادامه کار <span aria-hidden="true">←</span></a>
            @else
                <a href="{{ route('auth') }}" class="landing-text-link">ورود</a>
                <a href="{{ route('auth') }}" class="landing-button landing-button--small">شروع رایگان <span aria-hidden="true">←</span></a>
            @endauth
        </div>
    </header>

    <main id="main-content">
        <section class="landing-hero landing-shell" aria-labelledby="landing-title">
            <div class="landing-hero-copy">
                <p class="landing-kicker"><span></span>مدیریت پروژه برای تیم‌های کوچک</p>
                <h1 id="landing-title">همه می‌دانند قدم بعدی چیست.</h1>
                <p class="landing-lead">نئووا پروژه‌ها، وظایف و گفت‌وگوهای تیم را در یک فضای روشن و مشترک جمع می‌کند؛ تا کار جلو برود، نه اینکه بین پیام‌ها گم شود.</p>
                <div class="landing-hero-actions">
                    <a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="landing-button">{{ auth()->check() ? 'رفتن به داشبورد' : 'رایگان شروع کنید' }} <span aria-hidden="true">←</span></a>
                    <a href="#product" class="landing-outline-button">محصول را ببینید <span aria-hidden="true">↓</span></a>
                </div>
                <p class="landing-reassurance"><span aria-hidden="true">✓</span> بدون کارت بانکی <span aria-hidden="true">✓</span> ورود با شماره موبایل</p>
            </div>

            <div class="landing-hero-visual" aria-label="نمونه‌ای از تخته پروژه نئووا">
                <div class="landing-visual-note">کارها، یکجا</div>
                <div class="landing-product-frame">
                    <div class="landing-product-topbar"><span class="landing-window-dots"><i></i><i></i><i></i></span><span>نئووا / تیم محصول</span><span class="landing-live"><i></i> به‌روز</span></div>
                    <div class="landing-product-header"><div><small>پروژه</small><strong>نسخه جدید محصول</strong></div><div class="landing-avatar-stack"><i>ن</i><i>م</i><i>س</i><b>+۵</b></div></div>
                    <div class="landing-board-grid">
                        <div class="landing-board-column"><div class="landing-column-heading"><span><i class="landing-status-dot landing-status-dot--blue"></i>ایده‌ها</span><b>۳</b></div><article class="landing-task"><small>NEO-014</small><strong>تحقیق کاربران</strong><span>مسئول: نیلوفر</span></article><article class="landing-task"><small>NEO-018</small><strong>متن صفحه معرفی</strong><span>۲۳ خرداد</span></article></div>
                        <div class="landing-board-column landing-board-column--active"><div class="landing-column-heading"><span><i class="landing-status-dot landing-status-dot--orange"></i>در حال انجام</span><b>۲</b></div><article class="landing-task landing-task--active"><small>NEO-021</small><strong>طراحی جریان ثبت‌نام</strong><div class="landing-progress"><i></i></div><span>۳ از ۴ مورد تکمیل شده</span></article><article class="landing-task"><small>NEO-024</small><strong>اتصال پیامک دعوت</strong><span>مسئول: محمد</span></article></div>
                        <div class="landing-board-column"><div class="landing-column-heading"><span><i class="landing-status-dot landing-status-dot--green"></i>آماده انتشار</span><b>۱</b></div><article class="landing-task"><small>NEO-027</small><strong>آماده‌سازی انتشار</strong><span>تکمیل شده</span><em>✓</em></article></div>
                    </div>
                    <div class="landing-product-footer"><span>آخرین فعالیت: ۲ دقیقه پیش</span><b>+ افزودن وظیفه</b></div>
                </div>
            </div>
        </section>

        <section class="landing-trust-strip" aria-label="ارزش‌های اصلی نئووا">
            <div class="landing-shell landing-trust-grid"><div><strong>یک فضای مشترک</strong><span>همه اطلاعات پروژه، کنار هم</span></div><div><strong>مسئولیت روشن</strong><span>هر وظیفه، یک مسئول و یک موعد</span></div><div><strong>شروع سریع</strong><span>بدون آموزش طولانی و پیچیده</span></div></div>
        </section>

        <section class="landing-intro-section landing-shell" id="product" aria-labelledby="product-title">
            <div class="landing-section-label">چرا نئووا؟</div>
            <h2 id="product-title">کار تیمی نباید به پیدا کردن آخرین پیام تبدیل شود.</h2>
            <p>وقتی وظایف در پیام‌رسان‌ها، فایل‌ها و یادداشت‌های شخصی پخش می‌شوند، هیچ‌کس تصویر کاملی از پروژه ندارد. نئووا آن تصویر را برای همه تیم، در یک جای مشخص می‌سازد.</p>
        </section>

        <section class="landing-letter-section landing-shell" aria-labelledby="letter-title">
            <div class="landing-letter-heading">
                <div class="landing-section-label">پیام نئووا</div>
                <h2 id="letter-title">ابزار مدیریت پروژه باید کار تیم را ساده‌تر کند.</h2>
            </div>
            <div class="landing-letter">
                <p>سلام،</p>
                <p>اگر کارهای تیم شما بین پیام‌ها، جلسه‌ها و فایل‌ها پخش شده است، تنها نیستید. وقتی اطلاعات پروژه در چند جای مختلف قرار دارد، پیگیری آن سخت می‌شود.</p>
                <p>یک نفر دنبال آخرین تصمیم می‌گردد. یک وظیفه زمان انجام دارد، اما مسئول آن مشخص نیست. بخشی از کار هم در ذهن افراد می‌ماند و به‌مرور فراموش می‌شود.</p>
                <p>ما نئووا را برای همین وضعیت ساخته‌ایم. در نئووا می‌توانید فضای کاری بسازید، پروژه تعریف کنید، وظایف را روی تخته قرار دهید و برای هر وظیفه مسئول، زمان انجام، توضیح، چک‌لیست و گفت‌وگو داشته باشید.</p>
                <p>قرار نیست کار با نئووا پیچیده شود. کافی است پروژه‌تان را بسازید و اولین وظیفه را اضافه کنید. از همان‌جا، همه اعضای تیم می‌توانند وضعیت کار را ببینند و بدانند قدم بعدی چیست.</p>
                <p>امیدواریم نئووا هر روز بخشی از کار شما را ساده‌تر کند.</p>
                <div class="landing-letter-signature">
                    <img src="{{ asset('signatures/amir-mehrabian-signature.png') }}" alt="امضای امیر مهرابیان">
                </div>
            </div>
        </section>

        <section class="landing-features landing-shell" id="workflow" aria-labelledby="workflow-title">
            <div class="landing-section-heading"><div><div class="landing-section-label">همه‌چیز سر جای خودش</div><h2 id="workflow-title">از برنامه‌ریزی تا تحویل، با وضوح بیشتر.</h2></div><p>نئووا برای همان کارهای روزمره ساخته شده است؛ ساده، قابل فهم و آماده استفاده از همان روز اول.</p></div>
            <article class="landing-feature-row"><div class="landing-feature-copy"><span>۰۱ / ساختار پروژه</span><h3>پروژه‌ها را از هم جدا کنید.</h3><p>برای هر تیم و هر جریان کاری، فضای مشخص داشته باشید. پروژه‌ها و ستون‌ها کمک می‌کنند بدانید کار در کدام مرحله قرار دارد.</p></div><div class="landing-ui-card landing-structure-card"><div class="landing-ui-card-head"><strong>تیم محصول</strong><span>۸ عضو</span></div><div class="landing-breadcrumb"><b>فضای کاری</b><i>←</i><b>پروژه</b><i>←</i><strong>وضعیت</strong></div><div class="landing-ui-stat"><span>در حال انجام</span><strong>۱۲ وظیفه</strong><small>+۳ از دیروز</small></div></div></article>
            <article class="landing-feature-row landing-feature-row--reverse"><div class="landing-feature-copy"><span>۰۲ / مسئولیت روشن</span><h3>کار را به آدم درست بسپارید.</h3><p>مسئول، موعد و سطح دسترسی هر کار را مشخص کنید تا هیچ وظیفه‌ای بدون صاحب نماند و همه بدانند چه چیزی از آن‌ها انتظار می‌رود.</p></div><div class="landing-ui-card landing-people-card"><div class="landing-ui-card-head"><strong>اعضای پروژه</strong><button type="button">دعوت عضو +</button></div><div class="landing-person"><i>ن</i><strong>نیلوفر احمدی</strong><span>مالک</span></div><div class="landing-person"><i class="landing-person--orange">م</i><strong>محمد رضایی</strong><span>مدیر</span></div><div class="landing-person"><i class="landing-person--green">س</i><strong>سارا میرزایی</strong><span>مشاهده</span></div></div></article>
            <article class="landing-feature-row"><div class="landing-feature-copy"><span>۰۳ / جزئیات در کنار کار</span><h3>گفت‌وگو را از خود وظیفه جدا نکنید.</h3><p>توضیحات، چک‌لیست و تصمیم‌های تیم را کنار همان وظیفه نگه دارید؛ جایی که بعداً به آن نیاز پیدا می‌کنید.</p></div><div class="landing-ui-card landing-task-detail-card"><div class="landing-task-detail-top"><small>NEO-021 / در حال انجام</small><span>⋮</span></div><strong>طراحی جریان ثبت‌نام</strong><div class="landing-check-row"><i>✓</i> متن پیامک تأیید</div><div class="landing-check-row"><i>✓</i> حالت خطا</div><div class="landing-check-row landing-check-row--open"><i></i> بازبینی موبایل</div><div class="landing-comment"><i>م</i><span><b>محمد</b> نسخه موبایل را تا عصر بازبینی می‌کنم.</span></div></div></article>
        </section>

        <section class="landing-role-section" aria-labelledby="role-title"><div class="landing-shell"><div class="landing-section-label">برای هر عضو تیم</div><h2 id="role-title">یک تصویر مشترک، برای هر نقشی.</h2><div class="landing-role-grid"><article><span>مدیر تیم</span><h3>تصمیم بگیرید، نه اینکه پیگیری کنید.</h3><p>وضعیت پروژه و گلوگاه‌ها را در یک نگاه ببینید.</p></article><article><span>عضو تیم</span><h3>بدانید امروز باید روی چه چیزی کار کنید.</h3><p>وظیفه، توضیح و گفت‌وگو را کنار هم داشته باشید.</p></article><article><span>صاحب پروژه</span><h3>کارها را منظم و قابل تحویل نگه دارید.</h3><p>از ایده اولیه تا آخرین مرحله، مسیر را گم نکنید.</p></article></div></div></section>

        @php
            $questions = ['می‌توانم برای هر تیم فضای کاری جدا داشته باشم؟', 'می‌توانم چند پروژه را در یک فضای کاری مدیریت کنم؟', 'می‌توانم برای هر کار مسئول و موعد تعیین کنم؟', 'می‌توانم نقش و سطح دسترسی اعضا را کنترل کنم؟', 'می‌توانم گفت‌وگو را کنار همان وظیفه نگه دارم؟', 'ورود با شماره موبایل چگونه انجام می‌شود؟'];
        @endphp
        <section class="landing-faq landing-shell" id="faq" aria-labelledby="faq-title"><div class="landing-faq-grid"><div><div class="landing-section-label">سؤال‌های متداول</div><h2 id="faq-title">شروع کار ساده است.</h2><p>پاسخ چند سؤال رایج درباره استفاده از نئووا.</p></div><div class="landing-question-list">@foreach ($questions as $question)<p><span>+</span>{{ $question }}</p>@endforeach</div></div></section>

        <section class="landing-closing" aria-labelledby="closing-title"><div class="landing-shell"><div class="landing-section-label">قدم بعدی</div><h2 id="closing-title">کمتر دنبال کار بگردید.<br>بیشتر کار را جلو ببرید.</h2><p>اولین فضای کاری خود را بسازید و پروژه‌تان را از همین امروز مرتب کنید.</p><a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="landing-button landing-button--light">{{ auth()->check() ? 'رفتن به داشبورد' : 'رایگان شروع کنید' }} <span aria-hidden="true">←</span></a></div></section>
    </main>

    <footer class="landing-footer"><div class="landing-shell landing-footer-inner"><a href="{{ url('/') }}" class="landing-logo"><img src="{{ asset('assets/logo/horizental-logo-white-transparent.png') }}" alt="نئووا"></a><nav aria-label="پیوندهای پایانی"><a href="#product">محصول</a><a href="#workflow">نحوه کار</a><a href="#faq">سؤال‌های متداول</a><a href="{{ route('auth') }}">ورود</a></nav><p>نئووا؛ کار تیمی، واضح و منظم.</p></div></footer>
</body>
</html>
