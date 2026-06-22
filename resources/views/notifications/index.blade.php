@extends('layouts.app')

@section('body')
<div class="min-h-screen bg-[#F5F7FA]">
    <x-navbar>
        <x-breadcrumb :items="collect([
            ['label' => 'داشبورد', 'url' => route('dashboard')],
            ['label' => 'اعلان‌ها'],
        ])" />
    </x-navbar>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-7">
        @if (session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl px-4 py-3">{{ session('success') }}</div>
        @endif

        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl font-black text-[#172B4D]">دعوت‌ها و اعلان‌ها</h2>
                <p class="text-xs text-[#64748B] mt-1">دعوت‌های فضای کاری و تاریخچه اعلان‌های حساب شما</p>
            </div>
            @if (($navbarUnreadCount ?? 0) > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="text-[11px] font-bold text-[#0069FF]">خواندن همه</button>
                </form>
            @endif
        </div>

        <section class="mb-7">
            <h3 class="text-sm font-bold text-[#172B4D] mb-3">دعوت‌های فضای کاری</h3>
            <div class="space-y-3">
                @forelse ($invitations as $invitation)
                    <div class="bg-white border border-[#DFE5EF] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1">
                            <p class="text-[13px] font-bold text-[#172B4D]">{{ $invitation->workspace->name }}</p>
                            <p class="text-[11px] text-[#64748B] mt-1">دعوت از طرف {{ $invitation->inviter->full_name }}</p>
                        </div>
                        @if ($invitation->isPending())
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('invitations.accept', $invitation) }}">
                                    @csrf
                                    <button class="text-[11px] font-bold text-white bg-[#0069FF] rounded-lg px-4 py-2">پذیرش</button>
                                </form>
                                <form method="POST" action="{{ route('invitations.decline', $invitation) }}">
                                    @csrf
                                    <button class="text-[11px] font-bold text-[#64748B] border border-[#DCE3ED] rounded-lg px-4 py-2">رد</button>
                                </form>
                            </div>
                        @else
                            <span class="text-[10px] font-bold text-[#64748B] bg-[#F1F5F9] rounded-md px-2.5 py-1">{{ $invitation->status }}</span>
                        @endif
                    </div>
                @empty
                    <div class="bg-white border border-[#DFE5EF] rounded-xl py-10 text-center text-xs text-[#94A3B8]">دعوتی ندارید</div>
                @endforelse
            </div>
            <div class="mt-4">{{ $invitations->links() }}</div>
        </section>

        <section>
            <h3 class="text-sm font-bold text-[#172B4D] mb-3">تاریخچه اعلان‌ها</h3>
            <div class="bg-white border border-[#DFE5EF] rounded-xl overflow-hidden">
                @forelse ($notifications as $notification)
                    <a href="{{ route('notifications.open', $notification) }}" class="block px-4 py-4 border-b border-[#F1F5F9] last:border-0 hover:bg-[#F8FAFC] {{ $notification->read_at ? '' : 'bg-[#F5F9FF]' }}">
                        <p class="text-[12px] text-[#334155]">{{ $notification->data['message'] ?? 'اعلان جدید' }}</p>
                        <p class="text-[9px] text-[#94A3B8] mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <div class="py-10 text-center text-xs text-[#94A3B8]">اعلانی وجود ندارد</div>
                @endforelse
            </div>
            <div class="mt-4">{{ $notifications->links() }}</div>
        </section>
    </main>
</div>
@endsection
