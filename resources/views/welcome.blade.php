<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f4ec">
    <title>نئووا | مدیریت روشن کار تیمی</title>
    <meta name="description" content="نئووا کارها، مسئول‌ها، موعدها و گفت‌وگوهای هر پروژه را در یک جای روشن نگه می‌دارد.">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:title" content="نئووا | مدیریت روشن کار تیمی">
    <meta property="og:description" content="کار تیمی، بدون گم‌شدن بین پیام‌ها.">
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
            'description' => 'ابزار مدیریت پروژه و کار تیمی با فضای کاری، تخته پروژه، وظیفه، چک‌لیست و گفت‌وگو.',
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
                <a href="{{ route('dashboard') }}" class="landing-text-link">داشبورد</a>
                <a href="{{ route('dashboard') }}" class="landing-button landing-button--small">ادامه کار</a>
            @else
                <a href="{{ route('auth') }}" class="landing-text-link">ورود</a>
                <a href="{{ route('auth') }}" class="landing-button landing-button--small">شروع رایگان</a>
            @endauth
        </nav>
    </header>

    <main id="main-content">
        <section class="landing-shell landing-hero" aria-labelledby="landing-title">
            <div class="landing-hero-copy">
                <p class="landing-eyebrow">برای تیم‌های کوچک و جدی</p>
                <h1 id="landing-title">کار تیمی را روشن نگه دارید.</h1>
                <p class="landing-lead">نئووا کارها، مسئول‌ها، موعدها و گفت‌وگوهای پروژه را کنار هم نگه می‌دارد؛ جایی که همه می‌دانند چه چیزی در جریان است و قدم بعدی چیست.</p>
                <div class="landing-hero-actions">
                    <a href="#product-preview" class="landing-button">اول تخته را ببینید <span aria-hidden="true">←</span></a>
                    <a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="landing-text-link">{{ auth()->check() ? 'رفتن به داشبورد' : 'شروع رایگان' }}</a>
                </div>
                <p class="landing-reassurance">ورود با شماره موبایل؛ بدون فرم‌های طولانی و راه‌اندازی سنگین.</p>
            </div>

            <div id="product-preview" class="landing-product-frame" aria-label="نمونه واقعی تخته پروژه">
                <div class="landing-product-bar">
                    <div><small>محصول / NEO</small><strong>بازطراحی محصول</strong></div>
                    <span class="landing-product-status">در جریان</span>
                </div>
                <div class="landing-board-grid">
                    <div class="landing-board-column">
                        <div class="landing-column-heading"><span><i class="landing-dot landing-dot--blue"></i>برای انجام</span><b>۲</b></div>
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
                        <article class="landing-task"><small>NEO-027</small><strong>آماده‌سازی انتشار</strong><em><i style="width:75%"></i></em><span>۳ از ۴ مورد کامل</span></article>
                    </div>
                </div>
                <p class="landing-product-caption">تخته، مسئولیت و قدم بعدی؛ در یک نگاه.</p>
            </div>
        </section>

        <section class="landing-proof" aria-label="اصول نئووا">
            <div class="landing-shell landing-proof-grid">
                <p><b>یک جا</b><span>کارها و تصمیم‌ها کنار همان پروژه می‌مانند.</span></p>
                <p><b>یک قدم بعدی</b><span>هر نفر می‌داند حالا باید چه کاری انجام دهد.</span></p>
                <p><b>کم‌دردسر</b><span>ابزار نباید خودش به یک پروژه تازه تبدیل شود.</span></p>
            </div>
        </section>

        <section class="landing-shell landing-problem" aria-labelledby="problem-title">
            <div class="landing-section-label">مسئله</div>
            <div>
                <h2 id="problem-title">کار تیمی نباید بین پیام‌ها، جلسه‌ها و حافظه آدم‌ها پخش شود.</h2>
                <p>وقتی تصمیمی در گفتگو گم می‌شود، موعدی صاحب ندارد، یا کسی نمی‌داند اولویت چیست، مشکل از تلاش تیم نیست؛ مشکل از جایی است که کار را نگه می‌دارد.</p>
                <p>نئووا یک جای روشن برای جریان واقعی کار می‌سازد: فضای کاری، پروژه، تخته، وظیفه، مسئول، موعد و گفت‌وگو.</p>
            </div>
        </section>

        <section class="landing-shell landing-letter-section" aria-labelledby="letter-title">
            <div class="landing-letter-heading">
                <p class="landing-eyebrow">یک نامه از طرف نئووا</p>
                <h2 id="letter-title">ابزار قرار است کار را روشن‌تر کند، نه اینکه خودش به یک کار تازه تبدیل شود.</h2>
            </div>
            <div class="landing-letter">
                <p>سلام،</p>
                <p>احتمالاً کار کم ندارید؛ مسئله این است که کارها بین پیام‌رسان، جلسه، فایل و حافظه آدم‌ها پخش شده‌اند.</p>
                <p>یک نفر نمی‌داند چه چیزی اولویت دارد. یک کار موعد دارد اما صاحب ندارد. تصمیمی در گفتگو گرفته می‌شود و چند روز بعد کسی پیدایش نمی‌کند.</p>
                <p>نئووا برای جمع‌کردن همین پراکندگی ساخته شده: هر تیم یک فضای کاری، هر جریان یک پروژه، هر پروژه یک تخته، و هر کار یک جای مشخص برای مسئول، موعد، توضیح، چک‌لیست و گفتگو.</p>
                <p>قرار نیست برای مدیریت کار، خودِ ابزار به یک پروژه تازه تبدیل شود. نئووا باید سریع فهمیده شود، سبک بماند و هر روز واقعاً استفاده شود.</p>
                <div class="landing-letter-signature">
                    <img src="{{ asset('assets/signatures/amir-mehrabian-signature.png') }}" alt="امضای امیر مهرابیان">
                    <span>تیم نئووا</span>
                </div>
            </div>
        </section>

        <section class="landing-shell landing-workflow" aria-labelledby="workflow-title">
            <div class="landing-section-intro">
                <p class="landing-eyebrow">چیزی که هر روز استفاده می‌کنید</p>
                <h2 id="workflow-title">سه چیز را روشن کنید: ساختار، مسئولیت، جزئیات.</h2>
            </div>

            <article class="landing-workflow-row" id="structure">
                <div class="landing-workflow-copy"><span>۰۱ / ساختار</span><h3>پروژه را قابل دیدن کنید.</h3><p>فضای کاری تیم را جدا می‌کند، پروژه جریان را، و ستون‌ها نشان می‌دهند کارها الان کجا هستند.</p></div>
                <div class="landing-mini-panel landing-structure-panel"><div><small>فضای کاری</small><strong>تیم محصول</strong><span>۸ عضو</span></div><b>←</b><div><small>پروژه</small><strong>نسخه جدید</strong><span>۴ ستون</span></div><b>←</b><div><small>وضعیت</small><strong>در حال انجام</strong><span>۱۲ وظیفه</span></div></div>
            </article>

            <article class="landing-workflow-row" id="roles">
                <div class="landing-workflow-copy"><span>۰۲ / مسئولیت</span><h3>مسئولیت را حدس‌زدنی نگذارید.</h3><p>برای هر کار مسئول و موعد مشخص کنید و برای هر عضو همان سطح دسترسی را بدهید که لازم دارد.</p></div>
                <div class="landing-mini-panel landing-people-panel"><div class="landing-panel-heading"><strong>اعضای پروژه</strong><button type="button">دعوت عضو</button></div><div><i>ن</i><strong>نیلوفر احمدی</strong><span>مالک</span></div><div><i class="is-coral">م</i><strong>محمد رضایی</strong><span>مدیر</span></div><div><i class="is-mint">س</i><strong>سارا میرزایی</strong><span>فقط مشاهده</span></div></div>
            </article>

            <article class="landing-workflow-row" id="task-details">
                <div class="landing-workflow-copy"><span>۰۳ / جزئیات</span><h3>جزئیات را کنار خود کار نگه دارید.</h3><p>توضیح، برچسب، چک‌لیست و گفتگو در صفحه همان وظیفه می‌مانند؛ نه در چند ابزار پراکنده.</p></div>
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
                <div><p class="landing-eyebrow">سؤال‌های معمول</p><h2 id="faq-title">برای شروع، جواب بیشتر سؤال‌ها «بله» است.</h2><p>نئووا برای کار روزانه تیم ساخته شده؛ نه برای اینکه تیم روزهایش را صرف یادگیری ابزار کند.</p></div>
                <div class="landing-question-list">
                    @foreach ($questions as $question)
                        <p><span aria-hidden="true">✓</span>{{ $question }}</p>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="landing-shell landing-closing" aria-labelledby="closing-title">
            <p class="landing-eyebrow">قدم بعدی</p>
            <h2 id="closing-title">یک پروژه واقعی را همین امروز وارد نئووا کنید.</h2>
            <p>یک فضای کاری بسازید، پروژه‌تان را تعریف کنید و اولین کار را روی تخته بگذارید.</p>
            <div class="landing-hero-actions">
                <a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="landing-button">{{ auth()->check() ? 'رفتن به داشبورد' : 'شروع رایگان' }} <span aria-hidden="true">←</span></a>
                @guest<a href="{{ route('auth') }}" class="landing-text-link">قبلاً حساب دارید؟ وارد شوید</a>@endguest
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-shell landing-footer-inner">
            <a href="{{ url('/') }}" class="landing-logo"><img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا" class="h-8 w-auto object-contain"></a>
            <nav aria-label="پیوندهای پایانی"><a href="#product-preview">تخته پروژه</a><a href="#structure">ساختار</a><a href="#roles">دسترسی‌ها</a><a href="{{ route('auth') }}">ورود</a></nav>
            <p>نئووا؛ کار تیمی روشن‌تر.</p>
        </div>
    </footer>
</body>
</html>
