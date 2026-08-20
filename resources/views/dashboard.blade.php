@extends('layouts.app')

@section('body')
<main class="min-h-screen bg-[#F7F7F5] grid place-items-center px-5">
    <section class="w-full max-w-md text-center">
        <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا" class="h-9 w-auto mx-auto mb-12">
        <p class="text-[11px] font-black text-[#8A8A84] mb-3">شروع کار با نئووا</p>
        <h1 class="text-3xl font-black text-[#111111] leading-relaxed">فضای کاری تیم‌تان را بسازید</h1>
        <p class="mt-3 text-sm leading-7 text-[#555552]">پروژه‌ها، کارهای امروز و اعضای تیم در این فضا کنار هم قرار می‌گیرند.</p>
        @if ($errors->any())<p class="mt-6 text-xs text-red-600">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('dashboard.workspace.store') }}" class="mt-8 text-right">
            @csrf
            <label class="block text-[11px] font-bold text-[#555552] mb-2" for="workspace-name">نام فضای کاری</label>
            <input id="workspace-name" name="name" value="{{ old('name') }}" required autofocus maxlength="100" class="w-full h-12 rounded-lg border border-[#D8D8D3] bg-white px-4 text-sm text-[#111111] outline-none focus:border-[#111111] focus:ring-4 focus:ring-black/10" placeholder="مثلاً تیم محصول">
            <button class="mt-4 w-full h-12 rounded-lg bg-[#111111] text-sm font-black text-white hover:bg-black">ساخت فضای کاری و ورود</button>
        </form>
        <form method="POST" action="{{ route('auth.logout') }}" class="mt-7">@csrf<button class="text-[11px] font-bold text-[#8A8A84] hover:text-[#111111]">خروج از حساب</button></form>
    </section>
</main>
@endsection
