@extends('layouts.app')
@section('body')
<div x-data="auth()" x-cloak class="flex items-center justify-center min-h-screen p-4">
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-[#0069FF]/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-[#003B8E]/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-[420px]">
        <div class="text-center mb-8">
            <img src="{{ asset('assets/logo/logo-black.png') }}" alt="نئووا" class="w-22 h-22 object-cover rounded-2xl  p-1 mx-auto mb-4 ">
            <h1 class="text-xl font-black text-[#1A1D21]" x-text="step === 'phone' ? 'ورود به تخته اسکرام' : 'تایید شماره تلفن'"></h1>
            <p class="text-sm text-[#64748B] mt-1.5" x-text="step === 'phone' ? 'شماره تلفن خود را وارد کنید' : 'کد ۶ رقمی ارسال شده را وارد کنید'"></p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl shadow-black/5 border border-[#E2E8F0] overflow-hidden">

            {{-- Phone Step --}}
            <div x-show="step === 'phone'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <form @submit.prevent="sendOtp()" class="p-6 space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold text-[#94A3B8] mb-2 uppercase tracking-widest">شماره تلفن</label>
                        <div class="relative">
                            <input
                                x-model="phone"
                                type="tel"
                                maxlength="11"
                                class="w-full text-lg font-bold text-[#1A1D21] border-2 border-[#E2E8F0] rounded-xl px-4 py-3.5 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all placeholder:text-[#CBD5E1] tracking-wider"
                                placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                                dir="ltr"
                                @input="phone = phone.replace(/[^0-9]/g, '').substring(0, 11)"
                            >
                        </div>
                        <p x-show="errors.phone" class="text-[11px] text-red-500 font-semibold mt-1.5" x-text="errors.phone"></p>
                    </div>

                    <div x-show="globalError" x-transition class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs font-semibold text-red-700" x-text="globalError"></span>
                    </div>

                    <button
                        type="submit"
                        :disabled="loading || phone.length !== 11"
                        class="w-full text-sm font-bold text-white bg-gradient-to-l from-[#003B8E] to-[#0069FF] hover:from-[#004BAA] hover:to-[#4D99FF] disabled:opacity-50 disabled:cursor-not-allowed px-5 py-3.5 rounded-xl shadow-md shadow-[#0069FF]/25 hover:shadow-lg hover:shadow-[#0069FF]/30 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                    >
                        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="loading ? 'در حال ارسال...' : 'ارسال کد تایید'"></span>
                    </button>
                </form>
            </div>

            {{-- OTP Step --}}
            <div x-show="step === 'otp'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <form @submit.prevent="verifyOtp()" class="p-6 space-y-5">
                    <div class="flex items-center justify-between bg-[#F8FAFC] rounded-xl px-4 py-3 border border-[#F1F5F9]">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-sm font-bold text-[#475569]" x-text="phone"></span>
                        </div>
                        <button type="button" @click="step = 'phone'; code = ''; globalError = ''" class="text-[11px] font-bold text-[#0069FF] hover:text-[#003B8E] transition-colors">ویرایش</button>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-[#94A3B8] mb-2 uppercase tracking-widest">کد تایید</label>
                        <input
                            x-model="code"
                            type="text"
                            maxlength="6"
                            class="w-full text-center text-[1.5rem] font-bold tracking-[0.5em] text-[#1A1D21] border-2 border-[#E2E8F0] rounded-xl px-4 py-3.5 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all placeholder:text-[#CBD5E1]"
                            placeholder="۱۲۳۴۵۶"
                            dir="ltr"
                            @input="code = code.replace(/[^0-9]/g, '').substring(0, 6)"
                            x-ref="otpInput"
                        >
                        <p x-show="errors.code" class="text-[11px] text-red-500 font-semibold mt-1.5" x-text="errors.code"></p>
                    </div>

                    <div class="text-center">
                        <template x-if="resendTimer > 0">
                            <p class="text-xs text-[#94A3B8]" x-text="'ارسال مجدد کد تا ' + resendTimer + ' ثانیه'"></p>
                        </template>
                        <template x-if="resendTimer <= 0">
                            <button type="button" @click="sendOtp()" class="text-xs font-bold text-[#0069FF] hover:text-[#003B8E] transition-colors" x-text="loading ? 'در حال ارسال...' : 'ارسال مجدد کد'"></button>
                        </template>
                    </div>

                    <div x-show="globalError" x-transition class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs font-semibold text-red-700" x-text="globalError"></span>
                    </div>

                    <button
                        type="submit"
                        :disabled="loading || code.length !== 6"
                        class="w-full text-sm font-bold text-white bg-gradient-to-l from-[#003B8E] to-[#0069FF] hover:from-[#004BAA] hover:to-[#4D99FF] disabled:opacity-50 disabled:cursor-not-allowed px-5 py-3.5 rounded-xl shadow-md shadow-[#0069FF]/25 hover:shadow-lg hover:shadow-[#0069FF]/30 transition-all active:scale-[0.98] flex items-center justify-center gap-2"
                    >
                        <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="loading ? 'در حال بررسی...' : 'تایید و ورود'"></span>
                    </button>
                </form>
            </div>
        </div>

        <p class="text-center text-[10px] text-[#94A3B8] mt-6">
            با ورود، شما <a href="#" class="text-[#0069FF] hover:underline">شرایط استفاده</a> و <a href="#" class="text-[#0069FF] hover:underline">حریم خصوصی</a> را می‌پذیرید.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function auth() {
        return {
            step: 'phone',
            phone: '',
            code: '',
            loading: false,
            globalError: '',
            errors: {},
            resendTimer: 0,

            async sendOtp() {
                this.errors = {};
                this.globalError = '';
                if (this.phone.length !== 11) {
                    this.errors.phone = 'شماره تلفن باید ۱۱ رقم باشد';
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('{{ route("auth.send-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone: this.phone }),
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.step = 'otp';
                        this.startResendTimer();
                        this.$nextTick(() => this.$refs.otpInput?.focus());
                    } else {
                        this.errors.phone = data.message || 'شماره تلفن نادرست است';
                    }
                } catch (e) {
                    this.globalError = 'خطا در ارتباط با سرور';
                } finally {
                    this.loading = false;
                }
            },

            async verifyOtp() {
                this.errors = {};
                this.globalError = '';
                if (this.code.length !== 6) {
                    this.errors.code = 'کد تایید باید ۶ رقم باشد';
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('{{ route("auth.verify-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone: this.phone, code: this.code }),
                    });
                    const data = await res.json();
                    if (res.ok && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        this.globalError = data.message || 'کد تایید نادرست است';
                    }
                } catch (e) {
                    this.globalError = 'خطا در ارتباط با سرور';
                } finally {
                    this.loading = false;
                }
            },

            startResendTimer() {
                this.resendTimer = 120;
                const interval = setInterval(() => {
                    this.resendTimer--;
                    if (this.resendTimer <= 0) clearInterval(interval);
                }, 1000);
            },
        };
    }
</script>
@endpush
