@props(['dark' => false])

<div class="relative" x-data="{
    notificationsOpen: false,
    unreadCount: {{ (int) ($navbarUnreadCount ?? 0) }},
    busyNotification: null,
    busyAll: false,
    hasError: false,
    clearUnreadState(row) {
        row?.classList.remove('bg-[#F5F9FF]');
        row?.querySelector('[data-unread-dot]')?.remove();
        row?.querySelector('[data-read-button]')?.remove();
    },
    async markNotificationRead(id, event) {
        event.preventDefault(); event.stopPropagation();
        if (this.busyNotification === id || this.busyAll) return;
        this.busyNotification = id; this.hasError = false;
        try {
            const response = await fetch('{{ route('notifications.read', ['notification' => '__notification__']) }}'.replace('__notification__', id), { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('notification_read_failed');
            const data = await response.json();
            this.clearUnreadState(this.$root.querySelector(`[data-notification-id='${id}']`));
            this.unreadCount = Number.isFinite(data.unread_count) ? data.unread_count : Math.max(0, this.unreadCount - 1);
        } catch (error) { this.hasError = true; }
        finally { this.busyNotification = null; }
    },
    async markAllRead(event) {
        event.preventDefault(); event.stopPropagation();
        if (this.busyAll || this.unreadCount === 0) return;
        this.busyAll = true; this.hasError = false;
        try {
            const response = await fetch('{{ route('notifications.read-all') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('notifications_read_failed');
            const data = await response.json();
            this.$root.querySelectorAll('[data-notification-id]').forEach(row => this.clearUnreadState(row));
            this.unreadCount = Number.isFinite(data.unread_count) ? data.unread_count : 0;
        } catch (error) { this.hasError = true; }
        finally { this.busyAll = false; }
    }
}" @click.away="notificationsOpen = false">
    <button type="button" @click="notificationsOpen = !notificationsOpen" class="relative w-11 h-11 md:w-9 md:h-9 rounded-xl md:rounded-lg flex items-center justify-center {{ $dark ? 'text-white/75 hover:text-white hover:bg-white/10' : 'text-[#64788A] hover:text-[#102A43] hover:bg-[#F1F7FC]' }} transition-colors" aria-label="اعلان‌ها" :aria-expanded="notificationsOpen">
        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0m6 0H9"/></svg>
        <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '۹+' : unreadCount" class="absolute -top-1 -left-1 min-w-4 h-4 px-1 rounded-full bg-[#EF4444] text-white text-[8px] font-bold flex items-center justify-center {{ $dark ? 'ring-2 ring-[#031B4E]' : 'ring-2 ring-white' }}"></span>
    </button>

    <div x-show="notificationsOpen" x-cloak x-transition class="fixed left-3 right-3 top-14 mt-2 w-auto md:absolute md:left-0 md:right-auto md:top-full md:w-[min(340px,calc(100vw-2rem))] bg-white rounded-xl border border-[#E2E8F0] shadow-xl shadow-[#031B4E]/10 overflow-hidden z-50">
        <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-[#F1F5F9]">
            <span class="text-[13px] font-bold text-[#172B4D]">اعلان‌ها</span>
            <button type="button" x-show="unreadCount > 0" @click="markAllRead($event)" :disabled="busyAll" class="text-[10px] font-bold text-[#0069FF] disabled:opacity-50">
                <span x-show="!busyAll">خواندن همه</span><span x-show="busyAll">در حال ثبت…</span>
            </button>
        </div>

        <p x-show="hasError" class="border-b border-red-100 bg-red-50 px-4 py-2 text-[10px] text-red-600" role="alert">ثبت تغییر انجام نشد. دوباره تلاش کنید.</p>

        <div class="max-h-80 overflow-y-auto">
            @forelse (($navbarNotifications ?? collect()) as $notification)
                <div data-notification-id="{{ $notification->id }}" class="flex items-center gap-2 px-4 py-3 border-b border-[#F1F5F9] last:border-0 hover:bg-[#F8FAFC] transition-colors {{ $notification->read_at ? '' : 'bg-[#F5F9FF]' }}">
                    <a href="{{ route('notifications.open', $notification) }}" class="flex min-w-0 flex-1 gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#E8F0FE] text-[#0069FF] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H2v-2a4 4 0 014-4h3m6-6a4 4 0 11-8 0 4 4 0 018 0zm6 3v6m3-3h-6"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[11px] leading-5 text-[#334155]">{{ $notification->data['message'] ?? 'اعلان جدید' }}</p>
                            <p class="text-[9px] text-[#94A3B8] mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                    @if (! $notification->read_at)
                        <button type="button" data-read-button @click="markNotificationRead('{{ $notification->id }}', $event)" :disabled="busyNotification === '{{ $notification->id }}' || busyAll" class="shrink-0 rounded-lg border border-[#D8D0C3] px-2 py-1.5 text-[9px] font-bold text-[#66716B] hover:border-[#18212B] hover:text-[#18212B] disabled:opacity-50">خوانده شد</button>
                        <span data-unread-dot class="h-1.5 w-1.5 shrink-0 rounded-full bg-[#D86F57]" aria-label="خوانده نشده"></span>
                    @endif
                </div>
            @empty
                <div class="px-4 py-8 text-center"><p class="text-xs text-[#94A3B8]">اعلانی وجود ندارد</p></div>
            @endforelse
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-[#F1F5F9] px-4 py-3">
            <a href="{{ route('notifications.index') }}" class="text-[10px] font-bold text-[#475569]">مشاهده همه</a>
            <a x-show="unreadCount > 0" href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="text-[10px] font-bold text-[#0069FF]">خوانده‌نشده‌ها (<span x-text="unreadCount"></span>)</a>
        </div>
    </div>
</div>
