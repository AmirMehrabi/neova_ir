@extends('layouts.app')

@section('body')
<main class="min-h-screen bg-[#FBFDFF] grid place-items-center px-5">
    <section class="w-full max-w-md text-center">
        <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا" class="h-9 w-auto mx-auto mb-12">
        <p class="text-[11px] font-black text-[#0069D9] mb-3">شروع کار با نئووا</p>
        <h1 class="text-3xl font-black text-[#102A43] leading-relaxed">فضای کاری تیم‌تان را بسازید</h1>
        <p class="mt-3 text-sm leading-7 text-[#64788A]">پروژه‌ها، کارهای امروز و اعضای تیم در این فضا کنار هم قرار می‌گیرند.</p>
        @if ($errors->any())<p class="mt-6 text-xs text-red-600">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('dashboard.workspace.store') }}" class="mt-8 text-right">
            @csrf
            <label class="block text-[11px] font-bold text-[#64788A] mb-2" for="workspace-name">نام فضای کاری</label>
            <input id="workspace-name" name="name" value="{{ old('name') }}" required autofocus maxlength="100" class="w-full h-12 rounded-lg border border-[#DCE8F2] bg-white px-4 text-sm text-[#102A43] outline-none focus:border-[#0069D9] focus:ring-4 focus:ring-[#0069D9]/10" placeholder="مثلاً تیم محصول">
            <button class="mt-4 w-full h-12 rounded-lg bg-[#0069D9] text-sm font-black text-white hover:bg-[#0052B3]">ساخت فضای کاری و ورود</button>
        </form>
        <form method="POST" action="{{ route('auth.logout') }}" class="mt-7">@csrf<button class="text-[11px] font-bold text-[#94A3B8] hover:text-[#102A43]">خروج از حساب</button></form>
    </section>
</main>
@endsection
