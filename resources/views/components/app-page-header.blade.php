@props(['title', 'backUrl' => null])

<header class="sticky top-0 z-30 bg-[#071B33]/96 backdrop-blur-xl border-b border-white/10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">
        @if ($backUrl)
            <a href="{{ $backUrl }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10">
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        @endif
        <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/logo/horizental-logo-white-transparent.png') }}" alt="نئووا" class="h-7 w-auto object-contain"></a>
        <span class="h-5 w-px bg-white/15"></span>
        <h1 class="text-sm font-bold text-white truncate">{{ $title }}</h1>
        <div class="mr-auto flex items-center gap-2">
            <x-notification-menu />
            <div class="w-8 h-8 rounded-full bg-[#031B4E] text-white flex items-center justify-center text-[10px] font-bold">
                {{ mb_substr(auth()->user()->full_name, 0, 1) }}
            </div>
        </div>
    </div>
</header>
