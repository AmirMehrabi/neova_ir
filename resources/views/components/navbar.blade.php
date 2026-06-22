@props(['dark' => false, 'fluid' => false])

@php
    $navbarNotifications = $navbarNotifications ?? collect();
    $navbarUnreadCount = $navbarUnreadCount ?? 0;
@endphp

<header class="sticky top-0 z-30 {{ $dark ? 'border-b border-white/10 bg-[#031B4E]/95 shadow-lg shadow-[#031B4E]/25 backdrop-blur-xl' : 'bg-white/80 backdrop-blur-xl border-b border-[#E2E8F0]' }}">
    <div class="{{ $fluid ? 'max-w-[1600px] mx-auto' : '' }} px-4 sm:px-6 h-14 flex items-center gap-3">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="shrink-0 {{ $dark ? 'w-9 h-9 rounded-xl bg-[#0069FF] flex items-center justify-center shadow-md shadow-[#0069FF]/25' : 'w-8 h-8 rounded-lg bg-[#0069FF] flex items-center justify-center' }}" aria-label="داشبورد">
            <img src="{{ asset('assets/logo/logo-white.png') }}" alt="نئووا" class="{{ $dark ? 'w-5 h-5' : 'w-4.5 h-4.5' }} object-contain">
        </a>

        {{-- Slot: left side content (back button, breadcrumb, title) --}}
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            {{ $slot }}
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0 ml-auto">
            {{ $actions ?? '' }}

            {{-- Notification Bell --}}
            <div class="relative" x-data="{ notificationsOpen: false }" @click.away="notificationsOpen = false">
                <button
                    type="button"
                    @click="notificationsOpen = !notificationsOpen"
                    class="relative w-8 h-8 rounded-lg flex items-center justify-center transition-colors {{ $dark ? 'text-white/80 hover:text-white hover:bg-white/10' : 'text-[#64748B] hover:text-[#0069FF] hover:bg-[#F1F5F9]' }}"
                    aria-label="اعلان‌ها"
                >
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0m6 0H9"/>
                    </svg>
                    @if ($navbarUnreadCount > 0)
                        <span class="absolute -top-1 -left-1 min-w-4 h-4 px-1 rounded-full bg-[#EF4444] text-white text-[8px] font-bold flex items-center justify-center {{ $dark ? 'ring-2 ring-[#031B4E]' : 'ring-2 ring-white' }}">
                            {{ $navbarUnreadCount > 9 ? '۹+' : $navbarUnreadCount }}
                        </span>
                    @endif
                </button>

                <div
                    x-show="notificationsOpen"
                    x-cloak
                    x-transition
                    class="absolute left-0 top-full mt-2 w-[min(340px,calc(100vw-2rem))] bg-white rounded-xl border border-[#E2E8F0] shadow-xl shadow-[#031B4E]/10 overflow-hidden z-50"
                >
                    <div class="flex items-center justify-between px-4 py-3 border-b border-[#F1F5F9]">
                        <span class="text-[13px] font-bold text-[#172B4D]">اعلان‌ها</span>
                        <a href="{{ route('notifications.index') }}" class="text-[10px] font-bold text-[#0069FF]">مشاهده همه</a>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        @forelse ($navbarNotifications as $notification)
                            <a href="{{ route('notifications.index') }}" class="block px-4 py-3 border-b border-[#F1F5F9] last:border-0 hover:bg-[#F8FAFC] transition-colors {{ $notification->read_at ? '' : 'bg-[#F5F9FF]' }}">
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#E8F0FE] text-[#0069FF] flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m6-6a4 4 0 11-8 0 4 4 0 018 0zm6 3v6m3-3h-6"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] leading-5 text-[#334155]">{{ $notification->data['message'] ?? 'اعلان جدید' }}</p>
                                        <p class="text-[9px] text-[#94A3B8] mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-8 text-center">
                                <p class="text-xs text-[#94A3B8]">اعلان جدیدی ندارید</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- User Dropdown --}}
            <div class="relative" @click.away="userDropdown = false">
                <button
                    @click="userDropdown = !userDropdown"
                    class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-[#F1F5F9] transition-colors"
                >
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#003B8E] to-[#0069FF] flex items-center justify-center">
                        <span class="text-[9px] text-white font-bold">{{ substr(auth()->user()->first_name ?? auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <span class="text-[11px] font-semibold text-[#475569] hidden sm:block">{{ auth()->user()->full_name }}</span>
                    <svg class="w-3 h-3 text-[#94A3B8] transition-transform" :class="userDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div
                    x-show="userDropdown"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute left-0 top-full mt-1.5 w-52 bg-white rounded-xl border border-[#E2E8F0] overflow-hidden z-50"
                >
                    <div class="px-3 py-2.5 border-b border-[#F1F5F9]">
                        <p class="text-[11px] font-bold text-[#1A1D21]">{{ auth()->user()->full_name }}</p>
                        <p class="text-[10px] text-[#94A3B8]">{{ auth()->user()->phone }}</p>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                            <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            داشبورد
                        </a>
                    </div>
                    <div class="border-t border-[#F1F5F9] py-1">
                        <form action="{{ route('auth.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-[11px] font-medium text-[#EF4444] hover:bg-red-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                خروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
