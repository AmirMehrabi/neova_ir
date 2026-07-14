@extends('layouts.app')
@section('body')
<div
    class="neova-dashboard neova-product min-h-screen bg-[#FDFDFC]"
    x-data="{
        showModal: false,
        modalType: 'workspace',
        targetWorkspace: null,
        userDropdown: false,
        searchOpen: false,
        searchQuery: '',
        searchResults: [],
        searchLoading: false,
        recentSearches: [],
        selectedIdx: -1,

        init() {
            try { this.recentSearches = JSON.parse(localStorage.getItem('neova_search') || '[]'); } catch { this.recentSearches = []; }
        },

        saveRecentSearch(item) {
            let s = this.recentSearches.filter(x => x.url !== item.url);
            s.unshift(item);
            this.recentSearches = s.slice(0, 8);
            localStorage.setItem('neova_search', JSON.stringify(this.recentSearches));
        },

        clearRecentSearches() {
            this.recentSearches = [];
            localStorage.removeItem('neova_search');
        },

        async doSearch() {
            if (!this.searchQuery.trim()) { this.searchResults = []; this.init(); return; }
            this.searchLoading = true;
            this.selectedIdx = -1;
            try {
                const res = await fetch('{{ route('dashboard.search', [], false) }}?q=' + encodeURIComponent(this.searchQuery), {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.searchResults = await res.json();
            } catch { this.searchResults = []; } finally { this.searchLoading = false; }
        },

        navigateResults(dir) {
            const items = this.searchQuery ? this.searchResults : this.recentSearches;
            if (!items.length) return;
            this.selectedIdx = Math.max(-1, Math.min(items.length - 1, this.selectedIdx + dir));
        },

        selectResult() {
            const items = this.searchQuery ? this.searchResults : this.recentSearches;
            if (this.selectedIdx >= 0 && items[this.selectedIdx]) window.location.href = items[this.selectedIdx].url;
        }
    }"
>
    <style>
        [x-cloak] { display: none !important; }
        body.modal-open { overflow: hidden !important; position: fixed; width: 100%; }
        .project-card { transition: background-color 0.18s ease, box-shadow 0.18s ease; }
        .project-card:hover { background: #F5F7F6; box-shadow: inset -3px 0 0 #18212B; }
        .neova-dashboard .bg-white { background-color: #FFFFFF !important; }
        .neova-dashboard .text-\[\#071B33\] { color: #18212B !important; }
        .neova-dashboard .text-\[\#18212B\], .neova-dashboard .text-\[\#18212B\] { color: #18212B !important; }
        .neova-dashboard .bg-\[\#18212B\], .neova-dashboard .bg-\[\#18212B\] { background-color: #18212B !important; }
        .neova-dashboard .bg-\[\#F1F3F2\], .neova-dashboard .bg-\[\#F1F3F2\] { background-color: #F1F3F2 !important; }
        .neova-dashboard .border-\[\#D8E0EB\], .neova-dashboard .border-\[\#E6EBF2\] { border-color: #E8EBE9 !important; }
        .neova-dashboard .text-\[\#64748B\] { color: #66717A !important; }
        @media (prefers-reduced-motion: reduce) {
            .project-card { transition: none; }
        }
    </style>

    <x-navbar light>
        @slot('search')
            <div class="relative" @click.away="searchOpen = false; searchQuery = ''">
                <div class="relative">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A8175]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        x-model="searchQuery"
                        @input.debounce.300ms="doSearch()"
                        @focus="searchOpen = true; if (!searchQuery) init()"
                        @keydown.escape="searchOpen = false; searchQuery = ''"
                        @keydown.arrow-down.prevent="navigateResults(1)"
                        @keydown.arrow-up.prevent="navigateResults(-1)"
                        @keydown.enter.prevent="selectResult()"
                        type="text"
                        class="w-full text-[12px] font-medium text-[#18212B] bg-white/70 border border-[#E8EBE9] rounded-xl pr-9 pl-3 py-2.5 focus:outline-none focus:bg-white focus:border-[#9CB8F3] transition-all placeholder:text-[#8B949B]"
                        placeholder="جستجوی پروژه یا وظیفه…"
                    >
                </div>

                <div
                    x-show="searchOpen && (searchResults.length > 0 || recentSearches.length > 0 || searchQuery.length > 0)"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute top-full left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#E2E8F0] overflow-hidden z-50"
                >
                    <template x-if="!searchQuery && recentSearches.length > 0">
                        <div>
                            <div class="flex items-center justify-between px-3 py-2 border-b border-[#F1F5F9]">
                                <span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">اخیراً جستجو شده</span>
                                <button @click="clearRecentSearches()" class="text-[9px] text-[#94A3B8] hover:text-[#1A1D21]">پاک کردن</button>
                            </div>
                            <template x-for="(item, idx) in recentSearches" :key="idx">
                                <a :href="item.url" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors" :class="selectedIdx === idx ? 'bg-[#F1F5F9]' : ''">
                                    <span class="w-5 h-5 rounded-md flex items-center justify-center shrink-0" :class="{
                                        'bg-[#18212B]/10 text-[#18212B]': item.type === 'workspace',
                                        'bg-[#22C55E]/10 text-[#22C55E]': item.type === 'project',
                                        'bg-[#F59E0B]/10 text-[#F59E0B]': item.type === 'task',
                                    }">
                                        <svg x-show="item.type === 'workspace'" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <svg x-show="item.type === 'project'" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <svg x-show="item.type === 'task'" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 0h6"/></svg>
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[11px] font-semibold text-[#1A1D21] truncate" x-text="item.name"></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </template>

                    <template x-if="searchQuery && searchResults.length > 0">
                        <div class="max-h-[300px] overflow-y-auto">
                            <template x-if="searchResults.filter(r => r.type === 'workspace').length > 0">
                                <div>
                                    <div class="px-3 py-1.5 border-b border-[#F1F5F9]"><span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">فضاهای کاری</span></div>
                                    <template x-for="(item, idx) in searchResults.filter(r => r.type === 'workspace')" :key="'ws-'+idx">
                                        <a :href="item.url" @click="saveRecentSearch(item)" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors">
                                            <span class="w-5 h-5 rounded-md bg-[#18212B]/10 flex items-center justify-center shrink-0"><svg class="w-2.5 h-2.5 text-[#18212B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></span>
                                            <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold text-[#1A1D21] truncate" x-text="item.name"></p><p class="text-[9px] text-[#94A3B8]" x-text="item.subtitle"></p></div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                            <template x-if="searchResults.filter(r => r.type === 'project').length > 0">
                                <div>
                                    <div class="px-3 py-1.5 border-b border-[#F1F5F9]"><span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">پروژه‌ها</span></div>
                                    <template x-for="(item, idx) in searchResults.filter(r => r.type === 'project')" :key="'proj-'+idx">
                                        <a :href="item.url" @click="saveRecentSearch(item)" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors">
                                            <span class="w-5 h-5 rounded-md bg-[#22C55E]/10 flex items-center justify-center shrink-0"><svg class="w-2.5 h-2.5 text-[#22C55E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></span>
                                            <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold text-[#1A1D21] truncate" x-text="item.name"></p><p class="text-[9px] text-[#94A3B8]" x-text="item.subtitle"></p></div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                            <template x-if="searchResults.filter(r => r.type === 'task').length > 0">
                                <div>
                                    <div class="px-3 py-1.5 border-b border-[#F1F5F9]"><span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">وظایف</span></div>
                                    <template x-for="(item, idx) in searchResults.filter(r => r.type === 'task')" :key="'task-'+idx">
                                        <a :href="item.url" @click="saveRecentSearch(item)" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors">
                                            <span class="w-5 h-5 rounded-md bg-[#F59E0B]/10 flex items-center justify-center shrink-0"><svg class="w-2.5 h-2.5 text-[#F59E0B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 0h6"/></svg></span>
                                            <div class="flex-1 min-w-0"><p class="text-[11px] font-semibold text-[#1A1D21] truncate" x-text="item.name"></p><p class="text-[9px] text-[#94A3B8]" x-text="item.subtitle"></p></div>
                                        </a>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="searchQuery && searchResults.length === 0 && !searchLoading">
                        <div class="px-3 py-4 text-center"><p class="text-[11px] text-[#94A3B8]">نتیجه‌ای یافت نشد</p></div>
                    </template>

                    <template x-if="searchLoading">
                        <div class="px-3 py-4 text-center">
                            <div class="inline-flex items-center gap-2 text-[11px] text-[#94A3B8]">
                                <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                در حال جستجو...
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        @endslot
    </x-navbar>

    {{-- Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        @if (session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl px-4 py-3">{{ session('success') }}</div>
        @endif

        <div class="flex items-center justify-between gap-5 mb-8 sm:mb-10">
            <div>
                <h1 class="text-2xl sm:text-[32px] leading-tight font-black tracking-[-0.035em] text-[#071B33]">داشبورد</h1>
                <p class="text-[12px] sm:text-[13px] font-medium text-[#64748B] mt-2">{{ $workspaces->sum('projects_count') }} پروژه در {{ $workspaces->count() }} فضای کاری</p>
            </div>
            <button
                @click="modalType = 'workspace'; showModal = true"
                class="flex items-center gap-2 border border-[#B8C4D4] bg-white hover:border-[#071B33] hover:text-[#071B33] text-[#42526A] text-[12px] font-bold px-4 py-2.5 rounded-[10px] shadow-sm transition-all active:scale-[0.98] shrink-0"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">فضای کاری جدید</span>
            </button>
        </div>

        @if ($invitations->count())
            <div class="mb-6 sm:mb-8 space-y-3">
                <h2 class="text-[13px] font-bold text-[#071B33] flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#F59E0B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0m6 0H9"/></svg>
                    دعوت‌نامه‌های در انتظار
                </h2>
                @foreach ($invitations as $invitation)
                    <div class="bg-white border border-[#FDE68A] rounded-2xl p-4 sm:p-5 shadow-[0_4px_16px_rgba(245,158,11,0.1)]" x-data="{ processing: false }">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl bg-[#FEF3C7] text-[#D97706] flex items-center justify-center text-sm font-black shrink-0">
                                {{ mb_substr($invitation->inviter->full_name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-[#071B33]">
                                    <span class="text-[#D97706]">{{ $invitation->inviter->full_name }}</span>
                                    شما را به فضای کاری
                                    <span class="text-[#D97706]">{{ $invitation->workspace->name }}</span>
                                    دعوت کرده است
                                </p>
                                <div class="flex items-center gap-3 mt-2 text-[10px] text-[#94A3B8]">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        نقش: {{ $invitation->role === 'admin' ? 'مدیر' : 'کاربر' }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $invitation->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-4" x-show="!processing">
                                    <form method="POST" action="{{ route('invitations.accept', $invitation) }}" class="flex-1 sm:flex-none">
                                        @csrf
                                        <button type="submit" @click="processing = true" class="w-full sm:w-auto text-[11px] font-bold text-white bg-[#18212B] hover:bg-[#000000] rounded-xl px-5 py-2.5 transition-all active:scale-[0.98] shadow-[0_4px_12px_rgba(24,33,43,0.20)]">
                                            پذیرش دعوت
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('invitations.decline', $invitation) }}" class="flex-1 sm:flex-none">
                                        @csrf
                                        <button type="submit" @click="processing = true" class="w-full sm:w-auto text-[11px] font-bold text-[#64748B] border border-[#DCE3ED] rounded-xl px-5 py-2.5 hover:bg-[#F8FAFC] transition-colors">
                                            رد دعوت
                                        </button>
                                    </form>
                                </div>
                                <div x-show="processing" x-cloak class="flex items-center gap-2 mt-4 text-[11px] text-[#94A3B8]">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    در حال پردازش...
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-[#D8E0EB] bg-white shadow-[0_12px_30px_rgba(7,27,51,0.06)]">
        @forelse ($workspaces as $workspace)
            @php
                $role = $workspace->getAttribute('user_role');
                $canManage = in_array($role, ['owner', 'admin'], true);
            @endphp
            <section class="border-b border-[#D8E0EB] last:border-b-0">
                <div class="flex items-center justify-between gap-3 px-4 py-4 sm:px-5 sm:py-5 bg-[#F8FAFC] border-b border-[#E6EBF2]">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-[10px] bg-[#F1F3F2] text-[#18212B] flex items-center justify-center text-sm font-black shrink-0">{{ mb_substr($workspace->name, 0, 1) }}</div>
                        <div class="min-w-0">
                            <h2 class="text-[15px] sm:text-[17px] font-black text-[#071B33] truncate">{{ $workspace->name }}</h2>
                            <p class="text-[10px] sm:text-[11px] font-medium text-[#7C899B] mt-0.5">{{ $workspace->projects_count }} پروژه · {{ $workspace->members_count ?? 0 }} عضو</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($canManage)
                            <a href="{{ route('workspaces.settings', $workspace->slug) }}" class="sm:hidden w-9 h-9 inline-flex items-center justify-center text-[#64748B] border border-[#D8E0EB] bg-white rounded-[9px] hover:border-[#AEB9C8] hover:text-[#071B33] transition-colors" aria-label="مدیریت فضای کاری">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0015 19.4a1.7 1.7 0 00-1 .6 1.7 1.7 0 00-.4 1.1V21h-4v-.1A1.7 1.7 0 008.6 19.4a1.7 1.7 0 00-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-.6-1 1.7 1.7 0 00-1.1-.4H3v-4h.1A1.7 1.7 0 004.6 8.6a1.7 1.7 0 00-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 009 4.6a1.7 1.7 0 001-.6 1.7 1.7 0 00.4-1.1V3h4v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0019.4 9c.1.38.31.72.6 1 .3.27.68.41 1.1.4h.1v4h-.1a1.7 1.7 0 00-1.7.6z"/></svg>
                            </a>
                            <a href="{{ route('workspaces.settings', $workspace->slug) }}" class="hidden sm:inline-flex text-[11px] font-bold text-[#64748B] border border-[#D8E0EB] bg-white rounded-[9px] px-3 py-2.5 hover:border-[#AEB9C8] hover:text-[#071B33] transition-colors">مدیریت فضا</a>
                        @endif
                    </div>
                </div>

                @if ($workspace->projects->count())
                    <div class="divide-y divide-[#E6EBF2]">
                        @foreach ($workspace->projects as $project)
                            <a
                                href="{{ route('board', [$workspace->slug, $project->slug]) }}"
                                class="project-card group flex items-center gap-3 sm:gap-5 bg-white px-4 py-4 sm:px-5 sm:py-5 focus:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-[#18212B]/15"
                            >
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-[11px] bg-[#071B33] flex items-center justify-center shrink-0 shadow-[inset_0_0_0_1px_rgba(255,255,255,0.08)]">
                                    <span class="text-white font-black text-[11px] sm:text-[12px] tracking-wide">{{ $project->key ?: mb_substr($project->name, 0, 2) }}</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-[15px] sm:text-[17px] font-black text-[#071B33] truncate">{{ $project->name }}</h3>
                                        @if ($project->visibility === 'private')
                                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-[#D97706] bg-[#FEF3C7] px-1.5 py-0.5 rounded-md shrink-0">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                خصوصی
                                            </span>
                                        @endif
                                    </div>
                                    @if ($project->description)
                                        <p class="hidden sm:block text-[11px] sm:text-[12px] font-medium text-[#64748B] truncate mt-1.5">{{ $project->description }}</p>
                                    @else
                                        <p class="hidden sm:block text-[11px] sm:text-[12px] text-[#8A98AA] mt-1.5">تخته وظایف و روند اجرای پروژه</p>
                                    @endif
                                    <div class="lg:hidden flex items-center gap-2 mt-2 text-[9px] font-bold">
                                        @if ($project->total_tasks > 0)
                                            <span class="text-[#64748B]">{{ $project->done_tasks }} از {{ $project->total_tasks }} وظیفه</span>
                                            <span class="text-[#18212B]">{{ $project->progress_percentage }}٪</span>
                                        @else
                                            <span class="text-[#94A3B8]">بدون وظیفه</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="hidden lg:block w-48 xl:w-56 shrink-0">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        @if ($project->total_tasks > 0)
                                            <span class="text-[10px] font-semibold text-[#64748B]">{{ $project->done_tasks }} از {{ $project->total_tasks }} وظیفه</span>
                                            <span class="text-[10px] font-black text-[#18212B]">{{ $project->progress_percentage }}٪</span>
                                        @else
                                            <span class="text-[10px] font-semibold text-[#94A3B8]">هنوز وظیفه‌ای ثبت نشده</span>
                                        @endif
                                    </div>
                                    <div class="h-2 bg-[#E8EDF4] rounded-full overflow-hidden">
                                        <div class="h-full bg-[#18212B] rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                                    </div>
                                </div>

                                <div class="hidden md:flex items-center shrink-0">
                                    @php $members = $project->members->take(3); @endphp
                                    @if ($members->count())
                                        <div class="flex items-center">
                                            @foreach ($members as $member)
                                                <div class="relative group/tooltip {{ ! $loop->first ? '-mr-2' : '' }}">
                                                    <div class="w-8 h-8 rounded-full bg-[#18212B] flex items-center justify-center text-[10px] text-white font-bold ring-2 ring-white">
                                                        {{ mb_substr($member->name, 0, 1) }}
                                                    </div>
                                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2.5 py-1 bg-[#1A1D21] text-white text-[10px] font-medium rounded-lg whitespace-nowrap opacity-0 group-hover/tooltip:opacity-100 transition-opacity pointer-events-none z-10">
                                                        {{ $member->name }}
                                                        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-[#1A1D21]"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if ($project->members_count > 3)
                                                <div class="w-8 h-8 rounded-full bg-[#F1F3F2] text-[#18212B] flex items-center justify-center text-[10px] font-bold ring-2 ring-white">
                                                    +{{ $project->members_count - 3 }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 shrink-0 text-[#18212B]">
                                    <span class="hidden xl:inline text-[11px] font-black">ورود به تخته</span>
                                    <span class="w-9 h-9 rounded-[9px] bg-[#F1F3F2] group-hover:bg-[#18212B] group-hover:text-white flex items-center justify-center transition-colors">
                                        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                        @if ($canManage)
                            <button
                                @click="targetWorkspace = '{{ $workspace->slug }}'; modalType = 'project'; showModal = true"
                                class="w-full flex items-center justify-center gap-2 border border-dashed border-[#D7D1C5] rounded-none px-4 py-4 sm:px-5 sm:py-5 text-[12px] font-bold text-[#64748B] hover:border-[#18212B] hover:text-[#18212B] hover:bg-[#F8FAFC] transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                پروژه جدید
                            </button>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 px-5 text-center">
                        <p class="text-[13px] text-[#94A3B8] mb-3">هنوز پروژه‌ای ساخته نشده</p>
                        @if ($canManage)
                            <button
                                @click="targetWorkspace = '{{ $workspace->slug }}'; modalType = 'project'; showModal = true"
                                class="text-[11px] font-bold text-[#18212B] bg-[#F1F3F2] hover:bg-[#E8EBE9] px-3 py-1.5 rounded-lg transition-colors"
                            >ایجاد پروژه</button>
                        @endif
                    </div>
                @endif
            </section>
        @empty
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#F1F5F9] flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <p class="text-[15px] font-bold text-[#172B4D] mb-1">فضای کاری ندارید</p>
                <p class="text-xs text-[#64748B] mb-5">اولین فضای کاری خود را بسازید.</p>
                <button @click="modalType = 'workspace'; showModal = true" class="text-xs font-bold text-white bg-[#18212B] hover:bg-[#000000] px-4 py-2.5 rounded-lg transition-colors">ایجاد فضای کاری</button>
            </div>
        @endforelse
        </div>
    </main>

    {{-- Modals --}}
    <div x-show="showModal" x-cloak x-effect="showModal ? document.body.classList.add('modal-open') : document.body.classList.remove('modal-open')">
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 bg-[#0A1628]/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
            <div class="min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white w-full max-w-md rounded-2xl border border-[#D8E0EB] shadow-[0_28px_70px_rgba(7,27,51,0.28)] p-6 sm:p-7" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" @click.stop>
                    <template x-if="modalType === 'workspace'">
                        <div>
                            <h3 class="text-lg font-black text-[#071B33] mb-5">فضای کاری جدید</h3>
                            <form action="{{ route('dashboard.workspace.store') }}" method="POST">
                                @csrf
                                <input name="name" type="text" required class="w-full text-sm font-semibold border-2 border-[#D8E0EB] rounded-xl px-4 py-3 focus:outline-none focus:border-[#18212B] focus:ring-4 focus:ring-[#18212B]/10 transition-colors mb-5 placeholder:text-[#AAB5C4]" placeholder="نام فضای کاری">
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-black text-white bg-[#071B33] hover:bg-[#0B2545] px-4 py-2.5 rounded-xl transition-all">ایجاد فضای کاری</button>
                                </div>
                            </form>
                        </div>
                    </template>
                    <template x-if="modalType === 'project'">
                        <div>
                            <h3 class="text-lg font-black text-[#071B33] mb-5">پروژه جدید</h3>
                            <form :action="'{{ route('dashboard.project.store', ':slug') }}'.replace(':slug', targetWorkspace)" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">نام پروژه</label>
                                    <input name="name" type="text" required class="w-full text-sm font-semibold border-2 border-[#D8E0EB] rounded-xl px-4 py-3 focus:outline-none focus:border-[#18212B] focus:ring-4 focus:ring-[#18212B]/10 transition-colors placeholder:text-[#AAB5C4]" placeholder="نام پروژه">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">کلید پروژه <span class="text-[#CBD5E1]">(اختیاری)</span></label>
                                    <input name="key" type="text" maxlength="10" class="w-full text-sm font-semibold border-2 border-[#D8E0EB] rounded-xl px-4 py-3 focus:outline-none focus:border-[#18212B] focus:ring-4 focus:ring-[#18212B]/10 transition-colors uppercase placeholder:text-[#AAB5C4]" placeholder="مثلاً SCR">
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-black text-white bg-[#18212B] hover:bg-[#000000] px-4 py-2.5 rounded-xl shadow-[0_5px_14px_rgba(24,33,43,0.20)] transition-all">ایجاد پروژه</button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
