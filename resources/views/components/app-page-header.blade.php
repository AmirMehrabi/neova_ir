@props(['title', 'backUrl' => null])

<header class="sticky top-0 z-30 bg-white/95 backdrop-blur-xl border-b border-[#E2E8F0]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 flex items-center gap-3">
        @if ($backUrl)
            <a href="{{ $backUrl }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#64748B] hover:text-[#0069FF] hover:bg-[#F1F5F9]">
                <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
        @endif
        <a href="{{ route('dashboard') }}" class="w-8 h-8 rounded-lg bg-[#0069FF] text-white flex items-center justify-center font-black text-xs">N</a>
        <h1 class="text-sm font-bold text-[#172B4D] truncate">{{ $title }}</h1>
        <div class="mr-auto flex items-center gap-2">
            <x-notification-menu />
            <div class="w-8 h-8 rounded-full bg-[#031B4E] text-white flex items-center justify-center text-[10px] font-bold">
                {{ mb_substr(auth()->user()->full_name, 0, 1) }}
            </div>
        </div>
    </div>
</header>
