@extends('layouts.app')
@section('body')
<div class="flex items-center justify-center min-h-screen bg-[#EEF2F7] p-4">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#0069FF]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#003B8E]/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-[460px]">
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="inline-flex mb-5" aria-label="صفحه اصلی نئووا">
                <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا" class="h-12 sm:h-14 w-auto object-contain">
            </a>
            <h1 class="text-xl font-black text-[#1A1D21]">تکمیل اطلاعات</h1>
            <p class="text-sm text-[#64748B] mt-1.5">برای ادامه، اطلاعات شخصی خود را وارد کنید</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl shadow-black/5 border border-[#E2E8F0] overflow-hidden">
            <form action="{{ route('auth.profile.store') }}" method="POST" class="p-6 space-y-5">
                @csrf

                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-xs font-semibold text-green-700">{{ session('success') }}</span>
                    </div>
                @endif

                <div>
                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-2 uppercase tracking-widest">نام</label>
                    <input
                        name="first_name"
                        type="text"
                        required
                        value="{{ old('first_name') }}"
                        class="w-full text-sm font-bold text-[#1A1D21] border-2 border-[#E2E8F0] rounded-xl px-4 py-3 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all placeholder:text-[#CBD5E1]"
                        placeholder="نام خود را وارد کنید"
                    >
                    @error('first_name')
                        <p class="text-[11px] text-red-500 font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-2 uppercase tracking-widest">نام خانوادگی</label>
                    <input
                        name="last_name"
                        type="text"
                        required
                        value="{{ old('last_name') }}"
                        class="w-full text-sm font-bold text-[#1A1D21] border-2 border-[#E2E8F0] rounded-xl px-4 py-3 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all placeholder:text-[#CBD5E1]"
                        placeholder="نام خانوادگی خود را وارد کنید"
                    >
                    @error('last_name')
                        <p class="text-[11px] text-red-500 font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-2 uppercase tracking-widest">کد ملی <span class="text-[#CBD5E1]">(اختیاری)</span></label>
                    <input
                        name="national_code"
                        type="text"
                        maxlength="10"
                        value="{{ old('national_code') }}"
                        class="w-full text-sm font-bold text-[#1A1D21] border-2 border-[#E2E8F0] rounded-xl px-4 py-3 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all placeholder:text-[#CBD5E1]"
                        placeholder="۱۲۳۴۵۶۷۸۹۰"
                        dir="ltr"
                    >
                    @error('national_code')
                        <p class="text-[11px] text-red-500 font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-2 uppercase tracking-widest">ایمیل <span class="text-[#CBD5E1]">(اختیاری)</span></label>
                    <input
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        class="w-full text-sm font-bold text-[#1A1D21] border-2 border-[#E2E8F0] rounded-xl px-4 py-3 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all placeholder:text-[#CBD5E1]"
                        placeholder="email@example.com"
                        dir="ltr"
                    >
                    <p class="text-[10px] text-[#94A3B8] mt-1.5">ایمیل برای ارسال اعلان‌ها استفاده می‌شود.</p>
                    @error('email')
                        <p class="text-[11px] text-red-500 font-semibold mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full text-sm font-bold text-white bg-gradient-to-l from-[#003B8E] to-[#0069FF] hover:from-[#004BAA] hover:to-[#4D99FF] px-5 py-3.5 rounded-xl shadow-md shadow-[#0069FF]/25 hover:shadow-lg hover:shadow-[#0069FF]/30 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                >
                    <span>تکمیل و ورود</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
