@extends('layouts.app')
@section('body')
<div
    class="min-h-screen bg-[#F5F7FA] flex"
    x-data="dashboard()"
    x-init="init()"
>
    <style>
        body.modal-open { overflow: hidden !important; position: fixed; width: 100%; }
        .project-card { transition: all 0.15s ease; }
        .project-card:hover { transform: translateY(-1px); border-color: #0069FF30; }
        .ws-item { transition: all 0.15s ease; }
        .ws-item.active { background: rgba(255,255,255,0.08); }
        .ws-item:hover { background: rgba(255,255,255,0.05); }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
        .search-highlight { background: #E8F0FE; border-radius: 2px; padding: 0 2px; }
    </style>

    {{-- Sidebar --}}
    <aside
        class="fixed md:sticky top-0 right-0 h-screen w-[220px] bg-[#003078] flex flex-col z-40 transition-transform duration-200 shrink-0"
        :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'"
    >
        <div class="px-4 py-3.5 flex items-center gap-2.5 border-b border-white/10">
            <div class="w-7 h-7 rounded-lg bg-[#0069FF] flex items-center justify-center">
                <span class="text-white font-black text-xs">N</span>
            </div>
            <span class="text-white font-bold text-[13px]">Neova</span>
            <button @click="sidebarOpen = false" class="mr-auto md:hidden text-white/50 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-2.5 px-2">
            <p class="text-[8px] font-bold text-blue-300/40 uppercase tracking-[0.15em] px-2.5 mb-1.5">فضاهای کاری</p>
            @forelse ($workspaces as $ws)
                <a
                    href="{{ route('dashboard', ['workspace' => $ws->slug]) }}"
                    class="ws-item flex items-center gap-2 px-2.5 py-[7px] rounded-lg mb-px {{ $activeWorkspace?->slug === $ws->slug ? 'active' : '' }}"
                >
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] font-semibold text-white/90 truncate">{{ $ws->name }}</p>
                        <p class="text-[9px] text-blue-300/40">{{ $ws->projects_count }} پروژه</p>
                    </div>
                </a>
            @empty
                <p class="text-[10px] text-blue-300/30 px-2.5">فضای کاری‌ای وجود ندارد</p>
            @endforelse
        </div>

        <div class="p-2 border-t border-white/10">
            <button
                @click="modalType = 'workspace'; showModal = true"
                class="w-full flex items-center justify-center gap-1.5 text-[10px] font-semibold text-blue-300/60 hover:text-white hover:bg-white/5 px-2.5 py-2 rounded-lg transition-all"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                فضای کاری جدید
            </button>
        </div>
    </aside>

    {{-- Sidebar backdrop (mobile) --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/30 z-30 md:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen min-w-0">
        {{-- Header --}}
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-[#E2E8F0]">
            <div class="px-5 h-12 flex items-center gap-3">
                <button @click="sidebarOpen = true" class="md:hidden text-[#64748B] hover:text-[#1A1D21] shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                {{-- Search --}}
                <div class="relative flex-1 max-w-md" @click.away="searchOpen = false; searchQuery = ''">
                    <div class="relative">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input
                            x-model="searchQuery"
                            @input.debounce.300ms="doSearch()"
                            @focus="searchOpen = true; if (!searchQuery) loadRecentSearches()"
                            @keydown.escape="searchOpen = false; searchQuery = ''"
                            @keydown.arrow-down.prevent="navigateResults(1)"
                            @keydown.arrow-up.prevent="navigateResults(-1)"
                            @keydown.enter.prevent="selectResult()"
                            type="text"
                            class="w-full text-[12px] font-medium bg-[#F1F5F9] border border-transparent rounded-lg pr-9 pl-3 py-[7px] focus:outline-none focus:bg-white focus:border-[#0069FF]/30 transition-all placeholder:text-[#94A3B8]"
                            placeholder="جستجو..."
                        >
                    </div>

                    {{-- Search Results Dropdown --}}
                    <div
                        x-show="searchOpen && (searchResults.length > 0 || recentSearches.length > 0 || searchQuery.length > 0)"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#E2E8F0] overflow-hidden z-50"
                    >
                        {{-- Recent Searches --}}
                        <template x-if="!searchQuery && recentSearches.length > 0">
                            <div>
                                <div class="flex items-center justify-between px-3 py-2 border-b border-[#F1F5F9]">
                                    <span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">اخیراً جستجو شده</span>
                                    <button @click="clearRecentSearches()" class="text-[9px] text-[#94A3B8] hover:text-[#1A1D21]">پاک کردن</button>
                                </div>
                                <template x-for="(item, idx) in recentSearches" :key="idx">
                                    <a
                                        :href="item.url"
                                        class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors"
                                        :class="selectedIdx === idx ? 'bg-[#F1F5F9]' : ''"
                                    >
                                        <span class="w-5 h-5 rounded-md flex items-center justify-center shrink-0" :class="{
                                            'bg-[#0069FF]/10 text-[#0069FF]': item.type === 'workspace',
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

                        {{-- Search Results --}}
                        <template x-if="searchQuery && searchResults.length > 0">
                            <div class="max-h-[300px] overflow-y-auto">
                                {{-- Workspaces --}}
                                <template x-if="searchResults.filter(r => r.type === 'workspace').length > 0">
                                    <div>
                                        <div class="px-3 py-1.5 border-b border-[#F1F5F9]">
                                            <span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">فضاهای کاری</span>
                                        </div>
                                        <template x-for="(item, idx) in searchResults.filter(r => r.type === 'workspace')" :key="'ws-'+idx">
                                            <a :href="item.url" @click="saveRecentSearch(item)" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors">
                                                <span class="w-5 h-5 rounded-md bg-[#0069FF]/10 flex items-center justify-center shrink-0">
                                                    <svg class="w-2.5 h-2.5 text-[#0069FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[11px] font-semibold text-[#1A1D21] truncate" x-text="item.name"></p>
                                                    <p class="text-[9px] text-[#94A3B8]" x-text="item.subtitle"></p>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                {{-- Projects --}}
                                <template x-if="searchResults.filter(r => r.type === 'project').length > 0">
                                    <div>
                                        <div class="px-3 py-1.5 border-b border-[#F1F5F9]">
                                            <span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">پروژه‌ها</span>
                                        </div>
                                        <template x-for="(item, idx) in searchResults.filter(r => r.type === 'project')" :key="'proj-'+idx">
                                            <a :href="item.url" @click="saveRecentSearch(item)" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors">
                                                <span class="w-5 h-5 rounded-md bg-[#22C55E]/10 flex items-center justify-center shrink-0">
                                                    <svg class="w-2.5 h-2.5 text-[#22C55E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[11px] font-semibold text-[#1A1D21] truncate" x-text="item.name"></p>
                                                    <p class="text-[9px] text-[#94A3B8]" x-text="item.subtitle"></p>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                {{-- Tasks --}}
                                <template x-if="searchResults.filter(r => r.type === 'task').length > 0">
                                    <div>
                                        <div class="px-3 py-1.5 border-b border-[#F1F5F9]">
                                            <span class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest">وظایف</span>
                                        </div>
                                        <template x-for="(item, idx) in searchResults.filter(r => r.type === 'task')" :key="'task-'+idx">
                                            <a :href="item.url" @click="saveRecentSearch(item)" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F8FAFC] transition-colors">
                                                <span class="w-5 h-5 rounded-md bg-[#F59E0B]/10 flex items-center justify-center shrink-0">
                                                    <svg class="w-2.5 h-2.5 text-[#F59E0B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-6 0h6"/></svg>
                                                </span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[11px] font-semibold text-[#1A1D21] truncate" x-text="item.name"></p>
                                                    <p class="text-[9px] text-[#94A3B8]" x-text="item.subtitle"></p>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- No Results --}}
                        <template x-if="searchQuery && searchResults.length === 0 && !searchLoading">
                            <div class="px-3 py-4 text-center">
                                <p class="text-[11px] text-[#94A3B8]">نتیجه‌ای یافت نشد</p>
                            </div>
                        </template>

                        {{-- Loading --}}
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

                <div class="flex items-center gap-2.5 shrink-0">
                    {{-- New Project Button --}}
                    @if ($activeWorkspace)
                        <button
                            @click="modalType = 'project'; showModal = true"
                            class="hidden sm:flex items-center gap-1.5 bg-[#0069FF] hover:bg-[#4D99FF] text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all active:scale-[0.97]"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            پروژه جدید
                        </button>
                    @endif

                    {{-- User Dropdown --}}
                    <div class="relative" @click.away="userDropdown = false">
                        <button
                            @click="userDropdown = !userDropdown"
                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-[#F1F5F9] transition-colors"
                        >
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#003B8E] to-[#0069FF] flex items-center justify-center">
                                <span class="text-[9px] text-white font-bold">{{ substr(auth()->user()->first_name ?? auth()->user()->name, 0, 1) }}</span>
                            </div>
                            <span class="text-[11px] font-semibold text-[#475569] hidden sm:block">{{ auth()->user()->full_name }}</span>
                            <svg class="w-3 h-3 text-[#94A3B8] transition-transform" :class="userDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div
                            x-show="userDropdown"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute left-0 top-full mt-1.5 w-52 bg-white rounded-xl border border-[#E2E8F0] overflow-hidden z-50"
                        >
                            <div class="px-3 py-2.5 border-b border-[#F1F5F9]">
                                <p class="text-[11px] font-bold text-[#1A1D21]">{{ auth()->user()->full_name }}</p>
                                <p class="text-[10px] text-[#94A3B8]">{{ auth()->user()->phone }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 text-[11px] font-medium text-[#475569] hover:bg-[#F8FAFC] transition-colors">
                                    <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    داشبورد
                                </a>
                            </div>
                            <div class="border-t border-[#F1F5F9] py-1">
                                <form action="{{ route('auth.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-[11px] font-medium text-[#EF4444] hover:bg-red-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        خروج
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-5">
            @if ($activeWorkspace)
                @if ($projects->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($projects as $project)
                            <a
                                href="{{ route('board', [$activeWorkspace->slug, $project->slug]) }}"
                                class="project-card block bg-white rounded-xl border border-[#E2E8F0] p-5 relative group"
                            >
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#0069FF]/10 flex items-center justify-center group-hover:bg-[#0069FF] transition-colors">
                                        <span class="text-[#0069FF] font-black text-xs group-hover:text-white transition-colors">{{ $project->key }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @foreach ($project->columns->take(4) as $col)
                                            <span class="w-2 h-2 rounded-full opacity-60" style="background: {{ $col->color ?? '#94A3B8' }}" title="{{ $col->title }}: {{ $col->tasks_count ?? $col->tasks->count() }}"></span>
                                        @endforeach
                                    </div>
                                </div>
                                <h3 class="text-[14px] font-bold text-[#1A1D21] mb-1 leading-relaxed">{{ $project->name }}</h3>
                                @if ($project->description)
                                    <p class="text-[11px] text-[#94A3B8] line-clamp-2 leading-relaxed">{{ $project->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-4 pt-3 border-t border-[#F1F5F9]">
                                    <div class="flex items-center gap-3">
                                        @foreach ($project->columns->take(4) as $col)
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-[#94A3B8]">{{ $col->tasks_count ?? $col->tasks->count() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <svg class="w-4 h-4 text-[#CBD5E1] group-hover:text-[#0069FF] transition-colors -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-[#E8F0FE] flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-[#0069FF]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-sm font-bold text-[#1A1D21] mb-1">پروژه‌ای وجود ندارد</p>
                        <p class="text-[11px] text-[#94A3B8] mb-4">اولین پروژه خود را بسازید</p>
                        <button @click="modalType = 'project'; showModal = true" class="text-[11px] font-semibold text-[#0069FF] hover:text-[#4D99FF] transition-colors">+ پروژه جدید</button>
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#F1F5F9] flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <p class="text-sm font-bold text-[#1A1D21] mb-1">فضای کاری انتخاب کنید</p>
                    <p class="text-[11px] text-[#94A3B8]">از منوی کناری یک فضای کاری انتخاب کنید</p>
                </div>
            @endif
        </main>
    </div>

    {{-- Modals --}}
    <div x-show="showModal" x-cloak x-effect="showModal ? document.body.classList.add('modal-open') : document.body.classList.remove('modal-open')">
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 bg-[#0A1628]/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
            <div class="min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white w-full max-w-sm rounded-2xl border border-[#E2E8F0] p-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" @click.stop>
                    <template x-if="modalType === 'workspace'">
                        <div>
                            <h3 class="text-sm font-bold text-[#1A1D21] mb-4">فضای کاری جدید</h3>
                            <form action="{{ route('dashboard.workspace.store') }}" method="POST">
                                @csrf
                                <input name="name" type="text" required class="w-full text-sm font-semibold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors mb-4 placeholder:text-[#CBD5E1]" placeholder="نام فضای کاری">
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#4D99FF] px-4 py-2 rounded-xl transition-all">ایجاد</button>
                                </div>
                            </form>
                        </div>
                    </template>
                    <template x-if="modalType === 'project'">
                        <div>
                            <h3 class="text-sm font-bold text-[#1A1D21] mb-4">پروژه جدید</h3>
                            <form action="{{ route('dashboard.project.store', $activeWorkspace?->slug) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">نام پروژه</label>
                                    <input name="name" type="text" required class="w-full text-sm font-semibold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors placeholder:text-[#CBD5E1]" placeholder="نام پروژه">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">کلید پروژه <span class="text-[#CBD5E1]">(اختیاری)</span></label>
                                    <input name="key" type="text" maxlength="10" class="w-full text-sm font-semibold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors uppercase placeholder:text-[#CBD5E1]" placeholder="مثلاً SCR">
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#4D99FF] px-4 py-2 rounded-xl transition-all">ایجاد</button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function dashboard() {
        return {
            sidebarOpen: false,
            showModal: false,
            modalType: 'workspace',
            activeWs: '{{ $activeWorkspace?->slug ?? "" }}',
            userDropdown: false,
            searchOpen: false,
            searchQuery: '',
            searchResults: [],
            searchLoading: false,
            recentSearches: [],
            selectedIdx: -1,

            init() {
                this.loadRecentSearches();
            },

            loadRecentSearches() {
                try {
                    this.recentSearches = JSON.parse(localStorage.getItem('neova_search') || '[]');
                } catch { this.recentSearches = []; }
            },

            saveRecentSearch(item) {
                let searches = this.recentSearches.filter(s => s.url !== item.url);
                searches.unshift(item);
                searches = searches.slice(0, 8);
                this.recentSearches = searches;
                localStorage.setItem('neova_search', JSON.stringify(searches));
            },

            clearRecentSearches() {
                this.recentSearches = [];
                localStorage.removeItem('neova_search');
            },

            async doSearch() {
                if (!this.searchQuery.trim()) {
                    this.searchResults = [];
                    this.loadRecentSearches();
                    return;
                }
                this.searchLoading = true;
                this.selectedIdx = -1;
                try {
                    const res = await fetch('{{ route("dashboard.search") }}?q=' + encodeURIComponent(this.searchQuery), {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    this.searchResults = await res.json();
                } catch {
                    this.searchResults = [];
                } finally {
                    this.searchLoading = false;
                }
            },

            navigateResults(dir) {
                const items = this.searchQuery ? this.searchResults : this.recentSearches;
                if (items.length === 0) return;
                this.selectedIdx = Math.max(-1, Math.min(items.length - 1, this.selectedIdx + dir));
            },

            selectResult() {
                const items = this.searchQuery ? this.searchResults : this.recentSearches;
                if (this.selectedIdx >= 0 && items[this.selectedIdx]) {
                    window.location.href = items[this.selectedIdx].url;
                }
            },
        };
    }
</script>
@endpush
@endsection
