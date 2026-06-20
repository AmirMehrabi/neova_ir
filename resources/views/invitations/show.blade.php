@extends('layouts.app')

@section('body')
<div class="min-h-screen bg-[#F5F7FA]">
    <x-app-page-header title="دعوت به فضای کاری" :back-url="route('dashboard')" />

    <main class="max-w-xl mx-auto px-4 py-12">
        <div class="bg-white border border-[#DFE5EF] rounded-2xl p-6 sm:p-8 text-center">
            <img src="{{ asset('assets/logo/logo-white.png') }}" alt="نئووا" class="w-14 h-14 object-contain rounded-2xl bg-[#E8F0FE] p-2 mx-auto mb-5">
            <h2 class="text-xl font-black text-[#172B4D]">دعوت به {{ $invitation->workspace->name }}</h2>
            <p class="text-sm text-[#64748B] leading-7 mt-3">{{ $invitation->inviter->full_name }} شما را برای همکاری در این فضای کاری دعوت کرده است.</p>

            @if ($invitation->phone !== auth()->user()->phone)
                <div class="mt-6 bg-amber-50 border border-amber-200 text-amber-700 text-xs leading-6 rounded-xl px-4 py-3">
                    این دعوت برای شماره دیگری صادر شده است. با شماره دعوت‌شده وارد شوید.
                </div>
            @elseif (! $invitation->isPending())
                <div class="mt-6 bg-[#F1F5F9] text-[#64748B] text-xs rounded-xl px-4 py-3">این دعوت‌نامه دیگر فعال نیست.</div>
            @else
                <div class="flex gap-3 mt-7">
                    <form method="POST" action="{{ route('invitations.accept', $invitation) }}" class="flex-1">
                        @csrf
                        <button class="w-full text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0057D9] rounded-xl px-4 py-3">پذیرش دعوت</button>
                    </form>
                    <form method="POST" action="{{ route('invitations.decline', $invitation) }}" class="flex-1">
                        @csrf
                        <button class="w-full text-xs font-bold text-[#64748B] border border-[#DCE3ED] rounded-xl px-4 py-3">رد دعوت</button>
                    </form>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
