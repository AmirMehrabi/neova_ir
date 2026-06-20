<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f0e7">
    <title>نئووا | مدیریت روشن کار تیمی</title>
    <meta name="description" content="نئووا کارها، مسئول‌ها، موعدها و گفت‌وگوهای هر پروژه را در یک جای روشن نگه می‌دارد.">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-white.png') }}">
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
<body class="neova-home" x-data="{ moved: false }">
    <a href="#main-content" class="neova-skip-link">رفتن به محتوای اصلی</a>

    <header class="neova-shell neova-header" aria-label="سربرگ">
        <a href="{{ url('/') }}" class="neova-wordmark" aria-label="نئووا، صفحه اصلی">
                        <img src="{{ asset('assets/logo/horizental-logo-black.png') }}" alt="نئووا" class="h-12 bg-white object-contain rounded-2xl  p-1 mx-auto ">

        </a>

        <nav class="neova-header-actions" aria-label="دسترسی سریع">
            @auth
                <a href="{{ route('dashboard') }}" class="neova-text-link">داشبورد</a>
                <a href="{{ route('dashboard') }}" class="neova-button neova-button--small">ادامه کار</a>
            @else
                <a href="{{ route('auth') }}" class="neova-text-link">ورود</a>
                <a href="{{ route('auth') }}" class="neova-button neova-button--small">شروع رایگان</a>
            @endauth
        </nav>
    </header>

    <main id="main-content">
        <section class="neova-shell neova-opening" aria-labelledby="home-title">
            <div class="neova-opening-copy">
                <nav class="neova-editorial-index" aria-label="معرفی کوتاه امکانات">
                    <a href="#project-board"><strong>تخته پروژه</strong><span>کارها را همان‌جا که پیش می‌روند ببینید</span></a>
                    <a href="#clear-work"><strong>فضای کاری</strong><span>پروژه‌ها و آدم‌ها کنار هم</span></a>
                    <a href="#access"><strong>دعوت تیم</strong><span>با شماره موبایل، سریع و روشن</span></a>
                    <a href="#access"><strong>نقش‌ها</strong><span>مالک، مدیر، عضو یا فقط مشاهده</span></a>
                    <a href="#task-details"><strong>وظیفه‌ها</strong><span>مسئول، موعد، برچسب و چک‌لیست</span></a>
                </nav>

                <div class="neova-hero-copy">
                    <h1 id="home-title">کار تیمی، بدون گم‌شدن بین پیام‌ها.</h1>
                    <p>نئووا کارها، مسئول‌ها، موعدها و گفت‌وگوهای هر پروژه را در یک جای روشن نگه می‌دارد.</p>
                    <div class="neova-hero-actions">
                        <a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="neova-button">
                            {{ auth()->check() ? 'رفتن به داشبورد' : 'رایگان شروع کنید' }}
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <span>ورود با شماره موبایل؛ بدون فرم‌های طولانی</span>
                    </div>
                </div>
            </div>

            <div id="project-board" class="neova-board-wrap">
                <div class="neova-note neova-note--board" aria-hidden="true">همه‌چیز جلوی چشم تیم</div>
                <div class="neova-board" aria-label="نمونه تخته پروژه بازطراحی محصول">
                    <div class="neova-board-head">
                        <div>
                            <span class="neova-board-kicker">محصول / NEO</span>
                            <h2>بازطراحی محصول</h2>
                        </div>
                        <div class="neova-avatars" aria-label="سه عضو پروژه">
                            <span>م</span><span>ر</span><span>س</span>
                        </div>
                    </div>
                    <div class="neova-board-grid">
                        <div class="neova-column">
                            <div class="neova-column-head"><span><i class="is-blue"></i>برای انجام</span><b>۲</b></div>
                            <article class="neova-task">
                                <small>NEO-014</small>
                                <h3>تحقیق کاربران</h3>
                                <div class="neova-task-meta"><span class="is-mint">تحقیق</span><time>۲۸ خرداد</time></div>
                            </article>
                            <article class="neova-task">
                                <small>NEO-018</small>
                                <h3>متن صفحه معرفی</h3>
                                <div class="neova-task-meta"><span>محتوا</span><time>۳۰ خرداد</time></div>
                            </article>
                        </div>
                        <div class="neova-column">
                            <div class="neova-column-head"><span><i class="is-amber"></i>در حال انجام</span><b>۲</b></div>
                            <article class="neova-task neova-task--active">
                                <small>NEO-021</small>
                                <h3>طراحی جریان ثبت‌نام</h3>
                                <div class="neova-progress"><i style="width: 66%"></i></div>
                                <div class="neova-task-meta"><span>طراحی</span><time>امروز</time></div>
                            </article>
                            <article class="neova-task">
                                <small>NEO-024</small>
                                <h3>اتصال پیامک دعوت</h3>
                                <div class="neova-task-meta"><span class="is-mint">فنی</span><time>فردا</time></div>
                            </article>
                        </div>
                        <div class="neova-column">
                            <div class="neova-column-head"><span><i class="is-violet"></i>بازبینی</span><b>۱</b></div>
                            <article class="neova-task">
                                <small>NEO-027</small>
                                <h3>آماده‌سازی انتشار</h3>
                                <div class="neova-checkline"><span>۳ از ۴</span><i><b style="width: 75%"></b></i></div>
                                <div class="neova-task-meta"><span>انتشار</span><time>۲ تیر</time></div>
                            </article>
                        </div>
                        <div class="neova-column neova-column--done">
                            <div class="neova-column-head"><span><i class="is-mint"></i>انجام شد</span><b>۱</b></div>
                            <article class="neova-task">
                                <small>NEO-009</small>
                                <h3>تعریف مسیر پروژه</h3>
                                <div class="neova-task-meta"><span class="is-mint">تمام</span><time>۲۵ خرداد</time></div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="neova-proof-band" aria-label="ویژگی‌های اصلی">
            <div class="neova-shell neova-proof-list">
                <p><span>۰۱</span>هر پروژه یک تخته روشن دارد.</p>
                <p><span>۰۲</span>هر کار یک مسئول و موعد مشخص دارد.</p>
                <p><span>۰۳</span>بحث‌ها کنار خود کار می‌مانند.</p>
                <p><span>۰۴</span>سطح دسترسی هر عضو معلوم است.</p>
            </div>
        </section>

        <section class="neova-shell neova-letter-section">
            <div class="neova-section-heading">
                <h2>اگر کار تیم‌تان این‌طور پیش می‌رود، نئووا برای شماست.</h2>
                <span class="neova-handline" aria-hidden="true"></span>
            </div>
            <div class="neova-letter">
                <p>سلام،</p>
                <p>احتمالاً کار کم ندارید؛ مسئله این است که کارها بین پیام‌رسان، جلسه، فایل و حافظه آدم‌ها پخش شده‌اند.</p>
                <p>یک نفر نمی‌داند چه چیزی اولویت دارد. یک کار موعد دارد اما صاحب ندارد. تصمیمی در گفتگو گرفته می‌شود و چند روز بعد کسی پیدایش نمی‌کند.</p>
                <p>نئووا برای جمع‌کردن همین پراکندگی ساخته شده: هر تیم یک فضای کاری، هر جریان یک پروژه، هر پروژه یک تخته، و هر کار یک جای مشخص برای مسئول، موعد، توضیح، چک‌لیست و گفتگو.</p>
                <p>قرار نیست برای مدیریت کار، خودِ ابزار به یک پروژه تازه تبدیل شود. نئووا باید سریع فهمیده شود، سبک بماند و هر روز واقعاً استفاده شود.</p>
                <div class="neova-signature">
                    <svg viewBox="0 0 150 50" aria-hidden="true"><path d="M5 38c18-4 22-30 28-25 4 3-8 22-2 20 8-3 17-20 22-16 4 3-8 16-3 17 7 1 14-18 20-14 4 3-7 14-1 15 9 2 19-11 28-8 4 2-1 8 8 8 13 0 22-5 38-4"/></svg>
                    <strong>تیم نئووا</strong>
                </div>
            </div>
        </section>

        <section id="clear-work" class="neova-shell neova-evidence-section">
            <div class="neova-evidence-row">
                <div class="neova-evidence-title">
                    <span>ساختار</span>
                    <h2>کار روشن، از ساختار روشن می‌آید.</h2>
                </div>
                <div class="neova-evidence-body">
                    <p>فضاهای کاری تیم را جدا می‌کنند؛ پروژه‌ها جریان‌ها را؛ ستون‌ها وضعیت را؛ و وظیفه‌ها مسئولیت را.</p>
                    <div class="neova-structure-map" aria-label="ساختار نئووا">
                        <div><small>فضای کاری</small><strong>تیم محصول</strong><span>۸ عضو</span></div>
                        <svg viewBox="0 0 60 20" aria-hidden="true"><path d="M4 10h48m-7-6 7 6-7 6"/></svg>
                        <div><small>پروژه</small><strong>نسخه جدید</strong><span>۴ ستون</span></div>
                        <svg viewBox="0 0 60 20" aria-hidden="true"><path d="M4 10h48m-7-6 7 6-7 6"/></svg>
                        <div><small>وظیفه</small><strong>جریان ثبت‌نام</strong><span>در حال انجام</span></div>
                    </div>
                </div>
            </div>

            <div id="access" class="neova-evidence-row">
                <div class="neova-evidence-title">
                    <span>دسترسی</span>
                    <h2>دسترسی‌ها حدس‌زدنی نیستند.</h2>
                </div>
                <div class="neova-evidence-body">
                    <p>مالک، مدیر، عضو و مشاهده‌گر هرکدام محدوده مشخصی دارند. دعوت‌ها با شماره موبایل ارسال و وضعیت آن‌ها پیگیری می‌شود.</p>
                    <div class="neova-people-panel">
                        <div class="neova-panel-head"><strong>اعضای فضای کاری</strong><button type="button">دعوت عضو</button></div>
                        <div class="neova-person"><span class="neova-person-avatar">ن</span><p><strong>نیلوفر احمدی</strong><small>۰۹۱۲•••۴۶۳۱</small></p><b>مالک</b></div>
                        <div class="neova-person"><span class="neova-person-avatar is-coral">م</span><p><strong>محمد رضایی</strong><small>۰۹۳۵•••۱۲۰۸</small></p><button type="button">مدیر <svg viewBox="0 0 12 8"><path d="m1 1 5 5 5-5"/></svg></button></div>
                        <div class="neova-person"><span class="neova-person-avatar is-mint">س</span><p><strong>سارا میرزایی</strong><small>۰۹۹۱•••۸۰۷۲</small></p><button type="button">فقط مشاهده <svg viewBox="0 0 12 8"><path d="m1 1 5 5 5-5"/></svg></button></div>
                    </div>
                </div>
            </div>

            <div id="task-details" class="neova-evidence-row">
                <div class="neova-evidence-title">
                    <span>جزئیات</span>
                    <h2>هر چیز، کنار همان کاری می‌ماند که به آن مربوط است.</h2>
                </div>
                <div class="neova-evidence-body">
                    <p>توضیح، برچسب، موعد، مسئول، چک‌لیست و گفتگو در صفحه همان وظیفه ثبت می‌شوند؛ نه در چند ابزار پراکنده.</p>
                    <div class="neova-task-panel">
                        <div class="neova-task-panel-main">
                            <small>NEO-021 / در حال انجام</small>
                            <h3>طراحی جریان ثبت‌نام</h3>
                            <h4>چک‌لیست</h4>
                            <label><input type="checkbox" checked disabled><span>متن پیامک تأیید</span></label>
                            <label><input type="checkbox" checked disabled><span>حالت خطا</span></label>
                            <label><input type="checkbox" disabled><span>بازبینی موبایل</span></label>
                            <h4>گفتگو</h4>
                            <div class="neova-comment"><span>م</span><p><strong>محمد</strong> نسخه موبایل را تا عصر بازبینی می‌کنم.</p></div>
                        </div>
                        <aside>
                            <dl>
                                <div><dt>مسئول‌ها</dt><dd><span>ن</span><span>م</span></dd></div>
                                <div><dt>موعد</dt><dd>۳۰ خرداد</dd></div>
                                <div><dt>برچسب‌ها</dt><dd><b>طراحی</b><b class="is-mint">ثبت‌نام</b></dd></div>
                            </dl>
                        </aside>
                    </div>
                </div>
            </div>
        </section>

        @php
            $questions = [
                'می‌توانم برای هر تیم فضای کاری جدا داشته باشم؟',
                'می‌توانم چند پروژه را در یک فضای کاری مدیریت کنم؟',
                'می‌توانم وضعیت کارها را روی تخته ببینم؟',
                'می‌توانم ستون‌های پروژه را متناسب با فرایند تیم بچینم؟',
                'می‌توانم کارها را بین ستون‌ها جابه‌جا کنم؟',
                'می‌توانم برای هر کار مسئول و موعد تعیین کنم؟',
                'می‌توانم به کارها برچسب و اولویت بدهم؟',
                'می‌توانم برای هر وظیفه چک‌لیست بسازم؟',
                'می‌توانم گفتگو را کنار همان وظیفه نگه دارم؟',
                'می‌توانم اعضا را با شماره موبایل دعوت کنم؟',
                'می‌توانم نقش و سطح دسترسی اعضا را کنترل کنم؟',
                'می‌توانم بعضی اعضا را فقط مشاهده‌گر کنم؟',
                'می‌توانم اعلان دعوت‌ها را در خود نئووا ببینم؟',
                'می‌توانم با شماره موبایل و کد یک‌بارمصرف وارد شوم؟',
            ];
        @endphp
        <section class="neova-capabilities">
            <div class="neova-shell neova-capabilities-grid">
                <div class="neova-capabilities-title">
                    <h2>این سؤال‌ها یک جواب دارند: بله.</h2>
                    <p>چیزهایی که یک تیم برای روشن نگه‌داشتن کار روزانه لازم دارد.</p>
                </div>
                <div class="neova-question-list">
                    @foreach ($questions as $question)
                        <p><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4 10-10"/></svg>{{ $question }}<strong>بله</strong></p>
                    @endforeach
                    <a href="#project-board" class="neova-underlined-link">همه امکانات نئووا را ببینید</a>
                </div>
            </div>
        </section>

        <section class="neova-shell neova-closing">
            <div class="neova-closing-copy">
                <h2>یک پروژه واقعی را همین امروز وارد نئووا کنید.</h2>
                <p>نه ارائه لازم است، نه جلسه راه‌اندازی. یک فضای کاری بسازید، پروژه‌تان را تعریف کنید و اولین کار را روی تخته بگذارید.</p>
                <div class="neova-hero-actions">
                    <a href="{{ auth()->check() ? route('dashboard') : route('auth') }}" class="neova-button">
                        {{ auth()->check() ? 'رفتن به داشبورد' : 'شروع رایگان' }}
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    @guest
                        <a href="{{ route('auth') }}" class="neova-text-link">قبلاً حساب دارید؟ وارد شوید</a>
                    @endguest
                </div>
                <small>بدون فرم طولانی؛ ورود با شماره موبایل</small>
            </div>

            <div class="neova-mini-board" @mouseenter="moved = true" @mouseleave="moved = false" @focusin="moved = true">
                <div>
                    <span>برای انجام</span>
                    <article :class="{ 'is-moved': moved }">
                        <small>NEO-001</small>
                        <strong>اولین کار پروژه</strong>
                        <i></i>
                    </article>
                </div>
                <svg viewBox="0 0 48 24" aria-hidden="true"><path d="M3 12h38m-7-7 7 7-7 7"/></svg>
                <div class="is-done">
                    <span>انجام شد</span>
                    <article :class="{ 'is-arrived': moved }">
                        <small>NEO-001</small>
                        <strong>اولین کار پروژه</strong>
                        <i></i>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="neova-footer">
        <div class="neova-shell">
            <div class="neova-footer-top">
                <a href="{{ url('/') }}" class="neova-wordmark">
                    <span class="neova-mark" aria-hidden="true"><i></i><i></i><i></i></span>
                    <span>نئووا</span>
                </a>
                <nav aria-label="پیوندهای پایانی">
                    <a href="#clear-work">درباره نئووا</a>
                    <a href="#project-board">تخته پروژه</a>
                    <a href="#access">دسترسی‌ها</a>
                    <a href="{{ route('auth') }}">ورود</a>
                </nav>
            </div>
            <div class="neova-footer-bottom">
                <p>نئووا؛ کار تیمی روشن‌تر.</p>
                <a href="#main-content">بازگشت به بالا <span aria-hidden="true">↑</span></a>
            </div>
        </div>
    </footer>
</body>
</html>
