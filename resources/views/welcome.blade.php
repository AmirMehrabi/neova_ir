<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f4ec">
    <title>نئووا | مدیریت پروژه و کار تیمی</title>
    <meta name="description" content="نئووا پروژه‌ها، وظایف، مسئولان، زمان‌بندی و گفت‌وگوهای تیم را در یک فضای مشترک مدیریت می‌کند.">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:title" content="نئووا | مدیریت پروژه و کار تیمی">
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
            'description' => 'ابزار مدیریت پروژه و کار تیمی با فضای کاری، تخته پروژه، وظایف، چک‌لیست و گفت‌وگو.',
            'url' => url('/'),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
</head>
<body class="neova-home landing-page">
    <a href="#main-content" class="neova-skip-link">رفتن به محتوای اصلی</a>

    <header class="landing-shell landing-header" aria-label="سربرگ">
        <a href="{{ url('/') }}" class="landing-logo" aria-label="نئووا، صفحه اصلی">
            <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا" class="h-9 sm:h-10 w-auto object-contain">
        </a>
        <nav class="landing-header-actions" aria-label="دسترسی سریع">
            @auth
                <a href="{{ route('dashboard') }}" class="landing-button landing-button--small">ادامه کار</a>
            @else
                <a href="{{ route('auth') }}" class="landing-button landing-button--small">شروع رایگان</a>
            @endauth
        </nav>
    </header>

    <main id="main-content">
        <section class="landing-shell landing-hero" aria-labelledby="landing-title">
            <div class="landing-hero-copy">
                <p class="landing-eyebrow">مدیریت پروژه برای تیم‌های کوچک</p>
                <h1 id="landing-title">کارهای تیم خود را در یک جای مشخص مدیریت کنید.</h1>
                <p class="landing-lead">نئووا پروژه‌ها، وظایف، مسئولان، زمان‌بندی و گفت‌وگوهای تیم را در یک فضای مشترک قرار می‌دهد.</p>
                <div class="landing-hero-actions">
                    <button type="button" class="landing-button" onclick="document.getElementById('product-preview')?.scrollIntoView({ behavior: 'smooth', block: 'center' })">تخته پروژه را ببینید <span aria-hidden="true">←</span></button>
                    <a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="landing-text-link">{{ auth()->check() ? 'رفتن به داشبورد' : 'شروع رایگان' }}</a>
                </div>
                <p class="landing-reassurance">برای ورود فقط به شماره تلفن و کد تأیید نیاز دارید.</p>
            </div>

            <div id="product-preview" class="landing-product-frame" aria-label="نمونه تخته پروژه">
                <div class="landing-product-bar">
                    <div><small>محصول / NEO</small><strong>بازطراحی محصول</strong></div>
                    <span class="landing-product-status">در حال انجام</span>
                </div>
                <div class="landing-board-grid">
                    <div class="landing-board-column">
                        <div class="landing-column-heading"><span><i class="landing-dot landing-dot--blue"></i>انجام‌نشده</span><b>۲</b></div>
                        <article class="landing-task"><small>NEO-014</small><strong>تحقیق کاربران</strong><span>تحقیق · ۲۸ خرداد</span></article>
                        <article class="landing-task"><small>NEO-018</small><strong>متن صفحه معرفی</strong><span>محتوا · ۳۰ خرداد</span></article>
                    </div>
                    <div class="landing-board-column landing-board-column--active">
                        <div class="landing-column-heading"><span><i class="landing-dot landing-dot--amber"></i>در حال انجام</span><b>۲</b></div>
                        <article class="landing-task"><small>NEO-021</small><strong>طراحی جریان ثبت‌نام</strong><em><i></i></em><span>مسئول: محمد · امروز</span></article>
                        <article class="landing-task"><small>NEO-024</small><strong>اتصال پیامک دعوت</strong><span>فنی · فردا</span></article>
                    </div>
                    <div class="landing-board-column">
                        <div class="landing-column-heading"><span><i class="landing-dot landing-dot--green"></i>بازبینی</span><b>۱</b></div>
                        <article class="landing-task"><small>NEO-027</small><strong>آماده‌سازی انتشار</strong><em><i style="width:75%"></i></em><span>۳ مورد از ۴ مورد تکمیل شده است</span></article>
                    </div>
                </div>
                <p class="landing-product-caption">وضعیت پروژه، مسئول هر وظیفه و زمان انجام آن را ببینید.</p>
            </div>
        </section>

        <section class="landing-proof" aria-label="اصول نئووا">
            <div class="landing-shell landing-proof-grid">
                <p><b>فضای مشترک</b><span>اطلاعات پروژه در یک محل برای همه اعضای تیم قرار دارد.</span></p>
                <p><b>مسئول مشخص</b><span>برای هر وظیفه یک مسئول و یک زمان انجام تعیین می‌کنید.</span></p>
                <p><b>استفاده ساده</b><span>تیم بدون آموزش طولانی می‌تواند کار خود را شروع کند.</span></p>
            </div>
        </section>

        <section class="landing-shell landing-problem" aria-labelledby="problem-title">
            <div class="landing-section-label">چالش تیم‌ها</div>
            <div>
                <h2 id="problem-title">اطلاعات پروژه نباید در چند پیام‌رسان و فایل مختلف پخش شود.</h2>
                <p>وقتی وظایف، زمان‌بندی و تصمیم‌های تیم در چند ابزار مختلف قرار دارند، پیگیری پروژه دشوار می‌شود.</p>
                <p>نئووا همه این اطلاعات را در یک فضای کاری، پروژه، تخته و وظیفه سازمان‌دهی می‌کند.</p>
            </div>
        </section>

        <section class="landing-shell landing-letter-section" aria-labelledby="letter-title">
            <div class="landing-letter-heading">
                <p class="landing-eyebrow">پیام نئووا</p>
                <h2 id="letter-title">ابزار مدیریت پروژه باید کار تیم را ساده‌تر کند.</h2>
            </div>
            <div class="landing-letter">
                <p>سلام،</p>
                <p>اگر کارهای تیم شما بین پیام‌ها، جلسه‌ها و فایل‌ها پخش شده است، تنها نیستید. وقتی اطلاعات پروژه در چند جای مختلف قرار دارد، پیگیری آن سخت می‌شود.</p>
                <p>یک نفر دنبال آخرین تصمیم می‌گردد. یک وظیفه زمان انجام دارد، اما مسئول آن مشخص نیست. بخشی از کار هم در ذهن افراد می‌ماند و به‌مرور فراموش می‌شود.</p>
                <p>ما نئووا را برای همین وضعیت ساخته‌ایم. در نئووا می‌توانید فضای کاری بسازید، پروژه تعریف کنید، وظایف را روی تخته قرار دهید و برای هر وظیفه مسئول، زمان انجام، توضیح، چک‌لیست و گفتگو داشته باشید.</p>
                <p>قرار نیست کار با نئووا پیچیده شود. کافی است پروژه‌تان را بسازید و اولین وظیفه را اضافه کنید. از همان‌جا، همه اعضای تیم می‌توانند وضعیت کار را ببینند و بدانند قدم بعدی چیست.</p>
                <p>امیدواریم نئووا هر روز بخشی از کار شما را ساده‌تر کند.</p>
                <div class="landing-letter-signature">
                    <img src="{{ asset('assets/signatures/amir-mehrabian-signature.png') }}" alt="امضای امیر مهرابیان">
                    {{-- <span>تیم نئووا</span> --}}
                </div>
            </div>
        </section>

        <section class="landing-shell landing-workflow" aria-labelledby="workflow-title">
            <div class="landing-section-intro">
                <p class="landing-eyebrow">نحوه استفاده روزانه</p>
                <h2 id="workflow-title">ساختار پروژه، مسئولیت افراد و جزئیات وظایف را مشخص کنید.</h2>
            </div>

            <article class="landing-workflow-row" id="structure">
                <div class="landing-workflow-copy"><span>۰۱ / ساختار پروژه</span><h3>مراحل پروژه را مشخص کنید.</h3><p>فضای کاری، پروژه‌ها را جدا می‌کند و ستون‌ها وضعیت هر وظیفه را نشان می‌دهند.</p></div>
                <div class="landing-mini-panel landing-structure-panel"><div><small>فضای کاری</small><strong>تیم محصول</strong><span>۸ عضو</span></div><b>←</b><div><small>پروژه</small><strong>نسخه جدید</strong><span>۴ ستون</span></div><b>←</b><div><small>وضعیت</small><strong>در حال انجام</strong><span>۱۲ وظیفه</span></div></div>
            </article>

            <article class="landing-workflow-row" id="roles">
                <div class="landing-workflow-copy"><span>۰۲ / مسئولیت افراد</span><h3>مسئول هر وظیفه را مشخص کنید.</h3><p>برای هر وظیفه یک مسئول و زمان انجام تعیین کنید و سطح دسترسی مناسب را به هر عضو بدهید.</p></div>
                <div class="landing-mini-panel landing-people-panel"><div class="landing-panel-heading"><strong>اعضای پروژه</strong><button type="button">دعوت عضو</button></div><div><i>ن</i><strong>نیلوفر احمدی</strong><span>مالک</span></div><div><i class="is-coral">م</i><strong>محمد رضایی</strong><span>مدیر</span></div><div><i class="is-mint">س</i><strong>سارا میرزایی</strong><span>فقط مشاهده</span></div></div>
            </article>

            <article class="landing-workflow-row" id="task-details">
                <div class="landing-workflow-copy"><span>۰۳ / جزئیات وظیفه</span><h3>اطلاعات هر وظیفه را یکجا نگه دارید.</h3><p>توضیحات، برچسب‌ها، چک‌لیست و گفتگو در صفحه همان وظیفه قرار می‌گیرند.</p></div>
                <div class="landing-mini-panel landing-task-panel"><small>NEO-021 / در حال انجام</small><strong>طراحی جریان ثبت‌نام</strong><div class="landing-check-item"><i class="is-done">✓</i>متن پیامک تأیید</div><div class="landing-check-item"><i class="is-done">✓</i>حالت خطا</div><div class="landing-check-item"><i></i>بازبینی موبایل</div><div class="landing-comment"><i>م</i><span><b>محمد</b> نسخه موبایل را تا عصر بازبینی می‌کنم.</span></div></div>
            </article>
        </section>

        @php
            $questions = [
                'می‌توانم برای هر تیم فضای کاری جدا داشته باشم؟',
                'می‌توانم چند پروژه را در یک فضای کاری مدیریت کنم؟',
                'می‌توانم برای هر کار مسئول و موعد تعیین کنم؟',
                'می‌توانم نقش و سطح دسترسی اعضا را کنترل کنم؟',
                'می‌توانم گفتگو را کنار همان وظیفه نگه دارم؟',
                'می‌توانم با شماره موبایل و کد یک‌بارمصرف وارد شوم؟',
            ];
        @endphp
        <section class="landing-faq" aria-labelledby="faq-title">
            <div class="landing-shell landing-faq-grid">
                <div><p class="landing-eyebrow">سؤال‌های متداول</p><h2 id="faq-title">نئووا امکانات اصلی مدیریت پروژه را در اختیار تیم شما قرار می‌دهد.</h2><p>تیم شما می‌تواند کار روزانه را بدون آموزش طولانی در نئووا مدیریت کند.</p></div>
                <div class="landing-question-list">
                    @foreach ($questions as $question)
                        <p><span aria-hidden="true">✓</span>{{ $question }}</p>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-shell landing-closing" aria-labelledby="closing-title">
            <p class="landing-eyebrow">شروع کار</p>
            <h2 id="closing-title">پروژه خود را در نئووا ایجاد کنید.</h2>
            <p>یک فضای کاری بسازید، پروژه را تعریف کنید و اولین وظیفه را به تخته اضافه کنید.</p>
            <div class="landing-hero-actions  justify-center items-center content-center">
                <a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="landing-button">{{ auth()->check() ? 'رفتن به داشبورد' : 'شروع رایگان' }} <span aria-hidden="true">←</span></a>
                {{-- @guest<a href="{{ route('auth') }}" class="landing-text-link">قبلاً حساب دارید؟ وارد شوید</a>@endguest --}}
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-shell landing-footer-inner">
            <a href="{{ url('/') }}" class="landing-logo"><img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا" class="h-8 w-auto object-contain"></a>
            <nav aria-label="پیوندهای پایانی"><a href="#product-preview">تخته پروژه</a><a href="#structure">ساختار</a><a href="#roles">دسترسی‌ها</a><a href="{{ route('auth') }}">ورود</a></nav>
            <p>نئووا؛ مدیریت ساده پروژه و کار تیمی.</p>
        </div>
    </footer>
</body>
</html>
