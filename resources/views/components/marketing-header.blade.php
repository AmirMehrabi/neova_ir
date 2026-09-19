<header class="nl-shell nl-header" id="top">
    <a href="{{ route('home') }}" class="nl-logo" aria-label="نئووا، صفحه اصلی"><img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" width="433" height="120" alt="نئووا"></a>
    <nav class="nl-nav" aria-label="ناوبری اصلی"><a href="{{ url('project-management') }}">مدیریت پروژه</a><a href="{{ url('project-manager') }}">مدیر پروژه</a><a href="{{ url('features/task-management') }}">امکانات</a></nav>
    <div class="nl-header-actions">@auth<a href="{{ route('dashboard') }}" class="nl-text-link">امروز من</a><a href="{{ route('dashboard') }}" class="nl-button nl-button--small">ادامه کار <span aria-hidden="true">←</span></a>@else<a href="{{ route('auth') }}" class="nl-text-link">ورود</a><a href="{{ route('auth') }}" class="nl-button nl-button--small">شروع رایگان <span aria-hidden="true">←</span></a>@endauth</div>
</header>
