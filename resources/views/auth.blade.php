@extends('layouts.app')
@section('body')
<div x-data="auth()" x-cloak class="auth-page">
    <div class="auth-shell">
        <a href="{{ url('/') }}" class="auth-brand" aria-label="صفحه اصلی نئووا">
            <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا">
        </a>

        <div class="auth-heading">
            <p class="auth-eyebrow">ورود ساده، کار روشن</p>
            <h1 x-text="step === 'phone' ? 'به فضای کارتان برگردید.' : 'شماره‌تان را تأیید کنید.'"></h1>
            <p x-text="step === 'phone' ? 'شماره تلفن خود را وارد کنید تا کد ورود را برایتان بفرستیم.' : 'کد شش‌رقمی ارسال‌شده را وارد کنید.'"></p>
        </div>

        <div class="auth-card">
            <div x-show="step === 'phone'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <form @submit.prevent="sendOtp()" class="auth-form">
                    <div class="auth-field">
                        <label for="phone">شماره تلفن</label>
                        <input id="phone" x-model="phone" type="tel" maxlength="11" inputmode="numeric" autocomplete="tel" class="auth-input auth-input--phone" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" @input="phone = phone.replace(/[^0-9]/g, '').substring(0, 11)">
                        <p x-show="errors.phone" class="auth-error" x-text="errors.phone"></p>
                    </div>

                    <div x-show="globalError" x-transition class="auth-alert auth-alert--error" role="alert" x-text="globalError"></div>

                    <button type="submit" :disabled="loading || phone.length !== 11" class="auth-submit">
                        <span x-text="loading ? 'در حال ارسال...' : 'ارسال کد تایید'"></span>
                    </button>
                </form>
            </div>

            <div x-show="step === 'otp'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <form @submit.prevent="verifyOtp()" class="auth-form">
                    <div class="auth-phone-row">
                        <span dir="ltr" x-text="phone"></span>
                        <button type="button" @click="backToPhone()">ویرایش</button>
                    </div>

                    <div class="auth-field">
                        <div class="auth-label-row">
                            <label id="otp-label">کد تایید</label>
                            <span>۶ رقم</span>
                        </div>
                        <div class="auth-otp" role="group" aria-labelledby="otp-label" @keydown="handleOtpKeydown($event)">
                            <template x-for="(digit, index) in digits" :key="index">
                                <input
                                    :data-otp-index="index"
                                    :value="digit"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    maxlength="1"
                                    pattern="[0-9]*"
                                    class="auth-otp-input"
                                    dir="ltr"
                                    :aria-label="'رقم ' + (index + 1) + ' از ۶'"
                                    @input="handleOtpInput(index, $event)"
                                    @paste.prevent="handleOtpPaste(index, $event)"
                                >
                            </template>
                        </div>
                        <p x-show="errors.code" class="auth-error" x-text="errors.code"></p>
                    </div>

                    <div class="auth-resend">
                        <template x-if="resendTimer > 0"><span x-text="'ارسال مجدد کد تا ' + resendTimer + ' ثانیه'"></span></template>
                        <template x-if="resendTimer <= 0"><button type="button" @click="sendOtp()" x-text="loading ? 'در حال ارسال...' : 'ارسال مجدد کد'"></button></template>
                    </div>

                    <div x-show="globalError" x-transition class="auth-alert auth-alert--error" role="alert" x-text="globalError"></div>

                    <button type="submit" :disabled="loading || code.length !== 6" class="auth-submit">
                        <span x-text="loading ? 'در حال بررسی...' : 'تایید و ورود'"></span>
                    </button>
                </form>
            </div>
        </div>

        <p class="auth-legal">با ورود، شما <a href="#">شرایط استفاده</a> و <a href="#">حریم خصوصی</a> را می‌پذیرید.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function auth() {
        return {
            step: 'phone',
            phone: '',
            digits: ['', '', '', '', '', ''],
            loading: false,
            globalError: '',
            errors: {},
            resendTimer: 0,

            get code() {
                return this.digits.join('');
            },

            focusOtp(index) {
                this.$nextTick(() => document.querySelector(`[data-otp-index="${index}"]`)?.focus());
            },

            handleOtpInput(index, event) {
                const rawValue = event.target.value.replace(/[^0-9]/g, '');
                if (rawValue.length > 1) {
                    const autofilled = rawValue.slice(0, this.digits.length - index);
                    [...autofilled].forEach((digit, offset) => { this.digits[index + offset] = digit; });
                    event.target.value = this.digits[index];
                    this.errors.code = '';
                    this.focusOtp(Math.min(index + autofilled.length, this.digits.length - 1));
                    return;
                }
                const value = rawValue;
                this.digits[index] = value;
                event.target.value = value;
                this.errors.code = '';
                if (value && index < this.digits.length - 1) this.focusOtp(index + 1);
            },

            handleOtpPaste(index, event) {
                const pasted = event.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, this.digits.length - index);
                if (!pasted) return;
                [...pasted].forEach((digit, offset) => { this.digits[index + offset] = digit; });
                this.errors.code = '';
                this.focusOtp(Math.min(index + pasted.length, this.digits.length - 1));
            },

            handleOtpKeydown(event) {
                if (event.key !== 'Backspace') return;
                const index = Number(event.target.dataset.otpIndex);
                if (Number.isNaN(index)) return;
                event.preventDefault();
                if (this.digits[index]) {
                    this.digits[index] = '';
                    this.focusOtp(Math.max(index - 1, 0));
                } else if (index > 0) {
                    this.digits[index - 1] = '';
                    this.focusOtp(index - 1);
                }
            },

            backToPhone() {
                this.step = 'phone';
                this.digits = ['', '', '', '', '', ''];
                this.globalError = '';
                this.errors = {};
            },

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
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ phone: this.phone }),
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.step = 'otp';
                        this.digits = ['', '', '', '', '', ''];
                        this.startResendTimer();
                        this.focusOtp(0);
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
                    this.focusOtp(this.digits.findIndex(digit => !digit));
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('{{ route("auth.verify-otp") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ phone: this.phone, code: this.code }),
                    });
                    const data = await res.json();
                    if (res.ok && data.redirect) window.location.href = data.redirect;
                    else this.globalError = data.message || 'کد تایید نادرست است';
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
