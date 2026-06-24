@extends('layouts.app')

@section('body')
<div class="min-h-screen bg-[#F5F7FA]">
    <x-navbar>
        <x-breadcrumb :items="collect([
            ['label' => 'داشبورد', 'url' => route('dashboard')],
            ['label' => 'پروفایل'],
        ])" />
    </x-navbar>

    <main class="max-w-2xl mx-auto px-4 sm:px-6 py-7">
        @if (session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl px-4 py-3">
                @if (session('success') === 'profile_updated')
                    پروفایل با موفقیت به‌روزرسانی شد.
                @elseif (session('success') === 'avatar_updated')
                    تصویر پروفایل با موفقیت تغییر کرد.
                @elseif (session('success') === 'avatar_removed')
                    تصویر پروفایل حذف شد.
                @elseif (session('success') === 'notifications_updated')
                    تنظیمات اعلان‌ها ذخیره شد.
                @else
                    {{ session('success') }}
                @endif
            </div>
        @endif

        <div class="mb-6">
            <h2 class="text-xl font-black text-[#172B4D]">پروفایل</h2>
            <p class="text-xs text-[#64748B] mt-1">اطلاعات شخصی و تنظیمات اعلان‌های خود را مدیریت کنید.</p>
        </div>

        {{-- Avatar Section --}}
        <div class="bg-white border border-[#DFE5EF] rounded-xl p-5 mb-5">
            <h3 class="text-sm font-bold text-[#172B4D] mb-4">تصویر پروفایل</h3>
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-[#1668FF] flex items-center justify-center overflow-hidden shrink-0 ring-4 ring-[#E8F0FE]">
                    @if ($user->avatar)
                        <img src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->full_name }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-2xl text-white font-black">{{ $user->initials }}</span>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-[13px] font-bold text-[#172B4D]">{{ $user->full_name }}</p>
                    <p class="text-[10px] text-[#94A3B8] mt-1">{{ $user->phone }}</p>
                    <div class="flex items-center gap-2 mt-3" x-data="{ uploading: false }">
                        <label class="cursor-pointer text-[11px] font-bold text-[#0069FF] bg-[#E8F0FE] hover:bg-[#D6E4FD] px-3 py-1.5 rounded-lg transition-colors">
                            <span x-show="!uploading">تغییر تصویر</span>
                            <span x-show="uploading" x-cloak>در حال آپلود...</span>
                            <input type="file" accept="image/*" class="hidden" x-on:change="
                                uploading = true;
                                const form = new FormData();
                                form.append('avatar', $event.target.files[0]);
                                form.append('_token', '{{ csrf_token() }}');
                                form.append('_method', 'POST');
                                fetch('{{ route('profile.avatar') }}', {
                                    method: 'POST',
                                    body: form,
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                }).then(r => { if (r.ok) location.reload(); else { uploading = false; alert('خطا در آپلود تصویر'); } });
                            ">
                        </label>
                        @if ($user->avatar)
                            <form method="POST" action="{{ route('profile.avatar.destroy') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] font-bold text-red-500 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition-colors">
                                    حذف تصویر
                                </button>
                            </form>
                        @endif
                    </div>
                    <p class="text-[9px] text-[#94A3B8] mt-2">فرمت‌های پشتیبانی‌شده: JPG, PNG, WebP — حداکثر ۲ مگابایت</p>
                </div>
            </div>
        </div>

        {{-- Personal Information --}}
        <div class="bg-white border border-[#DFE5EF] rounded-xl p-5 mb-5">
            <h3 class="text-sm font-bold text-[#172B4D] mb-4">اطلاعات شخصی</h3>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">نام</label>
                        <input name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" required
                            class="w-full text-sm border border-[#DCE3ED] rounded-lg px-3 py-2.5 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all">
                        @error('first_name')
                            <p class="text-[10px] text-red-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">نام خانوادگی</label>
                        <input name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" required
                            class="w-full text-sm border border-[#DCE3ED] rounded-lg px-3 py-2.5 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all">
                        @error('last_name')
                            <p class="text-[10px] text-red-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">شماره تلفن</label>
                    <input type="text" value="{{ $user->phone }}" disabled
                        class="w-full text-sm border border-[#E2E8F0] rounded-lg px-3 py-2.5 bg-[#F8FAFC] text-[#94A3B8] cursor-not-allowed" dir="ltr">
                    <p class="text-[9px] text-[#94A3B8] mt-1">شماره تلفن قابل تغییر نیست.</p>
                </div>

                <div class="mb-5">
                    <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">ایمیل <span class="text-[#CBD5E1]">(اختیاری)</span></label>
                    <input name="email" type="email" value="{{ old('email', $user->email) }}"
                        class="w-full text-sm border border-[#DCE3ED] rounded-lg px-3 py-2.5 focus:outline-none focus:border-[#0069FF] focus:ring-2 focus:ring-[#0069FF]/10 transition-all" dir="ltr"
                        placeholder="email@example.com">
                    <p class="text-[9px] text-[#94A3B8] mt-1">ایمیل برای ارسال اعلان‌ها استفاده می‌شود.</p>
                    @error('email')
                        <p class="text-[10px] text-red-500 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0057D9] rounded-lg px-5 py-2.5 transition-all active:scale-[0.98]">
                    ذخیره تغییرات
                </button>
            </form>
        </div>

        {{-- Notification Preferences --}}
        <div class="bg-white border border-[#DFE5EF] rounded-xl p-5">
            <h3 class="text-sm font-bold text-[#172B4D] mb-1">تنظیمات اعلان‌ها</h3>
            <p class="text-[10px] text-[#94A3B8] mb-4">اعلان‌های ایمیلی مورد نظر خود را انتخاب کنید.</p>

            <form method="POST" action="{{ route('profile.notifications') }}">
                @csrf
                @method('PATCH')

                <div class="space-y-3">
                    <label class="flex items-center justify-between p-3 rounded-lg border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#E8F0FE] text-[#0069FF] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold text-[#172B4D]">فعالیت وظایف</p>
                                <p class="text-[10px] text-[#94A3B8]">تخصیص، نظرات و اشارات</p>
                            </div>
                        </div>
                        <input type="hidden" name="task_activity" value="0">
                        <input type="checkbox" name="task_activity" value="1" {{ $user->hasNotificationPreference('task_activity') ? 'checked' : '' }}
                            class="w-4 h-4 text-[#0069FF] border-[#DCE3ED] rounded focus:ring-[#0069FF]">
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-lg border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold text-[#172B4D]">دعوت‌نامه‌ها</p>
                                <p class="text-[10px] text-[#94A3B8]">دعوت به فضاهای کاری</p>
                            </div>
                        </div>
                        <input type="hidden" name="invitations" value="0">
                        <input type="checkbox" name="invitations" value="1" {{ $user->hasNotificationPreference('invitations') ? 'checked' : '' }}
                            class="w-4 h-4 text-[#0069FF] border-[#DCE3ED] rounded focus:ring-[#0069FF]">
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-lg border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#DCFCE7] text-[#16A34A] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold text-[#172B4D]">تغییرات پروژه</p>
                                <p class="text-[10px] text-[#94A3B8]">به‌روزرسانی‌های پروژه و تیم</p>
                            </div>
                        </div>
                        <input type="hidden" name="project_updates" value="0">
                        <input type="checkbox" name="project_updates" value="1" {{ $user->hasNotificationPreference('project_updates') ? 'checked' : '' }}
                            class="w-4 h-4 text-[#0069FF] border-[#DCE3ED] rounded focus:ring-[#0069FF]">
                    </label>

                    <label class="flex items-center justify-between p-3 rounded-lg border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors cursor-pointer">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#F1F5F9] text-[#64748B] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-[12px] font-bold text-[#172B4D]">خلاصه فعالیت</p>
                                <p class="text-[10px] text-[#94A3B8]">گزارش روزانه یا هفتگی</p>
                            </div>
                        </div>
                        <input type="hidden" name="digest" value="0">
                        <input type="checkbox" name="digest" value="1" {{ $user->hasNotificationPreference('digest') ? 'checked' : '' }}
                            class="w-4 h-4 text-[#0069FF] border-[#DCE3ED] rounded focus:ring-[#0069FF]">
                    </label>
                </div>

                <button type="submit" class="mt-4 text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0057D9] rounded-lg px-5 py-2.5 transition-all active:scale-[0.98]">
                    ذخیره تنظیمات
                </button>
            </form>
        </div>
    </main>
</div>
@endsection
