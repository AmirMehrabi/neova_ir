<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#f7f5ef">
    <title>{{ $page['title'] }}</title><meta name="description" content="{{ $page['description'] }}"><link rel="canonical" href="{{ url($slug) }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    <meta property="og:type" content="article"><meta property="og:locale" content="fa_IR"><meta property="og:site_name" content="نئووا"><meta property="og:title" content="{{ $page['title'] }}"><meta property="og:description" content="{{ $page['description'] }}"><meta property="og:url" content="{{ url($slug) }}">
    <meta name="twitter:card" content="summary"><meta name="twitter:title" content="{{ $page['title'] }}"><meta name="twitter:description" content="{{ $page['description'] }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="application/ld+json">{!! json_encode(["\x40context" => 'https://schema.org', "\x40type" => str_starts_with($slug, 'features/') || str_starts_with($slug, 'solutions/') || str_starts_with($slug, 'alternatives/') ? 'WebPage' : 'Article', 'headline' => $page['h1'], 'description' => $page['description'], 'inLanguage' => 'fa-IR', 'mainEntityOfPage' => url($slug), 'publisher' => ["\x40type" => 'Organization', 'name' => 'نئووا', 'url' => url('/')]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode(["\x40context" => 'https://schema.org', "\x40type" => 'BreadcrumbList', 'itemListElement' => [["\x40type" => 'ListItem', 'position' => 1, 'name' => 'نئووا', 'item' => url('/')], ["\x40type" => 'ListItem', 'position' => 2, 'name' => $page['h1'], 'item' => url($slug)]]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
</head>
<body class="nl-page nl-content-page"><a href="#main-content" class="nl-skip-link">رفتن به محتوای اصلی</a><x-marketing-header />
<main id="main-content">
    <nav class="nl-shell nl-breadcrumb" aria-label="مسیر صفحه"><a href="{{ route('home') }}">نئووا</a><span aria-hidden="true">/</span><span>{{ $page['h1'] }}</span></nav>
    <header class="nl-shell nl-article-hero"><p class="nl-kicker">{{ $page['eyebrow'] }}</p><h1>{{ $page['h1'] }}</h1><p>{{ $page['lead'] }}</p><div class="nl-hero-actions"><a class="nl-button" href="{{ route('auth') }}">رایگان شروع کنید</a><a class="nl-button nl-button--secondary" href="#content">مطالعه راهنما <span aria-hidden="true">↓</span></a></div></header>
    @if(!empty($page['toc']))<nav class="nl-shell nl-toc" aria-label="فهرست مطالب"><strong>در این صفحه</strong>@foreach($page['toc'] as $id => $label)<a href="#{{ $id }}">{{ $label }}</a>@endforeach</nav>@endif
    <div class="nl-shell nl-article-layout" id="content"><article class="nl-article-body">
        @foreach($page['sections'] as $section)<section @if(!empty($section['id'])) id="{{ $section['id'] }}" @endif><h2>{{ $section['title'] }}</h2>@foreach($section['body'] ?? [] as $paragraph)<p>{{ $paragraph }}</p>@endforeach
        @if(!empty($section['items']))<ul>@foreach($section['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>@endif
        @if(!empty($section['cards']))<div class="nl-info-grid">@foreach($section['cards'] as $card)<div><h3>{{ $card[0] }}</h3><p>{{ $card[1] }}</p></div>@endforeach</div>@endif</section>@endforeach
    </article><aside class="nl-article-aside"><span>یک جای روشن برای کار تیم</span><h2>پروژه بعدی را از چت بیرون بیاورید.</h2><p>نئووا برای مدیریت پروژه و وظایف تیم‌های کوچک، ساده و کاملاً فارسی ساخته شده است.</p><a href="{{ route('auth') }}">ساخت فضای کاری رایگان ←</a></aside></div>
    <section class="nl-related"><div class="nl-shell"><p class="nl-section-label">مطالب و راهکارهای مرتبط</p><h2>قدم بعدی را انتخاب کنید</h2><div class="nl-related-grid">@foreach($page['related'] as $relatedKey) @php($related = config('seo_pages')[$relatedKey])<a href="{{ url($relatedKey) }}"><span>راهنمای نئووا</span><strong>{{ $related['h1'] }}</strong><small>مطالعه کنید ←</small></a>@endforeach</div></div></section>
</main><x-marketing-footer /></body></html>
