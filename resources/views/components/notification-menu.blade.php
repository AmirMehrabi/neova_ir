@props(['dark' => false])

<div class="relative" x-data="{ notificationsOpen: false }" @click.away="notificationsOpen = false">
    <button
        type="button"
        @click="notificationsOpen = !notificationsOpen"
        class="relative w-8 h-8 rounded-lg flex items-center justify-center text-white/75 hover:text-white hover:bg-white/10 transition-colors"
        aria-label="اعلان‌ها"
    >
        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0m6 0H9"/>
        </svg>
        @if (($navbarUnreadCount ?? 0) > 0)
            <span class="absolute -top-1 -left-1 min-w-4 h-4 px-1 rounded-full bg-[#EF4444] text-white text-[8px] font-bold flex items-center justify-center ring-2 ring-[#071B33]">
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
            @forelse (($navbarNotifications ?? collect()) as $notification)
                <a href="{{ route('notifications.index') }}" class="block px-4 py-3 border-b border-[#F1F5F9] last:border-0 hover:bg-[#F8FAFC] transition-colors {{ $notification->read_at ? '' : 'bg-[#F5F9FF]' }}">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#E8F0FE] text-[#0069FF] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m6-6a4 4 0 11-8 0 4 4 0 018 0zm6 3v6m3-3h-6"/>
                            </svg>
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
