@props(['dark' => false, 'fluid' => false])

@php
    $navbarNotifications = $navbarNotifications ?? collect();
    $navbarUnreadCount = $navbarUnreadCount ?? 0;
@endphp

<header class="sticky top-0 z-30 border-b border-white/10 bg-[#071B33]/96 shadow-[0_8px_30px_rgba(7,27,51,0.18)] backdrop-blur-xl">
    <div class="{{ $fluid ? 'max-w-[1600px] mx-auto' : 'max-w-7xl mx-auto' }} px-3 sm:px-6 h-14 md:h-16 flex items-center gap-3 md:gap-4">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="shrink-0 inline-flex items-center" aria-label="داشبورد نئووا">
            <img src="{{ asset('assets/logo/horizental-logo-white-transparent.png') }}" alt="نئووا" class="h-7 sm:h-8 w-auto object-contain">
        </a>

        {{-- Content area: slot + search --}}
        <div class="items-center gap-3 min-w-0 flex-1 {{ isset($mobile) && $mobile->isNotEmpty() ? 'hidden md:flex' : 'flex' }}">
            {{ $slot }}

            @if (isset($search) && $search->isNotEmpty())
                <div class="hidden sm:block flex-1 max-w-md">
                    {{ $search }}
                </div>
            @endif
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-0.5 sm:gap-2 shrink-0 {{ isset($mobile) && $mobile->isNotEmpty() ? 'flex-1 justify-end md:flex-none md:justify-start md:ml-auto' : 'ml-auto' }}">
            {{ $actions ?? '' }}

            {{-- Notification Bell --}}
            <div class="relative" x-data="{ notificationsOpen: false }" @click.away="notificationsOpen = false">
                <button
                    type="button"
                    @click="notificationsOpen = !notificationsOpen"
                    class="relative w-11 h-11 md:w-9 md:h-9 rounded-xl md:rounded-lg flex items-center justify-center text-white/75 hover:text-white hover:bg-white/10 transition-colors"
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
                    class="fixed left-3 right-3 top-14 mt-2 w-auto md:absolute md:left-0 md:right-auto md:top-full md:w-[min(340px,calc(100vw-2rem))] bg-white rounded-xl border border-[#E2E8F0] shadow-xl shadow-[#031B4E]/10 overflow-hidden z-50"
                >
                    <div class="flex items-center justify-between px-4 py-3 border-b border-[#F1F5F9]">
                        <span class="text-[13px] font-bold text-[#172B4D]">اعلان‌ها</span>
                        <a href="{{ route('notifications.index') }}" class="text-[10px] font-bold text-[#0069FF]">مشاهده همه</a>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        @forelse ($navbarNotifications as $notification)
                            <a href="{{ route('notifications.open', $notification) }}" class="block px-4 py-3 border-b border-[#F1F5F9] last:border-0 hover:bg-[#F8FAFC] transition-colors {{ $notification->read_at ? '' : 'bg-[#F5F9FF]' }}">
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
            <div class="relative" x-data="{ userDropdown: false }" @click.away="userDropdown = false">
                <button
                    @click="userDropdown = !userDropdown"
                    class="min-w-11 min-h-11 flex items-center justify-center gap-2 px-1.5 sm:px-2 py-1.5 rounded-xl sm:rounded-lg hover:bg-white/10 transition-colors"
                >
                    <div class="w-8 h-8 rounded-full bg-[#1668FF] flex items-center justify-center ring-1 ring-white/20 overflow-hidden">
                        @if (auth()->user()->avatar)
                            <img src="{{ asset('storage/avatars/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->full_name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-[9px] text-white font-bold">{{ auth()->user()->initials }}</span>
                        @endif
                    </div>
                    <span class="text-[11px] font-semibold text-white/85 hidden sm:block">{{ auth()->user()->full_name }}</span>
                    <svg class="hidden sm:block w-3 h-3 text-white/50 transition-transform" :class="userDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div
                    x-show="userDropdown"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute left-0 top-full mt-1.5 w-52 bg-white rounded-xl border border-[#E2E8F0] overflow-hidden z-50"
                >
                    <div class="px-3 py-2.5 border-b border-[#F1F5F9]">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-[#1668FF] flex items-center justify-center overflow-hidden shrink-0">
                                @if (auth()->user()->avatar)
                                    <img src="{{ asset('storage/avatars/' . auth()->user()->avatar) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[10px] text-white font-bold">{{ auth()->user()->initials }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-[#1A1D21] truncate">{{ auth()->user()->full_name }}</p>
                                <p class="text-[10px] text-[#94A3B8]">{{ auth()->user()->phone }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="py-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                            <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            داشبورد
                        </a>
                        <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                            <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            پروفایل
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
    @if (isset($mobile) && $mobile->isNotEmpty())
        <div class="md:hidden border-t border-white/8">
            {{ $mobile }}
        </div>
    @endif
</header>
