<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f5ef">
    <title>نئووا | کارها را از توی چت بیرون بیاورید</title>
    <meta name="description" content="نئووا فضای کار فارسی برای تیم‌های کوچک است؛ پروژه‌ها، کارهای امروز، مسئولیت‌ها و گفت‌وگوها را یک‌جا و روشن مدیریت کنید.">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:title" content="نئووا | کارها را از توی چت بیرون بیاورید">
    <meta property="og:description" content="یک فضای فارسی و روشن برای پروژه‌ها، کارهای امروز و گفت‌وگوهای تیم شما.">
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
            'description' => 'فضای کار فارسی برای مدیریت پروژه‌ها، کارهای امروز، مسئولیت‌ها و گفت‌وگوهای تیم‌های کوچک.',
            'url' => url('/'),
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'IRR', 'description' => 'رایگان در دوره بتا'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
</head>
<body class="nl-page">
    <a href="#main-content" class="nl-skip-link">رفتن به محتوای اصلی</a>
    <header class="nl-shell nl-header" id="top">
        <a href="{{ url('/') }}" class="nl-logo" aria-label="نئووا، صفحه اصلی"><img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا"></a>
        <nav class="nl-nav" aria-label="ناوبری اصلی"><a href="#how">چطور کار می‌کند</a><a href="#features">امکانات</a><a href="#faq">سؤال‌ها</a></nav>
        <div class="nl-header-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="nl-text-link">امروز من</a><a href="{{ route('dashboard') }}" class="nl-button nl-button--small">ادامه کار <span aria-hidden="true">←</span></a>
            @else
                <a href="{{ route('auth') }}" class="nl-text-link">ورود</a><a href="{{ route('auth') }}" class="nl-button nl-button--small">شروع رایگان <span aria-hidden="true">←</span></a>
            @endauth
        </div>
    </header>

    <main id="main-content">
        <section class="nl-hero nl-shell" aria-labelledby="landing-title">
            <p class="nl-kicker">برای تیم‌های کوچک؛ از اولین کار تا آخرین تحویل</p>
            <h1 id="landing-title">کارها را از توی چت بیرون بیاورید.</h1>
            <p class="nl-lead">نئووا جای مشخص پروژه‌ها، کارهای امروز و گفت‌وگوهای تیم شماست. همه می‌دانند چه چیزی مهم است، مسئولش کیست و کار کجا مانده.</p>
            <div class="nl-hero-actions"><a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="nl-button">{{ auth()->check() ? 'رفتن به امروز' : 'رایگان در نسخه بتا شروع کنید' }}</a><a href="#product-preview" class="nl-button nl-button--secondary">محصول را ببینید <span aria-hidden="true">↓</span></a></div>
            <p class="nl-reassurance">بدون کارت بانکی <span aria-hidden="true">·</span> ورود با شماره موبایل <span aria-hidden="true">·</span> بدون نصب</p>
        </section>

        <section class="nl-product-stage nl-shell" id="product-preview" aria-label="نمایی از صفحه امروز نئووا">
            <div class="nl-product-window">
                <div class="nl-product-topbar"><strong>نئووا / تیم ما</strong><span><i></i> همه‌چیز به‌روز است</span></div>
                <div class="nl-product-layout">
                    <aside class="nl-product-sidebar" aria-label="نمونه ناوبری محصول"><img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا"><div class="nl-product-nav"><span class="is-active">امروز</span><span>تخته</span><span>پروژه‌ها</span><span>تیم</span></div><div class="nl-product-projects"><small>پروژه‌ها</small><span>پروژه محصول</span><span>فروش و همکاری</span></div></aside>
                    <div class="nl-today-preview">
                        <div class="nl-today-heading"><div><small>چهارشنبه ۲۹ مرداد</small><h2>امروز من</h2></div><span>+ کار سریع</span></div>
                        <div class="nl-today-grid"><div class="nl-today-list"><article><i></i><div><strong>بازبینی نسخه موبایل</strong><small>پروژه محصول · امروز</small></div><b>سارا</b></article><article><i></i><div><strong>ارسال پیشنهاد همکاری</strong><small>فروش · تا ساعت ۱۵</small></div><b>امیر</b></article><article class="is-done"><i>✓</i><div><strong>متن پیامک تأیید</strong><small>پروژه محصول · انجام شد</small></div></article></div><aside class="nl-team-preview"><strong>تیم امروز</strong><p><span>سارا</span><b>۳ کار</b></p><p><span>محمد</span><b>۲ کار</b></p><p><span>نیلوفر</span><b>۴ کار</b></p></aside></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="nl-outcomes" aria-label="نتیجه استفاده از نئووا"><div class="nl-shell nl-outcome-grid"><article><strong>کمتر پیگیری کنید</strong><p>وضعیت کارها جلوی چشم همه است.</p></article><article><strong>مسئولیت را روشن کنید</strong><p>هر کار صاحب و موعد مشخص دارد.</p></article><article><strong>یک جای قابل اعتماد بسازید</strong><p>تصمیم و گفت‌وگو کنار خود کار می‌ماند.</p></article></div></section>

        <section class="nl-story nl-shell" id="how" aria-labelledby="story-title"><div><p class="nl-section-label">اگر این وضعیت آشناست</p><h2 id="story-title">یک نگاه کافی‌ست تا بفهمید کارها کجا هستند.</h2></div><div class="nl-story-copy"><p>یک نفر می‌پرسد «آخرش چه شد؟». نفر بعدی دنبال آخرین فایل می‌گردد. موعد کار رسیده، اما مسئولش معلوم نیست. تصمیم مهم جلسه هم جایی بین صدها پیام جا مانده است.</p><p>مشکل کم‌کاری نیست؛ کار تیم جای مشخصی ندارد. نئووا همان جای مشخص است: ساده، فارسی و آن‌قدر روشن که از روز اول قابل استفاده باشد.</p></div></section>

        <section class="nl-product-stories nl-shell" aria-labelledby="product-stories-title">
            <div class="nl-section-intro"><p class="nl-section-label">سه بخش، یک تصویر مشترک</p><h2 id="product-stories-title">کار از برنامه‌ریزی تا تحویل، سر جای خودش می‌ماند.</h2></div>
            <div class="nl-story-cards">
                <article class="nl-story-card nl-story-card--mint"><span>۰۱ / امروز</span><h3>تمرکز هر نفر روشن است.</h3><p>کارهای مهم امروز، عقب‌مانده و انجام‌شده را کنار هم ببینید؛ بدون اینکه بین چند پروژه بگردید.</p><div class="nl-mini-today"><p><i></i><b>بازبینی نسخه موبایل</b><small>امروز</small></p><p><i></i><b>ارسال پیشنهاد همکاری</b><small>ساعت ۱۵</small></p><p class="is-done"><i>✓</i><b>متن پیامک تأیید</b><small>انجام شد</small></p></div></article>
                <article class="nl-story-card nl-story-card--blue"><span>۰۲ / تخته پروژه</span><h3>حرکت پروژه دیده می‌شود.</h3><p>کارها را از ایده تا انجام روی تخته حرکت دهید و گلوگاه را قبل از جلسه بعدی ببینید.</p><div class="nl-mini-board"><div><b>آماده</b><p>تحقیق کاربران</p></div><div><b>در حال انجام</b><p>صفحه معرفی</p></div><div><b>انجام شد</b><p>نسخه موبایل ✓</p></div></div></article>
                <article class="nl-story-card nl-story-card--sand"><span>۰۳ / خود کار</span><h3>جزئیات گم نمی‌شوند.</h3><p>مسئول، موعد، چک‌لیست، فایل و گفت‌وگو را همان‌جایی نگه دارید که بعداً لازم می‌شود.</p><div class="nl-mini-task"><small>NEO-021 / در حال انجام</small><strong>طراحی جریان ثبت‌نام</strong><p>✓ متن پیامک تأیید</p><p>○ بازبینی موبایل</p><blockquote><b>محمد:</b> نسخه موبایل را تا عصر بازبینی می‌کنم.</blockquote></div></article>
            </div>
        </section>

        <section class="nl-capabilities" id="features" aria-labelledby="features-title"><div class="nl-shell nl-capabilities-grid"><div><p class="nl-section-label">یک جواب روشن</p><h2 id="features-title">هر چیزی که تیم کوچک برای جلو بردن کار لازم دارد.</h2></div><div class="nl-capability-list"><p>چند فضای کاری و چند پروژه <b>✓</b></p><p>مسئول، موعد و چک‌لیست <b>✓</b></p><p>پروژه عمومی و خصوصی <b>✓</b></p><p>نقش و سطح دسترسی <b>✓</b></p><p>فایل و گفت‌وگوی هر کار <b>✓</b></p><p>دعوت پیامکی اعضا <b>✓</b></p><p>جست‌وجوی پروژه و کار <b>✓</b></p><p>چرخه‌های کوتاه کاری <b>✓</b></p></div></div></section>

        <section class="nl-testimonials nl-shell" aria-labelledby="testimonials-title"><div class="nl-testimonial-heading"><div><p class="nl-section-label">اعتماد واقعی، نه عدد ساختگی</p><h2 id="testimonials-title">تجربه مشتری‌ها</h2></div><p>محتوای تأییدشده پیش از انتشار جایگزین می‌شود.</p></div><div class="nl-testimonial-grid"><article><strong>نظر مشتری ۰۱</strong><p>جای نقل‌قول، نام، نقش و شرکت</p></article><article><strong>نظر مشتری ۰۲</strong><p>جای نقل‌قول، نام، نقش و شرکت</p></article><article><strong>نظر مشتری ۰۳</strong><p>جای نقل‌قول، نام، نقش و شرکت</p></article></div></section>

        <section class="nl-founder nl-shell" aria-labelledby="founder-title"><div><p class="nl-section-label">چرا نئووا را ساختیم</p><h2 id="founder-title">یک یادداشت کوتاه از سازنده نئووا</h2></div><article><p>سلام،</p><p>ما نئووا را ساختیم چون تیم کوچک نباید برای فهمیدن وضعیت کار، جلسه و گزارش بیشتری تولید کند. ابزار خوب آرام است: اطلاعات را سر جای خودش نگه می‌دارد و اجازه می‌دهد آدم‌ها روی کار اصلی تمرکز کنند.</p><p>فضای کاری را بسازید، پروژه را اضافه کنید و اولین کار را بنویسید. باقی مسیر باید طبیعی باشد.</p><footer><img src="{{ asset('signatures/amir-mehrabian-signature.png') }}" alt="امضای امیر مهرابیان"><strong>امیر مهرابیان — سازنده نئووا</strong></footer></article></section>

        <section class="nl-faq" id="faq" aria-labelledby="faq-title"><div class="nl-shell"><p class="nl-section-label">پیش از شروع</p><h2 id="faq-title">شاید این‌ها را بپرسید.</h2><div class="nl-faq-list"><details open><summary>نئووا رایگان است؟</summary><p>استفاده از نئووا در دوره بتا رایگان است و برای شروع به کارت بانکی نیاز ندارید.</p></details><details><summary>برای چه تیم‌هایی مناسب است؟</summary><p>برای تیم‌های کوچکی که پروژه و کار مشترک دارند؛ از محصول و نرم‌افزار تا آژانس، عملیات و خدمات.</p></details><details><summary>شروع کار چقدر زمان می‌برد؟</summary><p>با شماره موبایل وارد شوید، فضای کاری را بسازید و اولین پروژه و کار را همان روز اضافه کنید.</p></details><details><summary>می‌توانم چند پروژه و فضای کاری داشته باشم؟</summary><p>بله؛ پروژه‌های مختلف را در یک فضای کاری مدیریت کنید و برای تیم‌ها یا فعالیت‌های جدا، فضای کاری دیگری بسازید.</p></details><details><summary>می‌توانم دسترسی اعضا را کنترل کنم؟</summary><p>بله؛ نقش اعضا و عمومی یا خصوصی بودن پروژه‌ها قابل مدیریت است.</p></details></div></div></section>

        <section class="nl-closing" id="start" aria-labelledby="closing-title"><div class="nl-shell"><p class="nl-section-label">قدم بعدی</p><h2 id="closing-title">اولین فضای کاری‌تان را<br>همین امروز بسازید.</h2><p>راه‌اندازی ساده است؛ ادامه کار هم باید همین‌طور باشد.</p><a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="nl-button">{{ auth()->check() ? 'رفتن به امروز' : 'رایگان در نسخه بتا شروع کنید' }} <span aria-hidden="true">←</span></a></div></section>
    </main>

    <footer class="nl-footer"><div class="nl-shell nl-footer-inner"><a href="#top" class="nl-logo" aria-label="بازگشت به بالای صفحه"><img src="{{ asset('assets/logo/horizental-logo-white-transparent.png') }}" alt="نئووا"></a><nav aria-label="پیوندهای پایانی"><a href="#how">چطور کار می‌کند</a><a href="#features">امکانات</a><a href="#faq">سؤال‌ها</a><a href="{{ route('auth') }}">ورود</a></nav><p>نئووا؛ کار تیمی، واضح و منظم.</p></div></footer>
</body>
</html>
