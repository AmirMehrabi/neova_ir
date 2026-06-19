@extends('layouts.app')
@section('body')
<div class="min-h-screen bg-[#F0F4F8]" x-data="{ showModal: false }">
    <style>
        body.modal-open { overflow: hidden !important; position: fixed; width: 100%; }
    </style>

    {{-- Header --}}
    <header class="bg-[#003B8E] shadow-lg shadow-[#003B8E]/20 sticky top-0 z-40">
        <div class="max-w-[1400px] mx-auto px-5 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#0069FF] flex items-center justify-center shadow-md shadow-[#0069FF]/30">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                </div>
                <span class="text-white font-bold text-[15px]">تخته اسکرام</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center">
                        <span class="text-[10px] text-white font-bold">{{ substr(auth()->user()->first_name ?? auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <span class="text-blue-200 text-xs font-medium">{{ auth()->user()->full_name }}</span>
                </div>
                <form action="{{ route('auth.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-blue-200 hover:text-white text-xs transition-colors">خروج</button>
                </form>
            </div>
        </div>
    </header>

    {{-- Main --}}
    <main class="max-w-[1400px] mx-auto px-5 py-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-lg font-black text-[#1A1D21]">فضاهای کاری</h1>
                <p class="text-xs text-[#64748B] mt-0.5">فضاهای کاری خود را مدیریت کنید</p>
            </div>
            <button
                @click="showModal = true"
                class="flex items-center gap-1.5 bg-[#0069FF] hover:bg-[#4D99FF] text-white text-xs font-bold px-3.5 py-2 rounded-lg transition-all shadow-md shadow-[#0069FF]/25 active:scale-[0.97]"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                فضای کاری جدید
            </button>
        </div>

        {{-- Workspaces Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($workspaces as $workspace)
                <a href="{{ route('workspace', $workspace->slug) }}" class="block bg-white rounded-2xl border border-[#E2E8F0] p-5 hover:border-[#0069FF]/30 hover:shadow-lg hover:shadow-[#0069FF]/5 transition-all group">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#003B8E] to-[#0069FF] flex items-center justify-center shadow-md shadow-[#0069FF]/20">
                            <span class="text-white font-bold text-sm">{{ substr($workspace->name, 0, 1) }}</span>
                        </div>
                        <span class="text-[10px] text-[#94A3B8]">{{ $workspace->projects_count ?? $workspace->projects->count() }} پروژه</span>
                    </div>
                    <h3 class="text-sm font-bold text-[#1A1D21] group-hover:text-[#0069FF] transition-colors">{{ $workspace->name }}</h3>
                    @if ($workspace->description)
                        <p class="text-xs text-[#64748B] mt-1 line-clamp-2">{{ $workspace->description }}</p>
                    @endif
                    <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-[#F1F5F9]">
                        <div class="w-4 h-4 rounded-full bg-[#0069FF] flex items-center justify-center">
                            <span class="text-[6px] text-white font-bold">{{ substr($workspace->owner->first_name ?? 'م', 0, 1) }}</span>
                        </div>
                        <span class="text-[10px] text-[#94A3B8]">{{ $workspace->owner->full_name }}</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center py-16 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-[#E8F0FE] flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[#0069FF]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <p class="text-sm font-bold text-[#1A1D21] mb-1">هنوز فضای کاری ندارید</p>
                    <p class="text-xs text-[#94A3B8]">اولین فضای کاری خود را بسازید</p>
                </div>
            @endforelse
        </div>
    </main>

    {{-- Modal --}}
    <div x-show="showModal" x-cloak x-effect="showModal ? document.body.classList.add('modal-open') : document.body.classList.remove('modal-open')">
        {{-- Backdrop --}}
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-[#0A1628]/60 backdrop-blur-sm"
            @click="showModal = false"
        ></div>

        {{-- Scroll container --}}
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="showModal = false"
        >
            <div class="min-h-screen flex items-center justify-center p-4">
                <div
                    class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl shadow-black/25 p-6"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    @click.stop
                >
                    <h3 class="text-sm font-bold text-[#1A1D21] mb-4">فضای کاری جدید</h3>
                    <form action="{{ route('dashboard.workspace.store') }}" method="POST">
                        @csrf
                        <input name="name" type="text" required class="w-full text-sm font-bold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors mb-4" placeholder="نام فضای کاری">
                        <div class="flex gap-2 justify-end">
                            <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                            <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0055CC] px-4 py-2 rounded-xl transition-all">ایجاد</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
