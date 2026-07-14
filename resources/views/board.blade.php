<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تخته اسکرام</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body.modal-open { overflow: hidden !important; position: fixed; width: 100%; }
        .sortable-ghost { opacity: 0.4; background: #E8F0FE !important; border: 2px dashed #0069FF !important; box-shadow: none !important; }
        .sortable-chosen { box-shadow: 0 12px 28px rgba(0,105,255,0.18), 0 2px 8px rgba(0,0,0,0.12) !important; transform: rotate(1.5deg); z-index: 50; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        .checklist-bar { height: 6px; border-radius: 3px; background: #E2E8F0; overflow: hidden; }
        .checklist-bar-fill { height: 100%; border-radius: 3px; background: #0069FF; transition: width 0.3s ease; }
        .check-item input[type="checkbox"]:checked + span { text-decoration: line-through; color: #94A3B8; }
        .mobile-board-track { scrollbar-width: none; scroll-padding-inline: 1rem; overscroll-behavior-inline: contain; }
        .mobile-board-track::-webkit-scrollbar { display: none; }
        .mobile-column-tabs { scrollbar-width: none; }
        .mobile-column-tabs::-webkit-scrollbar { display: none; }
        .mobile-board-column { scroll-snap-align: center; scroll-snap-stop: always; }
        .mobile-task-list { touch-action: pan-y; }
        .task-drag-handle { touch-action: none; }
        body.mobile-task-dragging { user-select: none; -webkit-user-select: none; }
        body.mobile-task-dragging .mobile-board-track { scroll-behavior: auto !important; }
        .mobile-drag-edge {
            position: fixed;
            z-index: 48;
            top: 12rem;
            bottom: max(1rem, env(safe-area-inset-bottom));
            width: 4.5rem;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 120ms ease, background-color 120ms ease;
        }
        .mobile-drag-edge--left {
            left: 0;
            background: linear-gradient(to right, rgba(0, 105, 255, .2), transparent);
        }
        .mobile-drag-edge--right {
            right: 0;
            background: linear-gradient(to left, rgba(0, 105, 255, .2), transparent);
        }
        .mobile-drag-edge.is-available { opacity: .55; }
        .mobile-drag-edge.is-active { opacity: 1; }
        @media (prefers-reduced-motion: reduce) {
            .mobile-board-track { scroll-behavior: auto !important; }
            .mobile-drag-edge { transition: none; }
        }
    </style>
</head>
<body class="bg-[#F7F5F0] min-h-screen overflow-x-hidden" x-data="board()" x-init="init()" x-cloak>

    {{-- Top Navigation Bar --}}
    <x-navbar light fluid>
        <a href="{{ route('dashboard', ['workspace' => $workspace->slug]) }}" class="w-9 h-9 rounded-xl bg-[#F1EFEA] border border-[#E7E3DA] flex items-center justify-center text-[#475569] hover:text-[#18212B] hover:bg-white transition-colors shrink-0" aria-label="بازگشت">
            <svg class="w-4 h-4 scale-x-[-1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="min-w-0">
            <div class="flex items-center gap-2 min-w-0">
                @if ($project->key)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-[#F1EFEA] border border-[#E7E3DA] text-[10px] font-bold text-[#475569] shrink-0">{{ $project->key }}</span>
                @endif
                <span class="text-[#18212B] font-black text-[15px] truncate">{{ $project->name }}</span>
                @if ($project->visibility === 'private')
                    <span class="inline-flex items-center gap-1 text-[9px] font-bold text-[#FEF3C7] bg-white/10 border border-white/10 px-1.5 py-0.5 rounded-md shrink-0">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        خصوصی
                    </span>
                @endif
            </div>
            <span class="block text-[#94A3B8] text-[10px] mt-0.5 truncate">{{ $workspace->name }}</span>
        </div>


        @slot('search')
            <div class="relative hidden" @click.away="boardSearchOpen = false">
                <div class="relative">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        x-model="boardSearchQuery"
                        @input.debounce.200ms="boardSearchOpen = boardSearchQuery.length > 0"
                        @focus="boardSearchOpen = boardSearchQuery.length > 0"
                        @keydown.escape="boardSearchQuery = ''; boardSearchOpen = false"
                        type="text"
                        class="w-full text-[12px] font-medium text-white bg-white/8 border border-white/10 rounded-lg pr-9 pl-3 py-2.5 focus:outline-none focus:bg-white/12 focus:border-white/25 transition-all placeholder:text-white/45"
                        placeholder="جستجوی وظیفه…"
                    >
                </div>
                <div
                    x-show="boardSearchOpen && boardSearchQuery.length > 0"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="absolute top-full left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#E2E8F0] shadow-xl overflow-hidden z-50"
                >
                    <div class="px-3 py-2 border-b border-[#F1F5F9]">
                        <span class="text-[10px] font-bold text-[#94A3B8]"><span x-text="boardSearchResultCount()"></span> نتیجه یافت شد</span>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        <template x-for="col in columns" :key="col.id">
                            <template x-for="task in filteredTasks(col)" :key="task.dbId">
                                <div class="px-3 py-2.5 border-b border-[#F1F5F9] last:border-0 hover:bg-[#F8FAFC] cursor-pointer transition-colors"
                                     @click="boardSearchOpen = false; boardSearchQuery = ''">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="w-2 h-2 rounded-full shrink-0" :class="col.dotColor"></span>
                                        <span class="text-[9px] font-bold text-[#94A3B8]" x-text="task.id"></span>
                                        <span class="text-[9px] text-[#94A3B8]" x-text="col.title"></span>
                                    </div>
                                    <p class="text-[12px] font-bold text-[#1A1D21] truncate" x-html="highlightText(task.title, boardSearchQuery)"></p>
                                </div>
                            </template>
                        </template>
                        <div x-show="boardSearchResultCount() === 0" class="px-3 py-6 text-center">
                            <p class="text-[11px] text-[#94A3B8]">نتیجه‌ای یافت نشد</p>
                        </div>
                    </div>
                </div>
            </div>
        @endslot

        @slot('actions')
            <span class="hidden md:inline-flex items-center text-[#64748B] text-xs font-bold px-3 py-1.5 rounded-full bg-[#F1EFEA] border border-[#E7E3DA]" x-text="totalTasks() + ' وظیفه'"></span>
            @if ($canManageProject)
                <button
                    @click="openProjectDrawer()"
                    aria-label="مدیریت پروژه"
                    class="hidden md:flex items-center justify-center w-9 h-9 text-[#64748B] hover:text-[#18212B] bg-[#F1EFEA] hover:bg-white border border-[#E7E3DA] rounded-xl transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0015 19.4a1.7 1.7 0 00-1 .6 1.7 1.7 0 00-.4 1.1V21h-4v-.1A1.7 1.7 0 008.6 19.4a1.7 1.7 0 00-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-.6-1 1.7 1.7 0 00-1.1-.4H3v-4h.1A1.7 1.7 0 004.6 8.6a1.7 1.7 0 00-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 009 4.6a1.7 1.7 0 001-.6 1.7 1.7 0 00.4-1.1V3h4v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0019.4 9c.1.38.31.72.6 1 .3.27.68.41 1.1.4h.1v4h-.1a1.7 1.7 0 00-1.7.6z"/></svg>
                </button>
            @endif
            @if ($canEdit)
                <button
                    @click="openAddModal(columns[activeColumnIndex]?.id || columns[0]?.id)"
                    class="hidden md:flex items-center justify-center w-9 h-9 bg-[#0069FF] hover:bg-[#4D99FF] text-white rounded-xl transition-all duration-150 shadow-md shadow-[#0069FF]/25 hover:shadow-lg hover:shadow-[#0069FF]/30 active:scale-[0.97]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            @else
                <span class="hidden md:inline-flex text-[10px] font-bold text-blue-100 bg-white/10 rounded-md px-2.5 py-1.5">فقط مشاهده</span>
            @endif
        @endslot

        @slot('mobile')
            <div class="max-w-[1600px] mx-auto px-3 py-2.5 flex items-center gap-2.5">
                <a href="{{ route('dashboard', ['workspace' => $workspace->slug]) }}" class="w-11 h-11 rounded-xl bg-[#F1EFEA] border border-[#E7E3DA] flex items-center justify-center text-[#475569] shrink-0 active:bg-white" aria-label="بازگشت">
                    <svg class="w-4.5 h-4.5 scale-x-[-1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-[#18212B] font-black text-[13px] truncate">{{ $project->name }}</span>
                        @if ($project->visibility === 'private')
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-[#FEF3C7] bg-white/10 border border-white/10 px-1.5 py-0.5 rounded-md shrink-0">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                خصوصی
                            </span>
                        @endif
                        @if ($project->key)
                            <span class="px-1.5 py-0.5 rounded bg-white/10 border border-white/10 text-[9px] font-bold text-blue-100 shrink-0">{{ $project->key }}</span>
                        @endif
                    </div>
                    <span class="block text-[#94A3B8] text-[9px] mt-0.5 truncate">{{ $workspace->name }}</span>
                </div>
                @if ($canEdit)
                    <button @click="openAddModal(columns[activeColumnIndex]?.id || columns[0]?.id)" class="h-11 px-3 rounded-xl bg-[#0069FF] text-white flex items-center gap-1.5 text-[10px] font-black shadow-lg shadow-black/10 active:scale-[0.97] shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        وظیفه جدید
                    </button>
                @else
                    <span class="text-[9px] font-bold text-blue-100 bg-white/10 rounded-lg px-2.5 py-2">فقط مشاهده</span>
                @endif
                <div class="relative shrink-0" @click.away="mobileActionsOpen = false">
                    <button @click="mobileActionsOpen = !mobileActionsOpen" class="w-11 h-11 rounded-xl border border-[#E7E3DA] text-[#64748B] flex items-center justify-center active:bg-white" aria-label="گزینه‌های پروژه" :aria-expanded="mobileActionsOpen">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.7"/><circle cx="12" cy="12" r="1.7"/><circle cx="12" cy="19" r="1.7"/></svg>
                    </button>
                    <div x-show="mobileActionsOpen" x-transition class="absolute left-0 top-full mt-2 w-48 rounded-xl bg-white border border-[#E2E8F0] shadow-xl overflow-hidden z-50">
                        <div class="px-3 py-2.5 text-[10px] font-bold text-[#64748B] border-b border-[#F1F5F9]">
                            <span x-text="totalTasks()"></span> وظیفه
                        </div>
                        @if ($canManageProject)
                            <button @click="mobileActionsOpen = false; openProjectDrawer()" class="w-full min-h-11 px-3 flex items-center gap-2 text-right text-[11px] font-bold text-[#334155] hover:bg-[#F8FAFC]">
                                <svg class="w-4 h-4 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0015 19.4a1.7 1.7 0 00-1 .6 1.7 1.7 0 00-.4 1.1V21h-4v-.1A1.7 1.7 0 008.6 19.4a1.7 1.7 0 00-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 004.6 15a1.7 1.7 0 00-.6-1 1.7 1.7 0 00-1.1-.4H3v-4h.1A1.7 1.7 0 004.6 8.6a1.7 1.7 0 00-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 009 4.6a1.7 1.7 0 001-.6 1.7 1.7 0 00.4-1.1V3h4v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0019.4 9c.1.38.31.72.6 1 .3.27.68.41 1.1.4h.1v4h-.1a1.7 1.7 0 00-1.7.6z"/></svg>
                                مدیریت پروژه
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endslot
    </x-navbar>

    {{-- Board --}}
    <main class="max-w-[1600px] mx-auto md:px-5 md:py-5">

        {{-- Primary board search and commands --}}
        <section class="px-4 pt-4 md:px-0 md:pt-0 mb-4">
            <div class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
                <div class="relative flex-1" @click.away="boardSearchOpen = false">
                    <svg class="absolute right-5 top-1/2 -translate-y-1/2 w-5 h-5 text-[#64748B] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/></svg>
                    <input x-ref="boardSearch" x-model="boardSearchQuery" @input="boardSearchOpen = boardSearchQuery.length > 0" @focus="boardSearchOpen = boardSearchQuery.length > 0" @keydown.escape="clearBoardSearch()" type="search" class="w-full h-14 md:h-16 rounded-2xl border border-[#DDD8CE] bg-white pr-14 pl-20 text-sm md:text-base font-bold text-[#18212B] shadow-[0_8px_24px_rgba(24,33,43,0.05)] outline-none transition-all placeholder:text-[#A8A39A] focus:border-[#93B4F7] focus:ring-4 focus:ring-[#2563EB]/10" placeholder="جستجوی کارها و پروژه‌ها…">
                    <kbd class="absolute left-4 top-1/2 -translate-y-1/2 inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-[#DDD8CE] bg-[#FBFAF7] px-2 text-xs font-black text-[#64748B]">/</kbd>
                    <div x-show="boardSearchOpen && boardSearchQuery.length > 0" x-transition class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl border border-[#E7E3DA] shadow-2xl overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-[#F1EFEA] flex items-center justify-between"><span class="text-[11px] font-bold text-[#94A3B8]">نتایج جستجو</span><span class="text-[11px] font-black text-[#2563EB]" x-text="boardSearchResultCount() + ' نتیجه'"></span></div>
                        <div class="max-h-72 overflow-y-auto">
                            <template x-for="col in columns" :key="'search-' + col.id"><template x-for="task in filteredTasks(col)" :key="'result-' + task.dbId"><button type="button" @click="boardSearchOpen = false; boardSearchQuery = ''; openEditModal(task, col.id)" class="w-full text-right px-4 py-3 border-b border-[#F7F5F0] hover:bg-[#FBFAF7] transition-colors"><span class="text-[10px] font-bold text-[#94A3B8]" x-text="col.title + ' · ' + task.id"></span><span class="block mt-1 text-sm font-black text-[#18212B]" x-html="highlightText(task.title, boardSearchQuery)"></span></button></template></template>
                            <div x-show="boardSearchResultCount() === 0" class="px-4 py-8 text-center text-xs font-bold text-[#94A3B8]">نتیجه‌ای پیدا نشد</div>
                        </div>
                    </div>
                </div>
                @if ($canEdit)
                    <button @click="openColumnModal()" class="h-14 md:h-16 px-5 rounded-2xl bg-[#18212B] hover:bg-[#253342] text-white text-xs md:text-sm font-black inline-flex items-center justify-center gap-2 shadow-lg shadow-[#18212B]/10 transition-all active:scale-[.98] shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.2" d="M12 5v14m-7-7h14"/></svg>افزودن ستون</button>
                @endif
            </div>
            <div class="mt-2 flex items-center gap-2 text-[10px] font-bold text-[#A8A39A]"><span class="w-1.5 h-1.5 rounded-full bg-[#22C55E]"></span><span x-text="totalTasks() + ' وظیفه در ' + columns.length + ' ستون'"></span><span class="mr-auto hidden md:inline">برای جستجوی سریع کلید / یا Ctrl/⌘ K را بزنید</span></div>
        </section>

        {{-- Desktop board --}}
        <div class="hidden md:flex gap-3 items-start overflow-x-auto pb-4" style="direction: rtl;">
            <template x-for="(column, colIdx) in columns" :key="column.id">
                <div class="flex flex-col shrink-0 transition-[width] duration-200" :class="column.collapsed ? 'w-14' : 'min-w-[280px] flex-1'">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full shadow-sm" :class="column.dotColor"></span>
                            <h2 x-show="!column.collapsed" class="text-[13px] font-black text-[#18212B] truncate" x-text="column.title"></h2>
                            <span class="text-[10px] font-bold min-w-[20px] text-center px-1.5 py-0.5 rounded-full" :class="column.badgeClass" x-text="column.tasks.length"></span>
                        </div>
                        @if ($canEdit)
                            <button x-show="!column.collapsed" @click="openAddModal(column.id)" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-[#0069FF] hover:bg-[#E8F0FE] transition-all" title="افزودن وظیفه">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                            <button x-show="!column.collapsed" @click="confirmDeleteColumn(column)" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-red-500 hover:bg-red-50 transition-all" title="حذف ستون">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M6 6l12 12M18 6 6 18"/></svg>
                            </button>
                        @endif
                        <button @click="column.collapsed = !column.collapsed" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-[#18212B] hover:bg-white transition-all" :title="column.collapsed ? 'باز کردن ستون' : 'جمع کردن ستون'"><svg class="w-3.5 h-3.5 transition-transform" :class="column.collapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="m15 18-6-6 6-6"/></svg></button>
                    </div>

                    <div x-show="!column.collapsed" class="flex flex-col gap-2.5 min-h-[240px] max-h-[calc(100vh-14rem)] overflow-y-auto rounded-2xl p-2.5 bg-[#EFEEE9] border border-[#E5E1D8]" :id="'col-desktop-' + column.id" x-init="$nextTick(() => initSortable(column.id, 'desktop'))">
                        <template x-for="task in filteredTasks(column)" :key="task.dbId">
                            <div class="bg-white rounded-xl border border-[#E2E8F0] p-3.5 cursor-grab active:cursor-grabbing hover:border-[#0069FF]/30 hover:shadow-md hover:shadow-[#0069FF]/8 transition-all duration-150 group relative" :data-id="task.dbId" :data-column="column.id" @click="openEditModal(task, column.id)">
                                <div class="absolute top-0 right-0 w-1 h-full rounded-r-xl" :class="{ 'bg-[#EF4444]': task.priority === 'بالا', 'bg-[#8B5CF6]': task.priority === 'متوسط', 'bg-[#64748B]': task.priority === 'پایین' }"></div>
                                <div class="pr-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-bold text-[#94A3B8] tracking-wider" x-text="task.id"></span>
                                        <div class="flex gap-1">
                                            <template x-for="tag in task.tags" :key="tag">
                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" :class="getTagClass(tag)" x-text="tag"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-[13px] font-bold text-[#1A1D21] mb-1.5 leading-relaxed" x-html="highlightText(task.title, boardSearchQuery)"></p>
                                    <p x-show="task.description" class="text-[11px] text-[#64748B] leading-relaxed line-clamp-2 mb-2.5" x-html="highlightText(task.description, boardSearchQuery)"></p>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-[#F1F5F9]">
                                        <div class="flex items-center -space-x-1.5 space-x-reverse">
                                            <template x-for="(a, ai) in (task.assignees || []).slice(0, 3)" :key="ai">
                                                <div class="w-5 h-5 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shadow-sm ring-2 ring-white" :style="'z-index:' + (10 - ai)">
                                                    <span class="text-[7px] text-white font-bold" x-text="a.charAt(0)"></span>
                                                </div>
                                            </template>
                                            <span x-show="(task.assignees || []).length > 3" class="text-[9px] text-[#94A3B8] font-bold mr-1" x-text="'+' + ((task.assignees || []).length - 3)"></span>
                                        </div>
                                        <div class="flex items-center gap-1" x-show="task.dueDate">
                                            <svg class="w-3 h-3 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[10px] font-medium" :class="isOverdue(task.dueDate) ? 'text-red-500' : 'text-[#94A3B8]'" x-text="formatDate(task.dueDate)"></span>
                                        </div>
                                    </div>
                                </div>
                                @if ($canEdit)
                                    <div class="absolute top-3 left-2 flex gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click.stop="confirmDelete(column.id, task.dbId)" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-red-500 hover:bg-red-50 transition-all" title="حذف">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </template>
                        <div x-show="column.tasks.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="w-10 h-10 rounded-xl bg-[#E8F0FE] flex items-center justify-center mb-2">
                                <svg class="w-5 h-5 text-[#0069FF]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <p class="text-[11px] text-[#94A3B8]">وظیفه‌ای نیست</p>
                        </div>
                    </div>
                </div>
            </template>
            @if ($canEdit)
                <button @click="openColumnModal()" class="min-w-[72px] w-[72px] min-h-[240px] rounded-2xl border-2 border-dashed border-[#D7D1C5] hover:border-[#2563EB] hover:bg-[#EAF1FF] text-[#64748B] hover:text-[#2563EB] flex items-center justify-center transition-colors" title="افزودن ستون"><span class="[writing-mode:vertical-rl] text-xs font-black">+ افزودن ستون</span></button>
            @endif
        </div>

        {{-- Mobile column navigator --}}
        <section class="md:hidden bg-white border-b border-[#DCE4EE] shadow-[0_5px_18px_rgba(7,27,51,0.05)] sticky top-[116px] z-20">
            <div class="mobile-column-tabs flex gap-1.5 overflow-x-auto px-3 pt-2.5 pb-2" role="tablist" aria-label="ستون‌های تخته">
                <template x-for="(column, index) in columns" :key="'tab-' + column.id">
                    <button
                        @click="scrollToColumn(index)"
                        class="min-h-11 px-3 rounded-xl border flex items-center gap-1.5 whitespace-nowrap text-[10px] font-black transition-colors"
                        :class="activeColumnIndex === index ? 'bg-[#EAF2FF] border-[#9FC1FF] text-[#005CE6]' : 'bg-white border-[#E2E8F0] text-[#64748B]'"
                        :aria-current="activeColumnIndex === index ? 'true' : 'false'"
                        :aria-selected="activeColumnIndex === index ? 'true' : 'false'"
                        role="tab"
                    >
                        <span x-text="column.title"></span>
                        <span class="min-w-5 h-5 px-1 rounded-full flex items-center justify-center text-[9px]" :class="activeColumnIndex === index ? 'bg-[#0069FF] text-white' : 'bg-[#F1F5F9] text-[#64748B]'" x-text="toPersianDigits(column.tasks.length)"></span>
                    </button>
                </template>
            </div>
            <div class="px-4 pb-2.5 flex items-center justify-between">
                <span class="text-[9px] font-bold text-[#64748B]" x-text="toPersianDigits(activeColumnIndex + 1) + ' از ' + toPersianDigits(columns.length)"></span>
                <div class="flex items-center gap-1.5" aria-hidden="true">
                    <template x-for="(_, index) in columns" :key="'dot-' + index">
                        <button tabindex="-1" @click="scrollToColumn(index)" class="h-1.5 rounded-full transition-all" :class="activeColumnIndex === index ? 'w-5 bg-[#0069FF]' : 'w-1.5 bg-[#CBD5E1]'"></button>
                    </template>
                </div>
                <span class="text-[9px] font-bold text-[#94A3B8] flex items-center gap-1">
                    ورق بزنید
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7l-5 5 5 5m8-10l5 5-5 5M3 12h18"/></svg>
                </span>
            </div>
        </section>

        {{-- Mobile one-column swipe board --}}
        <div
            x-ref="mobileBoardTrack"
            @scroll.passive="handleMobileBoardScroll()"
            class="mobile-board-track md:hidden flex gap-3 overflow-x-auto snap-x snap-mandatory px-4 pt-4 pb-8"
            style="direction: rtl;"
            aria-label="تخته پروژه"
        >
            <template x-for="(column, colIdx) in columns" :key="'mobile-' + column.id">
                <section
                    class="mobile-board-column flex-none w-[calc(100%_-_32px)] min-w-[calc(100%_-_32px)]"
                    :data-column-index="colIdx"
                    :aria-label="column.title"
                >
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full shadow-sm" :class="column.dotColor"></span>
                            <h2 class="text-[14px] font-black text-[#172B4D]" x-text="column.title"></h2>
                            <span class="text-[10px] font-bold min-w-[22px] text-center px-1.5 py-0.5 rounded-full" :class="column.badgeClass" x-text="toPersianDigits(column.tasks.length)"></span>
                        </div>
                        @if ($canEdit)
                            <button @click="openAddModal(column.id)" class="w-11 h-11 rounded-xl flex items-center justify-center text-[#0069FF] bg-[#E8F0FE] active:bg-[#D7E6FF]" :aria-label="'افزودن وظیفه به ' + column.title">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        @endif
                    </div>
                    <div
                        class="mobile-task-list flex flex-col gap-3 min-h-[calc(100dvh-18rem)] rounded-2xl p-2.5 bg-[#E2E8F0]/55 border transition-colors"
                        :class="mobileDragActive && activeColumnIndex === colIdx ? 'border-[#0069FF]/60 bg-[#DCE9FF]/65' : 'border-[#CBD5E1]/50'"
                        :id="'col-mobile-' + column.id"
                        x-init="$nextTick(() => initSortable(column.id, 'mobile'))"
                    >
                        <template x-for="task in filteredTasks(column)" :key="'mobile-task-' + task.dbId">
                            <article class="bg-white rounded-2xl border border-[#DDE5EF] p-4 hover:border-[#AFCBFF] transition-colors group relative shadow-[0_3px_12px_rgba(7,27,51,0.05)]" :data-id="task.dbId" :data-column="column.id" @click="if (canOpenTaskFromCard()) openEditModal(task, column.id)">
                                <div class="absolute top-0 right-0 w-1 h-full rounded-r-2xl" :class="{ 'bg-[#EF4444]': task.priority === 'بالا', 'bg-[#8B5CF6]': task.priority === 'متوسط', 'bg-[#64748B]': task.priority === 'پایین' }"></div>
                                <div class="pr-2">
                                    <div class="flex items-start justify-between gap-2 mb-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="tag in task.tags" :key="tag">
                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" :class="getTagClass(tag)" x-text="tag"></span>
                                            </template>
                                        </div>
                                        @if ($canEdit)
                                            <button @click.stop class="task-drag-handle w-11 h-11 -mt-2.5 -ml-2.5 rounded-xl text-[#94A3B8] flex items-center justify-center active:bg-[#F1F5F9] cursor-grab" aria-label="جابجایی وظیفه">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="8" cy="6" r="1.5"/><circle cx="16" cy="6" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="18" r="1.5"/><circle cx="16" cy="18" r="1.5"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                    <span class="block text-[9px] font-bold text-[#94A3B8] mb-1.5" x-text="task.id"></span>
                                    <p class="text-[13px] font-black text-[#172B4D] leading-6" x-html="highlightText(task.title, boardSearchQuery)"></p>
                                    <p x-show="task.description" class="text-[11px] text-[#64748B] leading-6 line-clamp-2 mt-1.5" x-html="highlightText(task.description, boardSearchQuery)"></p>
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-[#EEF2F6]">
                                        <div class="flex items-center -space-x-1.5 space-x-reverse">
                                            <template x-for="(a, ai) in (task.assignees || []).slice(0, 3)" :key="ai">
                                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shadow-sm ring-2 ring-white" :style="'z-index:' + (10 - ai)">
                                                    <span class="text-[8px] text-white font-bold" x-text="a.charAt(0)"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-1" x-show="task.dueDate">
                                            <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[10px] font-bold" :class="isOverdue(task.dueDate) ? 'text-red-500' : 'text-[#64748B]'" x-text="formatDate(task.dueDate)"></span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </template>
                        <div x-show="column.tasks.length === 0" class="flex flex-col items-center justify-center min-h-52 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-white text-[#0069FF]/55 flex items-center justify-center mb-3 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                            <p class="text-[11px] font-bold text-[#94A3B8]">وظیفه‌ای در این ستون نیست</p>
                        </div>
                    </div>
                </section>
            </template>
        </div>

        <div
            x-show="swipeHintVisible && !mobileDragActive"
            x-transition.opacity
            class="md:hidden fixed bottom-[max(1rem,env(safe-area-inset-bottom))] left-1/2 -translate-x-1/2 z-20 w-[calc(100%_-_2rem)] max-w-sm"
        >
            <button @click="dismissSwipeHint()" class="w-full min-h-11 px-4 py-2.5 rounded-xl bg-[#071B33] text-white shadow-xl flex items-center justify-center gap-2 text-[10px] font-bold">
                <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7l-5 5 5 5m8-10l5 5-5 5M3 12h18"/></svg>
                برای جابه‌جایی بین ستون‌ها ورق بزنید
            </button>
        </div>
        <p class="sr-only" aria-live="polite" x-text="'ستون ' + (columns[activeColumnIndex]?.title || '')"></p>
    </main>

    <template x-if="mobileDragActive">
        <div class="md:hidden">
            <div
                class="mobile-drag-edge mobile-drag-edge--left"
                :class="{
                    'is-available': activeColumnIndex < columns.length - 1,
                    'is-active': mobileDragDirection === 'next',
                }"
                aria-hidden="true"
            >
                <div x-show="activeColumnIndex < columns.length - 1" class="w-10 h-16 rounded-r-2xl bg-[#0069FF] text-white shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M15 19l-7-7 7-7"/></svg>
                </div>
            </div>
            <div
                class="mobile-drag-edge mobile-drag-edge--right"
                :class="{
                    'is-available': activeColumnIndex > 0,
                    'is-active': mobileDragDirection === 'previous',
                }"
                aria-hidden="true"
            >
                <div x-show="activeColumnIndex > 0" class="w-10 h-16 rounded-l-2xl bg-[#0069FF] text-white shadow-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
            <div class="fixed z-50 bottom-[max(1rem,env(safe-area-inset-bottom))] left-1/2 -translate-x-1/2 max-w-[calc(100%_-_2rem)] rounded-xl bg-[#071B33] text-white px-4 py-2.5 shadow-xl pointer-events-none">
                <p class="text-[10px] font-bold whitespace-nowrap" x-text="mobileDragStatusText()"></p>
            </div>
        </div>
    </template>

    {{-- Project management drawer --}}
    <div x-show="projectDrawerOpen" class="fixed inset-0 z-[55]" @keydown.escape.window="closeProjectDrawer()">
        <div x-show="projectDrawerOpen" x-transition.opacity class="absolute inset-0 bg-[#071B33]/45 backdrop-blur-[2px]" @click="closeProjectDrawer()"></div>
        <aside
            x-show="projectDrawerOpen"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 -translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-180"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 -translate-x-full"
            class="absolute inset-y-0 left-0 w-full sm:w-[430px] bg-white shadow-[18px_0_50px_rgba(7,27,51,0.22)] flex flex-col"
            @click.stop
        >
            <header class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <h2 class="text-base font-black text-[#071B33]">مدیریت پروژه</h2>
                    <p class="text-[11px] text-[#64748B] mt-1" x-text="projectForm.name"></p>
                </div>
                <button @click="closeProjectDrawer()" class="w-9 h-9 rounded-lg text-[#64748B] hover:text-[#071B33] hover:bg-[#F1F5F9] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </header>

            <div class="flex border-b border-[#E2E8F0] px-5">
                <button @click="projectDrawerTab = 'members'" class="px-1 py-3.5 ml-6 text-xs font-black border-b-2" :class="projectDrawerTab === 'members' ? 'text-[#1668FF] border-[#1668FF]' : 'text-[#64748B] border-transparent'">اعضای پروژه</button>
                <button @click="projectDrawerTab = 'settings'" class="px-1 py-3.5 text-xs font-black border-b-2" :class="projectDrawerTab === 'settings' ? 'text-[#1668FF] border-[#1668FF]' : 'text-[#64748B] border-transparent'">تنظیمات</button>
                <button @click="projectDrawerTab = 'activity'; if (activityItems.length === 0) loadActivity()" class="px-1 py-3.5 mr-6 text-xs font-black border-b-2" :class="projectDrawerTab === 'activity' ? 'text-[#1668FF] border-[#1668FF]' : 'text-[#64748B] border-transparent'">فعالیت‌ها</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <section x-show="projectDrawerTab === 'members'">
                    <div class="mb-4">
                        <h3 class="text-sm font-black text-[#071B33]">تیم پروژه</h3>
                        <p class="text-[11px] leading-5 text-[#64748B] mt-1">افراد انتخاب‌شده می‌توانند به وظیفه‌ها تخصیص داده شوند و در گفتگوها منشن شوند.</p>
                    </div>
                    <div class="relative mb-4">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input x-model="projectMemberSearch" class="w-full text-xs border-2 border-[#E2E8F0] rounded-xl pr-10 pl-3 py-3 focus:outline-none focus:border-[#1668FF]" placeholder="جستجوی اعضای فضای کاری…">
                    </div>
                    <div class="space-y-2">
                        <template x-for="person in filteredWorkspacePeople()" :key="person.id">
                            <button
                                @click="toggleProjectMember(person)"
                                :disabled="projectMemberSaving === person.id"
                                class="w-full flex items-center gap-3 rounded-xl border p-3 text-right transition-all disabled:opacity-60"
                                :class="isProjectMember(person.id) ? 'border-[#BBD1FF] bg-[#F4F8FF]' : 'border-[#E2E8F0] hover:border-[#B8C4D4] bg-white'"
                            >
                                <div class="w-9 h-9 rounded-full bg-[#071B33] text-white flex items-center justify-center text-xs font-black" x-text="person.name.charAt(0)"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] font-bold text-[#172B4D] truncate" x-text="person.name"></p>
                                    <p class="text-[10px] text-[#94A3B8] mt-0.5" x-text="person.phone"></p>
                                </div>
                                <span class="w-6 h-6 rounded-md flex items-center justify-center border-2" :class="isProjectMember(person.id) ? 'bg-[#1668FF] border-[#1668FF] text-white' : 'border-[#CBD5E1] text-transparent'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            </button>
                        </template>
                    </div>
                    <div x-show="projectMembers.length > 0" class="mt-5 pt-5 border-t border-[#E2E8F0]">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-[11px] font-bold text-[#071B33]">فیلتر وظایف</h4>
                            <button x-show="filterByAssignee.length > 0" @click="clearAssigneeFilter()" class="text-[9px] font-bold text-[#EF4444] hover:text-red-600">پاک کردن</button>
                        </div>
                        <div class="space-y-1.5">
                            <template x-for="member in projectMembers" :key="member.id">
                                <button
                                    @click="toggleAssigneeFilter(member.name)"
                                    class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-right transition-all"
                                    :class="isAssigneeFilterActive(member.name) ? 'bg-[#EAF1FF] border border-[#BBD1FF]' : 'hover:bg-[#F8FAFC]'"
                                >
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0" :class="isAssigneeFilterActive(member.name) ? 'bg-[#1668FF] text-white' : 'bg-[#E8F0FE] text-[#0069FF]'">
                                        <span class="text-[8px] font-bold" x-text="member.name.charAt(0)"></span>
                                    </div>
                                    <span class="text-[11px] font-bold" :class="isAssigneeFilterActive(member.name) ? 'text-[#1668FF]' : 'text-[#475569]'" x-text="member.name"></span>
                                    <svg x-show="isAssigneeFilterActive(member.name)" class="w-3.5 h-3.5 text-[#1668FF] mr-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
                        <p x-show="filterByAssignee.length > 0" class="text-[9px] text-[#94A3B8] mt-2 text-center">نمایش وظایف <span x-text="filterByAssignee.length"></span> عضو</p>
                    </div>
                </section>

                <section x-show="projectDrawerTab === 'settings'" class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">نام پروژه</label>
                        <input x-model="projectForm.name" class="w-full text-sm font-bold border-2 border-[#E2E8F0] rounded-xl px-3.5 py-3 focus:outline-none focus:border-[#1668FF]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">کلید پروژه</label>
                        <input x-model="projectForm.key" maxlength="10" dir="ltr" class="w-full text-sm font-bold uppercase border-2 border-[#E2E8F0] rounded-xl px-3.5 py-3 focus:outline-none focus:border-[#1668FF]">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">توضیحات پروژه</label>
                        <textarea x-model="projectForm.description" rows="5" class="w-full text-sm leading-7 border-2 border-[#E2E8F0] rounded-xl px-3.5 py-3 focus:outline-none focus:border-[#1668FF] resize-none" placeholder="هدف و محدوده پروژه را توضیح دهید…"></textarea>
                    </div>
                </section>
                <section x-show="projectDrawerTab === 'activity'" class="space-y-4">
                    <div>
                        <h3 class="text-sm font-black text-[#071B33]">فعالیت‌های پروژه</h3>
                        <p class="text-[11px] leading-5 text-[#64748B] mt-1">تمام تغییرات وظایف در این پروژه</p>
                    </div>
                    <div class="relative">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input x-model="activitySearch" class="w-full text-xs border-2 border-[#E2E8F0] rounded-xl pr-10 pl-3 py-3 focus:outline-none focus:border-[#1668FF]" placeholder="جستجو در فعالیت‌ها…">
                    </div>
                    <div x-show="activityLoading" class="py-8 text-center">
                        <svg class="animate-spin w-5 h-5 text-[#1668FF] mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <p class="text-[11px] text-[#94A3B8] mt-2">در حال بارگذاری…</p>
                    </div>
                    <div x-show="!activityLoading" class="space-y-2">
                        <template x-for="(item, idx) in filteredActivity()" :key="idx">
                            <div class="flex gap-3 p-3 rounded-xl border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                     :class="{
                                         'bg-[#DCFCE7] text-[#16A34A]': item.kind === 'task_assigned',
                                         'bg-[#E8F0FE] text-[#0069FF]': item.kind === 'task_updated',
                                         'bg-[#FEF3C7] text-[#D97706]': item.kind === 'task_moved',
                                         'bg-[#F3E8FF] text-[#9333EA]': item.kind === 'task_mentioned',
                                     }">
                                    <svg x-show="item.kind === 'task_assigned'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <svg x-show="item.kind === 'task_updated'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <svg x-show="item.kind === 'task_moved'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <svg x-show="item.kind === 'task_mentioned'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] leading-5 text-[#334155]" x-html="highlightText(item.message || '', activitySearch)"></p>
                                    <p class="text-[9px] text-[#94A3B8] mt-1" x-text="item.time || ''"></p>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredActivity().length === 0 && !activityLoading" class="py-8 text-center">
                            <p class="text-[11px] text-[#94A3B8]">فعالیتی یافت نشد</p>
                        </div>
                    </div>
                </section>
            </div>

            <footer class="border-t border-[#E2E8F0] p-4 bg-[#F8FAFC]">
                <button x-show="projectDrawerTab === 'settings'" @click="saveProjectSettings()" :disabled="projectSettingsSaving" class="w-full bg-[#1668FF] hover:bg-[#0E57DB] disabled:opacity-60 text-white text-xs font-black rounded-xl px-4 py-3">
                    <span x-text="projectSettingsSaving ? 'در حال ذخیره…' : 'ذخیره تغییرات'"></span>
                </button>
                <p x-show="projectDrawerTab === 'members'" class="text-[10px] text-[#64748B] text-center"><span x-text="projectMembers.length"></span> عضو در تیم پروژه</p>
            </footer>
        </aside>
    </div>

    {{-- ======== Trello-Style Task Modal ======== --}}
    {{-- Backdrop (fixed, never scrolls) --}}
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-[#0A1628]/60 backdrop-blur-sm"
        @click="closeModal()"
        x-effect="if (showModal) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open') }"
    ></div>

    {{-- Scroll container (fixed, independent of backdrop) --}}
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="closeModal()"
    >
        <div class="min-h-screen flex items-start justify-center p-4 pt-8 pb-8 md:pt-12 md:pb-12">
            <div
                class="relative bg-white w-full max-w-[820px] rounded-2xl shadow-2xl shadow-black/25 overflow-hidden my-auto"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                @click.stop
            >
                {{-- Header --}}
                <div class="bg-gradient-to-l from-[#003B8E] to-[#0069FF] px-4 md:px-6 py-3.5 md:py-4 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <h3 class="text-white font-bold text-sm" x-text="editingTask ? 'ویرایش وظیفه' : 'وظیفه جدید'"></h3>
                        <span x-show="editingTask" class="text-blue-200 text-[10px] font-bold bg-white/15 px-2 py-0.5 rounded-md" x-text="form.id"></span>
                    </div>
                    <button @click="closeModal()" class="text-white/70 hover:text-white transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/10">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body: Main + Sidebar (responsive) --}}
                <div class="flex flex-col md:flex-row" style="direction: rtl;">
                    {{-- Main Content Area --}}
                    <div class="flex-1 min-w-0 p-4 md:p-6 space-y-5 md:space-y-6">
                        {{-- Title --}}
                        <div>
                            <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">عنوان</label>
                            <input
                                x-model="form.title"
                                type="text"
                                :disabled="!canEdit"
                                class="w-full text-base font-bold text-[#1A1D21] border-b-2 border-[#E2E8F0] pb-2 focus:outline-none focus:border-[#0069FF] transition-colors bg-transparent placeholder:text-[#CBD5E1]"
                                placeholder="عنوان وظیفه..."
                            >
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">توضیحات</label>
                            <div x-show="!editingDescription" @click="if (canEdit) editingDescription = true" class="min-h-[32px]" :class="[(canEdit ? 'cursor-pointer' : 'cursor-default'), form.description ? 'text-sm text-[#475569] leading-relaxed whitespace-pre-wrap' : 'text-sm text-[#CBD5E1]']" x-html="form.description ? formatMentionText(form.description) : 'توضیحی ثبت نشده'"></div>
                            <div x-show="editingDescription" x-transition class="relative">
                                <textarea
                                    x-model="form.description"
                                    x-init="$nextTick(() => $el.focus())"
                                    rows="4"
                                    class="w-full text-sm text-[#1A1D21] border-2 border-[#0069FF] rounded-lg px-3 py-2 focus:outline-none transition-colors resize-none leading-relaxed"
                                    placeholder="توضیحات وظیفه را بنویسید..."
                                    @input="handleMentionInput('description', $event)"
                                    @keydown.down.prevent="moveMentionSelection(1)"
                                    @keydown.up.prevent="moveMentionSelection(-1)"
                                    @keydown.enter="if (mentionOpen) { $event.preventDefault(); selectActiveMention() }"
                                    @keydown.escape="mentionOpen ? closeMentionMenu() : editingDescription = false"
                                ></textarea>
                                <div x-show="mentionOpen && mentionField === 'description'" class="absolute top-full right-0 left-0 mt-1 bg-white border border-[#D8E0EB] rounded-xl shadow-xl z-30 overflow-hidden">
                                    <template x-for="(person, index) in mentionResults" :key="person.id">
                                        <button @click="selectMention(person)" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-right" :class="mentionIndex === index ? 'bg-[#EAF1FF]' : 'hover:bg-[#F8FAFC]'">
                                            <span class="w-7 h-7 rounded-full bg-[#071B33] text-white flex items-center justify-center text-[9px] font-bold" x-text="person.name.charAt(0)"></span>
                                            <span class="text-[11px] font-bold text-[#172B4D]" x-text="person.name"></span>
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[10px] text-[#94A3B8] mt-1.5">برای اشاره به هم‌تیمی‌ها @ تایپ کنید.</p>
                                <div class="flex gap-2 mt-2">
                                    <button @click="editingDescription = false" class="text-[11px] font-bold text-white bg-[#0069FF] hover:bg-[#0055CC] px-3 py-1.5 rounded-lg transition-all">ذخیره</button>
                                    <button @click="editingDescription = false; form.description = ''" class="text-[11px] font-bold text-[#64748B] hover:text-[#1A1D21] px-3 py-1.5 rounded-lg transition-all">لغو</button>
                                </div>
                            </div>
                        </div>

                        {{-- Checklist --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[10px] font-bold text-[#94A3B8] uppercase tracking-widest">چک‌لیست</label>
                                <span class="text-[10px] font-bold text-[#64748B]" x-text="checklistProgress()"></span>
                            </div>
                            <div class="checklist-bar mb-3">
                                <div class="checklist-bar-fill" :style="'width:' + checklistPercent() + '%'"></div>
                            </div>
                            <div class="space-y-1.5">
                                <template x-for="(item, idx) in form.checklist" :key="idx">
                                    <div class="check-item flex items-center gap-2.5 py-1.5 px-2 rounded-lg hover:bg-[#F8FAFC] group/item transition-colors">
                                        <label class="flex items-center gap-2.5 cursor-pointer flex-1">
                                            <input type="checkbox" x-model="item.done" :disabled="!canEdit" class="w-4 h-4 rounded border-2 border-[#CBD5E1] text-[#0069FF] focus:ring-[#0069FF]/20 cursor-pointer accent-[#0069FF] disabled:cursor-default">
                                            <span class="text-sm text-[#1A1D21] transition-all" x-text="item.text"></span>
                                        </label>
                                        @if ($canEdit)
                                            <button @click="removeCheckItem(idx)" class="opacity-0 group-hover/item:opacity-100 text-[#94A3B8] hover:text-red-500 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </template>
                            </div>
                            @if ($canEdit)
                            <div class="mt-2">
                                <input
                                    x-model="newCheckItem"
                                    @keydown.enter="addCheckItem()"
                                    type="text"
                                    class="w-full text-sm border border-[#E2E8F0] rounded-lg px-3 py-2 focus:outline-none focus:border-[#0069FF] transition-colors placeholder:text-[#CBD5E1]"
                                    placeholder="افزودن آیتم..."
                                >
                            </div>
                            @endif
                        </div>

                        {{-- Conversation / Activity --}}
                        <div>
                            <label class="block text-[10px] font-bold text-[#94A3B8] mb-3 uppercase tracking-widest">گفتگو</label>
                            <div class="space-y-3">
                                <template x-for="(comment, idx) in form.comments" :key="idx">
                                    <div class="flex gap-3">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shrink-0 shadow-sm">
                                            <span class="text-[9px] text-white font-bold" x-text="comment.author.charAt(0)"></span>
                                        </div>
                                        <div class="flex-1 bg-[#F8FAFC] rounded-xl px-3.5 py-2.5 border border-[#F1F5F9]">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="text-[11px] font-bold text-[#1A1D21]" x-text="comment.author"></span>
                                                <span class="text-[9px] text-[#94A3B8]" x-text="comment.time"></span>
                                            </div>
                                            <p class="text-[12px] text-[#475569] leading-relaxed" x-html="formatMentionText(comment.text)"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            @if ($canEdit)
                            <div class="mt-3 flex gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shrink-0 shadow-sm">
                                    <span class="text-[9px] text-white font-bold">ش</span>
                                </div>
                                <div class="flex-1 relative">
                                    <textarea
                                        x-model="newComment"
                                        rows="2"
                                        class="w-full text-sm border-2 border-[#E2E8F0] rounded-xl px-3 py-2 focus:outline-none focus:border-[#0069FF] transition-colors resize-none placeholder:text-[#CBD5E1]"
                                        placeholder="پیام بنویسید..."
                                        @input="handleMentionInput('comment', $event)"
                                        @keydown.down.prevent="moveMentionSelection(1)"
                                        @keydown.up.prevent="moveMentionSelection(-1)"
                                        @keydown.enter="if (mentionOpen) { $event.preventDefault(); selectActiveMention() }"
                                        @keydown.escape="closeMentionMenu()"
                                        @keydown.meta.enter="addComment()"
                                        @keydown.ctrl.enter="addComment()"
                                    ></textarea>
                                    <div x-show="mentionOpen && mentionField === 'comment'" class="absolute bottom-full right-0 left-0 mb-1 bg-white border border-[#D8E0EB] rounded-xl shadow-xl z-30 overflow-hidden">
                                        <template x-for="(person, index) in mentionResults" :key="person.id">
                                            <button @click="selectMention(person)" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-right" :class="mentionIndex === index ? 'bg-[#EAF1FF]' : 'hover:bg-[#F8FAFC]'">
                                                <span class="w-7 h-7 rounded-full bg-[#071B33] text-white flex items-center justify-center text-[9px] font-bold" x-text="person.name.charAt(0)"></span>
                                                <span class="text-[11px] font-bold text-[#172B4D]" x-text="person.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-[#94A3B8] mt-1">برای اشاره به هم‌تیمی‌ها @ تایپ کنید.</p>
                                    <div class="flex justify-end mt-1.5" x-show="newComment.trim()">
                                        <button @click="addComment()" class="text-[10px] font-bold text-white bg-[#0069FF] hover:bg-[#0055CC] px-3 py-1 rounded-lg transition-all">ارسال (Ctrl+Enter)</button>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Sidebar --}}
                    <div class="w-full md:w-[240px] shrink-0 bg-[#F8FAFC] md:border-r border-t md:border-t-0 border-[#F1F5F9] p-4 space-y-4">
                        {{-- Column --}}
                        <div>
                            <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">ستون</label>
                            <select x-model="form.columnId" :disabled="!canEdit" class="w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#0069FF] transition-colors bg-white disabled:bg-[#F1F5F9]">
                                <template x-for="col in columns" :key="col.id">
                                    <option :value="col.id" x-text="col.title"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">اولویت</label>
                            <div class="flex flex-col gap-1">
                                <template x-for="p in [{name:'بالا', color:'bg-red-500'}, {name:'متوسط', color:'bg-violet-500'}, {name:'پایین', color:'bg-slate-400'}]" :key="p.name">
                                    <label class="flex items-center gap-2 text-[11px] cursor-pointer px-2.5 py-1.5 rounded-lg border transition-all duration-150" :class="form.priority === p.name ? 'border-[#0069FF] bg-[#E8F0FE]' : 'border-transparent hover:bg-white'">
                                        <input type="radio" :value="p.name" x-model="form.priority" :disabled="!canEdit" class="hidden">
                                        <span class="w-2 h-2 rounded-full" :class="p.color"></span>
                                        <span class="font-semibold" :class="form.priority === p.name ? 'text-[#003B8E]' : 'text-[#64748B]'" x-text="p.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Due Date --}}
                        <div>
                            <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">سررسید</label>
                            <input x-model="form.dueDate" type="date" :disabled="!canEdit" class="w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#0069FF] transition-colors bg-white disabled:bg-[#F1F5F9]">
                            <p x-show="form.dueDate && isOverdue(form.dueDate)" class="text-[10px] text-red-500 font-bold mt-1">سررسید گذشته</p>
                        </div>

                        {{-- Multi-User Assignee --}}
                        <div x-data="{ assigneeOpen: false, assigneeSearch: '' }" @click.away="assigneeOpen = false" class="relative">
                            <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">مسئولین</label>

                            {{-- Selected chips + trigger --}}
                            <div
                                @click="if (canEdit) assigneeOpen = !assigneeOpen"
                                class="w-full min-h-[36px] border-2 border-[#E2E8F0] rounded-lg px-2.5 py-1.5 transition-colors bg-white flex flex-wrap items-center gap-1"
                                :class="canEdit ? 'cursor-pointer hover:border-[#CBD5E1]' : 'cursor-default bg-[#F1F5F9]'"
                            >
                                <template x-for="name in form.assignees" :key="name">
                                    <span class="inline-flex items-center gap-1 bg-[#E8F0FE] text-[#003B8E] text-[10px] font-bold px-2 py-0.5 rounded-md">
                                        <span x-text="name"></span>
                                        @if ($canEdit)
                                            <button @click.stop="removeAssignee(name)" class="hover:text-red-500 ml-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </span>
                                </template>
                                <span x-show="form.assignees.length === 0" class="text-xs text-[#CBD5E1]">انتخاب کنید...</span>
                                <svg class="w-3.5 h-3.5 text-[#94A3B8] mr-auto shrink-0" :class="assigneeOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>

                            {{-- Dropdown --}}
                            <div
                                x-show="assigneeOpen"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="absolute top-full left-0 right-0 mt-1 bg-white border-2 border-[#E2E8F0] rounded-xl shadow-lg shadow-black/10 z-10 overflow-hidden"
                            >
                                {{-- Search --}}
                                <div class="p-2 border-b border-[#F1F5F9]">
                                    <div class="relative">
                                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <input
                                            x-model="assigneeSearch"
                                            @keydown.escape="assigneeOpen = false"
                                            type="text"
                                            class="w-full text-xs border border-[#E2E8F0] rounded-lg pr-7 pl-2 py-1.5 focus:outline-none focus:border-[#0069FF] transition-colors placeholder:text-[#CBD5E1]"
                                            placeholder="جستجو..."
                                            x-init="$nextTick(() => $el.focus())"
                                        >
                                    </div>
                                </div>

                                {{-- Select all --}}
                                <button
                                    @click="toggleAllAssignees()"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-[11px] font-semibold hover:bg-[#F8FAFC] transition-colors border-b border-[#F1F5F9]"
                                    :class="form.assignees.length === assignees.length ? 'text-[#0069FF]' : 'text-[#64748B]'"
                                >
                                    <div class="w-4 h-4 rounded border-2 flex items-center justify-center transition-colors" :class="form.assignees.length === assignees.length ? 'border-[#0069FF] bg-[#0069FF]' : 'border-[#CBD5E1]'">
                                        <svg x-show="form.assignees.length === assignees.length" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span x-text="form.assignees.length === assignees.length ? 'حذف همه' : 'انتخاب همه'"></span>
                                </button>

                                {{-- User list --}}
                                <div class="max-h-[180px] overflow-y-auto">
                                    <template x-for="name in filteredAssignees(assigneeSearch)" :key="name">
                                        <button
                                            @click="toggleAssignee(name)"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 hover:bg-[#F8FAFC] transition-colors"
                                            :class="form.assignees.includes(name) ? 'bg-[#E8F0FE]/50' : ''"
                                        >
                                            <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors" :class="form.assignees.includes(name) ? 'border-[#0069FF] bg-[#0069FF]' : 'border-[#CBD5E1]'">
                                                <svg x-show="form.assignees.includes(name)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shrink-0">
                                                <span class="text-[7px] text-white font-bold" x-text="name.charAt(0)"></span>
                                            </div>
                                            <span class="text-[11px] font-semibold" :class="form.assignees.includes(name) ? 'text-[#003B8E]' : 'text-[#475569]'" x-text="name"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredAssignees(assigneeSearch).length === 0" class="px-3 py-4 text-center">
                                        <p class="text-[11px] text-[#94A3B8]">نتیجه‌ای یافت نشد</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tags --}}
                        <div>
                            <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">برچسب‌ها</label>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="tag in allTags" :key="tag.name">
                                    <button type="button" @click="if (canEdit) toggleTag(tag.name)" :disabled="!canEdit" class="text-[9px] font-bold px-2 py-1 rounded-md border transition-all duration-150" :class="form.tags.includes(tag.name) ? tag.activeClass : tag.inactiveClass" x-text="tag.name"></button>
                                </template>
                            </div>
                        </div>

                        {{-- Separator --}}
                        <div class="border-t border-[#E2E8F0]"></div>

                        {{-- Actions --}}
                        @if ($canEdit)
                        <div class="space-y-2">
                            <button type="button" @click="saveTask()" class="w-full text-[11px] font-bold text-white bg-gradient-to-l from-[#003B8E] to-[#0069FF] hover:from-[#004BAA] hover:to-[#4D99FF] px-4 py-2.5 rounded-xl shadow-md shadow-[#0069FF]/25 hover:shadow-lg transition-all active:scale-[0.97]">
                                <span x-text="editingTask ? 'ذخیره تغییرات' : 'ایجاد وظیفه'"></span>
                            </button>
                            <button x-show="editingTask" type="button" @click="confirmDelete(form.columnId, editingTask); showModal = false" class="w-full flex items-center justify-center gap-1.5 text-[11px] font-semibold text-[#94A3B8] hover:text-red-500 px-4 py-2 rounded-xl border border-[#E2E8F0] hover:border-red-200 hover:bg-red-50 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                حذف وظیفه
                            </button>
                        </div>
                        @else
                            <div class="text-[11px] leading-5 text-[#64748B] bg-white border border-[#E2E8F0] rounded-lg px-3 py-2.5">شما دسترسی مشاهده دارید و نمی‌توانید این وظیفه را تغییر دهید.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Column Modal --}}
    <div x-show="showColumnModal" x-transition class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.escape.window="closeColumnModal()">
        <div class="absolute inset-0 bg-[#18212B]/45 backdrop-blur-sm" @click="closeColumnModal()"></div>
        <form @submit.prevent="addColumn()" class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden" @click.stop>
            <div class="p-6 border-b border-[#F1EFEA]"><h4 class="text-base font-black text-[#18212B]">افزودن ستون</h4><p class="text-xs text-[#64748B] mt-1">یک مرحله جدید برای جریان کار پروژه بسازید.</p></div>
            <div class="p-6"><label class="block text-[11px] font-black text-[#64748B] mb-2">نام ستون</label><input x-ref="columnTitle" x-model="columnFormTitle" type="text" maxlength="100" required class="w-full h-12 rounded-xl border-2 border-[#E5E1D8] px-4 text-sm font-bold text-[#18212B] outline-none focus:border-[#2563EB]" placeholder="مثلاً آماده انتشار"></div>
            <div class="flex gap-2.5 px-6 pb-6"><button type="button" @click="closeColumnModal()" class="flex-1 h-11 rounded-xl border-2 border-[#E5E1D8] text-xs font-bold text-[#64748B]">انصراف</button><button type="submit" class="flex-1 h-11 rounded-xl bg-[#18212B] text-white text-xs font-black hover:bg-[#253342]">افزودن ستون</button></div>
        </form>
    </div>

    {{-- Delete Column Confirmation Modal --}}
    <div x-show="showColumnDeleteModal" x-transition class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.escape.window="showColumnDeleteModal = false">
        <div class="absolute inset-0 bg-[#18212B]/55 backdrop-blur-sm" @click="showColumnDeleteModal = false"></div>
        <div class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden" @click.stop>
            <div class="p-6 text-center"><div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4"><svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 9v3m0 4h.01M5.2 19h13.6c1.5 0 2.4-1.6 1.65-2.9L13.65 4.2a1.9 1.9 0 0 0-3.3 0L3.55 16.1C2.8 17.4 3.7 19 5.2 19Z"/></svg></div><h4 class="text-sm font-black text-[#18212B]">حذف ستون «<span x-text="columnDeleteTarget.title"></span>»؟</h4><p class="text-xs leading-6 text-[#64748B] mt-2 mb-5">این ستون و <span class="font-black text-red-500" x-text="columnDeleteTarget.taskCount"></span> وظیفه داخل آن حذف می‌شوند و قابل بازگشت نیستند.</p><div class="flex gap-2.5"><button @click="showColumnDeleteModal = false" class="flex-1 h-11 rounded-xl border-2 border-[#E5E1D8] text-xs font-bold text-[#64748B]">انصراف</button><button @click="deleteColumn()" class="flex-1 h-11 rounded-xl bg-red-500 hover:bg-red-600 text-white text-xs font-black">حذف ستون</button></div></div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div
        x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        @keydown.escape.window="showDeleteModal = false"
    >
        <div class="absolute inset-0 bg-[#0A1628]/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl shadow-black/20 overflow-hidden" @click.stop>
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-[#1A1D21] mb-1">حذف وظیفه</h4>
                <p class="text-xs text-[#64748B] mb-5">آیا از حذف این وظیفه مطمئن هستید؟ این عمل قابل بازگشت نیست.</p>
                <div class="flex gap-2.5">
                    <button @click="showDeleteModal = false" class="flex-1 text-xs font-semibold text-[#64748B] hover:text-[#1A1D21] px-4 py-2.5 rounded-xl border-2 border-[#E2E8F0] hover:border-[#CBD5E1] transition-all">انصراف</button>
                    <button @click="deleteTask()" class="flex-1 text-xs font-bold text-white bg-red-500 hover:bg-red-600 px-4 py-2.5 rounded-xl shadow-md shadow-red-500/25 transition-all active:scale-[0.97]">حذف</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    <div
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[70]"
    >
        <div class="flex items-center gap-2 bg-[#1A1D21] text-white text-xs font-medium px-4 py-2.5 rounded-xl shadow-lg shadow-black/20">
            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span x-text="toast.message"></span>
        </div>
    </div>

    <script>
        function board() {
            const serverColumns = @json($columnsData);

            const serverMembers = @json($membersData);
            const serverWorkspacePeople = @json($workspacePeopleData);

            return {
                canEdit: @json($canEdit),
                canManageProject: @json($canManageProject),
                mobileActionsOpen: false,
                activeColumnIndex: 0,
                swipeHintVisible: false,
                mobileBoardObserver: null,
                mobileScrollTimer: null,
                boardMediaQuery: null,
                mobileDragActive: false,
                mobileDragDirection: null,
                mobileDragEdgeTimer: null,
                mobileDragLastX: null,
                mobileDragLastY: null,
                mobileDragPointerHandler: null,
                mobileDragTouchHandler: null,
                mobileDragSuppressClickUntil: 0,
                taskMovePending: false,
                showModal: false,
                showDeleteModal: false,
                showColumnModal: false,
                showColumnDeleteModal: false,
                projectDrawerOpen: false,
                projectDrawerTab: 'members',
                projectMemberSearch: '',
                boardSearchQuery: '',
                boardSearchOpen: false,
                filterByAssignee: [],
                activityTab: 'members',
                activitySearch: '',
                activityItems: [],
                activityLoading: false,
                projectMemberSaving: null,
                projectSettingsSaving: false,
                editingTask: null,
                editingDescription: false,
                deleteTarget: { columnId: null, taskId: null },
                columnDeleteTarget: { id: null, title: '', taskCount: 0 },
                columnFormTitle: '',
                toast: { show: false, message: '' },
                newCheckItem: '',
                newComment: '',
                commentPosting: false,
                mentionOpen: false,
                mentionField: null,
                mentionQuery: '',
                mentionResults: [],
                mentionIndex: 0,
                mentionStart: null,
                mentionCursor: null,
                form: { id: '', title: '', description: '', priority: 'متوسط', assignees: [], columnId: '', dueDate: '', tags: [], checklist: [], comments: [] },

                projectMembers: serverMembers,
                workspacePeople: serverWorkspacePeople,
                assignees: serverMembers.map(member => member.name),
                projectForm: {
                    name: @json($project->name),
                    key: @json($project->key),
                    description: @json($project->description ?? ''),
                },

                allTags: [
                    { name: 'طراحی', activeClass: 'border-purple-400 bg-purple-50 text-purple-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-purple-200 hover:text-purple-500' },
                    { name: 'توسعه', activeClass: 'border-blue-400 bg-blue-50 text-blue-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-blue-200 hover:text-blue-500' },
                    { name: 'بک‌اند', activeClass: 'border-amber-400 bg-amber-50 text-amber-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-amber-200 hover:text-amber-500' },
                    { name: 'فرانت‌اند', activeClass: 'border-green-400 bg-green-50 text-green-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-green-200 hover:text-green-500' },
                    { name: 'باگ', activeClass: 'border-red-400 bg-red-50 text-red-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-red-200 hover:text-red-500' },
                    { name: 'بهبود', activeClass: 'border-teal-400 bg-teal-50 text-teal-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-teal-200 hover:text-teal-500' },
                ],

                columns: serverColumns.map(column => ({ ...column, collapsed: false })),
                sortableInstances: [],

                totalTasks() { return this.columns.reduce((sum, col) => sum + col.tasks.length, 0); },

                filteredTasks(column) {
                    return column.tasks.filter(task => {
                        const matchesAssignee = this.filterByAssignee.length === 0 ||
                            (task.assignees || []).some(a => this.filterByAssignee.includes(a));
                        const q = this.boardSearchQuery.trim().toLowerCase();
                        const matchesSearch = !q ||
                            (task.title || '').toLowerCase().includes(q) ||
                            (task.description || '').toLowerCase().includes(q);
                        return matchesAssignee && matchesSearch;
                    });
                },

                highlightText(text, query) {
                    if (!query || !text) return text || '';
                    const q = query.trim();
                    if (!q) return text;
                    const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp('(' + escaped + ')', 'gi');
                    return text.replace(regex, '<mark style="background:#FEF3C7;padding:0 2px;border-radius:3px">$1</mark>');
                },

                toggleAssigneeFilter(name) {
                    const idx = this.filterByAssignee.indexOf(name);
                    if (idx === -1) {
                        this.filterByAssignee.push(name);
                    } else {
                        this.filterByAssignee.splice(idx, 1);
                    }
                },

                isAssigneeFilterActive(name) {
                    return this.filterByAssignee.includes(name);
                },

                clearAssigneeFilter() {
                    this.filterByAssignee = [];
                },

                boardSearchResultCount() {
                    let count = 0;
                    this.columns.forEach(col => { count += this.filteredTasks(col).length; });
                    return count;
                },

                async loadActivity() {
                    if (this.activityLoading) return;
                    this.activityLoading = true;
                    try {
                        const res = await fetch('{{ route("board.activity", [$workspace->slug, $project->slug], false) }}', {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        this.activityItems = await res.json();
                    } catch (e) {
                        this.activityItems = [];
                    } finally {
                        this.activityLoading = false;
                    }
                },

                filteredActivity() {
                    const q = this.activitySearch.trim().toLowerCase();
                    if (!q) return this.activityItems;
                    return this.activityItems.filter(item =>
                        (item.message || '').toLowerCase().includes(q)
                    );
                },

                init() {
                    this.keyboardHandler = event => {
                        const target = event.target;
                        const typing = target && ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName);
                        if ((event.key === '/' && !typing) || ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k')) {
                            event.preventDefault();
                            this.$nextTick(() => this.$refs.boardSearch?.focus());
                        }
                        if (event.key === 'Escape' && !this.showModal && !this.showDeleteModal && !this.showColumnModal && !this.showColumnDeleteModal) this.clearBoardSearch();
                    };
                    window.addEventListener('keydown', this.keyboardHandler);
                    try {
                        this.swipeHintVisible = window.innerWidth < 768 && localStorage.getItem('neova-board-swipe-hint') !== 'dismissed';
                    } catch (error) {
                        this.swipeHintVisible = window.innerWidth < 768;
                    }

                    this.boardMediaQuery = window.matchMedia('(max-width: 767px)');
                    this.boardMediaQuery.addEventListener('change', () => {
                        this.destroySortables();
                        this.$nextTick(() => {
                            this.columns.forEach(column => {
                                this.initSortable(column.id, this.boardMediaQuery.matches ? 'mobile' : 'desktop');
                            });
                            if (this.boardMediaQuery.matches) this.initMobileBoardObserver();
                            else this.destroyMobileBoardObserver();
                        });
                    });

                    this.$nextTick(() => {
                        if (this.boardMediaQuery.matches) this.initMobileBoardObserver();
                    });
                },

                clearBoardSearch() {
                    this.boardSearchQuery = '';
                    this.boardSearchOpen = false;
                },

                toPersianDigits(value) {
                    return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
                },

                dismissSwipeHint() {
                    this.swipeHintVisible = false;
                    try {
                        localStorage.setItem('neova-board-swipe-hint', 'dismissed');
                    } catch (error) {
                        // Storage can be unavailable in private browsing.
                    }
                },

                scrollToColumn(index) {
                    const track = this.$refs.mobileBoardTrack;
                    const target = track?.querySelector(`[data-column-index="${index}"]`);
                    if (!target) return;
                    target.scrollIntoView({
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                        block: 'nearest',
                        inline: 'center',
                    });
                    this.activeColumnIndex = index;
                },

                handleMobileBoardScroll() {
                    window.clearTimeout(this.mobileScrollTimer);
                    this.mobileScrollTimer = window.setTimeout(() => {
                        const track = this.$refs.mobileBoardTrack;
                        if (!track) return;
                        const center = track.getBoundingClientRect().left + (track.clientWidth / 2);
                        let closestIndex = this.activeColumnIndex;
                        let closestDistance = Number.POSITIVE_INFINITY;
                        track.querySelectorAll('[data-column-index]').forEach(column => {
                            const rect = column.getBoundingClientRect();
                            const distance = Math.abs((rect.left + rect.width / 2) - center);
                            if (distance < closestDistance) {
                                closestDistance = distance;
                                closestIndex = Number(column.dataset.columnIndex);
                            }
                        });
                        this.activeColumnIndex = closestIndex;
                        this.dismissSwipeHint();
                    }, 80);
                },

                initMobileBoardObserver() {
                    this.destroyMobileBoardObserver();
                    const track = this.$refs.mobileBoardTrack;
                    if (!track || !window.IntersectionObserver) return;
                    this.mobileBoardObserver = new IntersectionObserver(entries => {
                        const visible = entries
                            .filter(entry => entry.isIntersecting)
                            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
                        if (visible) this.activeColumnIndex = Number(visible.target.dataset.columnIndex);
                    }, { root: track, threshold: [0.55, 0.7, 0.9] });
                    track.querySelectorAll('[data-column-index]').forEach(column => this.mobileBoardObserver.observe(column));
                },

                destroyMobileBoardObserver() {
                    if (this.mobileBoardObserver) this.mobileBoardObserver.disconnect();
                    this.mobileBoardObserver = null;
                },

                canOpenTaskFromCard() {
                    return !this.mobileDragActive && Date.now() > this.mobileDragSuppressClickUntil;
                },

                mobileDragStatusText() {
                    if (this.mobileDragDirection === 'next' && this.activeColumnIndex < this.columns.length - 1) {
                        return 'انتقال به «' + this.columns[this.activeColumnIndex + 1].title + '»';
                    }
                    if (this.mobileDragDirection === 'previous' && this.activeColumnIndex > 0) {
                        return 'انتقال به «' + this.columns[this.activeColumnIndex - 1].title + '»';
                    }
                    if (this.activeColumnIndex === 0 && this.mobileDragDirection === 'previous') {
                        return 'این اولین ستون است';
                    }
                    if (this.activeColumnIndex === this.columns.length - 1 && this.mobileDragDirection === 'next') {
                        return 'این آخرین ستون است';
                    }
                    return 'وظیفه را به لبه صفحه ببرید';
                },

                startMobileDrag() {
                    this.mobileDragActive = true;
                    this.mobileDragSuppressClickUntil = Date.now() + 500;
                    document.body.classList.add('mobile-task-dragging');

                    this.mobileDragPointerHandler = event => this.handleMobileDragPosition(event.clientX, event.clientY);
                    this.mobileDragTouchHandler = event => {
                        const touch = event.touches?.[0] || event.changedTouches?.[0];
                        if (touch) this.handleMobileDragPosition(touch.clientX, touch.clientY);
                    };
                    document.addEventListener('pointermove', this.mobileDragPointerHandler, { passive: true });
                    document.addEventListener('touchmove', this.mobileDragTouchHandler, { passive: true });
                },

                handleMobileDragPosition(clientX, clientY = null) {
                    if (!this.mobileDragActive || !Number.isFinite(clientX)) return;
                    this.mobileDragLastX = clientX;
                    if (Number.isFinite(clientY)) this.mobileDragLastY = clientY;
                    const edgeSize = Math.min(82, Math.max(56, window.innerWidth * 0.18));
                    let direction = null;

                    if (clientX <= edgeSize) direction = 'next';
                    else if (clientX >= window.innerWidth - edgeSize) direction = 'previous';

                    if (direction !== this.mobileDragDirection) {
                        this.clearMobileDragEdgeTimer();
                        this.mobileDragDirection = direction;
                    }

                    if (!direction) return;
                    const targetIndex = direction === 'next'
                        ? this.activeColumnIndex + 1
                        : this.activeColumnIndex - 1;
                    if (targetIndex < 0 || targetIndex >= this.columns.length || this.mobileDragEdgeTimer) return;

                    this.mobileDragEdgeTimer = window.setTimeout(() => {
                        this.mobileDragEdgeTimer = null;
                        this.navigateDuringMobileDrag(targetIndex);
                    }, 360);
                },

                navigateDuringMobileDrag(targetIndex) {
                    if (!this.mobileDragActive) return;
                    const track = this.$refs.mobileBoardTrack;
                    const target = track?.querySelector(`[data-column-index="${targetIndex}"]`);
                    if (!target) return;

                    target.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'center' });
                    this.activeColumnIndex = targetIndex;
                    window.setTimeout(() => {
                        if (this.mobileDragActive && Number.isFinite(this.mobileDragLastX)) {
                            this.handleMobileDragPosition(this.mobileDragLastX, this.mobileDragLastY);
                        }
                    }, 460);
                },

                mobileDropIndex(columnId, clientY) {
                    const list = document.getElementById('col-mobile-' + columnId);
                    if (!list) return 0;
                    const cards = [...list.querySelectorAll(':scope > article[data-id]')];
                    if (!cards.length || !Number.isFinite(clientY)) return cards.length;
                    const index = cards.findIndex(card => {
                        const rect = card.getBoundingClientRect();
                        return clientY < rect.top + (rect.height / 2);
                    });
                    return index === -1 ? cards.length : index;
                },

                clearMobileDragEdgeTimer() {
                    if (this.mobileDragEdgeTimer) window.clearTimeout(this.mobileDragEdgeTimer);
                    this.mobileDragEdgeTimer = null;
                },

                endMobileDrag() {
                    this.clearMobileDragEdgeTimer();
                    if (this.mobileDragPointerHandler) {
                        document.removeEventListener('pointermove', this.mobileDragPointerHandler);
                    }
                    if (this.mobileDragTouchHandler) {
                        document.removeEventListener('touchmove', this.mobileDragTouchHandler);
                    }
                    this.mobileDragPointerHandler = null;
                    this.mobileDragTouchHandler = null;
                    this.mobileDragDirection = null;
                    this.mobileDragLastX = null;
                    this.mobileDragLastY = null;
                    this.mobileDragActive = false;
                    this.mobileDragSuppressClickUntil = Date.now() + 350;
                    document.body.classList.remove('mobile-task-dragging');
                },

                openProjectDrawer() {
                    if (!this.canManageProject) return;
                    this.projectDrawerOpen = true;
                    this.projectDrawerTab = 'members';
                    document.body.classList.add('modal-open');
                },

                closeProjectDrawer() {
                    this.projectDrawerOpen = false;
                    document.body.classList.remove('modal-open');
                },

                isProjectMember(userId) {
                    return this.projectMembers.some(member => member.id === userId);
                },

                filteredWorkspacePeople() {
                    const query = this.projectMemberSearch.trim().toLowerCase();
                    if (!query) return this.workspacePeople;
                    return this.workspacePeople.filter(person =>
                        person.name.toLowerCase().includes(query) || (person.phone || '').includes(query)
                    );
                },

                async toggleProjectMember(person) {
                    if (!this.canManageProject || this.projectMemberSaving) return;
                    this.projectMemberSaving = person.id;
                    const selected = this.isProjectMember(person.id);
                    const url = selected
                        ? '{{ route("board.project.members.destroy", [$workspace->slug, $project->slug, "__USER__"], false) }}'.replace('__USER__', person.id)
                        : '{{ route("board.project.members.store", [$workspace->slug, $project->slug], false) }}';
                    try {
                        const response = await fetch(url, {
                            method: selected ? 'DELETE' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: selected ? null : JSON.stringify({ user_id: person.id }),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'ذخیره تغییرات انجام نشد.');
                        if (selected) {
                            this.projectMembers = this.projectMembers.filter(member => member.id !== person.id);
                            this.form.assignees = this.form.assignees.filter(name => name !== person.name);
                            this.columns.forEach(column => column.tasks.forEach(task => {
                                task.assignees = (task.assignees || []).filter(name => name !== person.name);
                            }));
                        } else {
                            this.projectMembers.push(data.member);
                        }
                        this.assignees = this.projectMembers.map(member => member.name);
                        this.showToast(data.message);
                    } catch (error) {
                        this.showToast(error.message);
                    } finally {
                        this.projectMemberSaving = null;
                    }
                },

                async saveProjectSettings() {
                    if (!this.canManageProject || this.projectSettingsSaving) return;
                    this.projectSettingsSaving = true;
                    try {
                        const response = await fetch('{{ route("board.project.update", [$workspace->slug, $project->slug], false) }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(this.projectForm),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'ذخیره تنظیمات انجام نشد.');
                        this.projectForm = { ...this.projectForm, ...data.project };
                        this.showToast(data.message);
                    } catch (error) {
                        this.showToast(error.message);
                    } finally {
                        this.projectSettingsSaving = false;
                    }
                },

                mentionableMembers() {
                    return this.projectMembers;
                },

                handleMentionInput(field, event) {
                    const value = field === 'description' ? this.form.description : this.newComment;
                    const cursor = event.target.selectionStart;
                    const beforeCursor = value.slice(0, cursor);
                    const match = beforeCursor.match(/(?:^|\s)@([^\s@]*)$/u);
                    if (!match) {
                        this.closeMentionMenu();
                        return;
                    }
                    this.mentionField = field;
                    this.mentionQuery = match[1] || '';
                    this.mentionCursor = cursor;
                    this.mentionStart = cursor - this.mentionQuery.length - 1;
                    const query = this.mentionQuery.toLowerCase();
                    this.mentionResults = this.mentionableMembers()
                        .filter(person => person.name.toLowerCase().includes(query))
                        .slice(0, 6);
                    this.mentionIndex = 0;
                    this.mentionOpen = this.mentionResults.length > 0;
                },

                moveMentionSelection(direction) {
                    if (!this.mentionOpen || !this.mentionResults.length) return;
                    this.mentionIndex = (this.mentionIndex + direction + this.mentionResults.length) % this.mentionResults.length;
                },

                selectActiveMention() {
                    const person = this.mentionResults[this.mentionIndex];
                    if (person) this.selectMention(person);
                },

                selectMention(person) {
                    const field = this.mentionField;
                    const value = field === 'description' ? this.form.description : this.newComment;
                    const token = `@[${person.name}](user:${person.id}) `;
                    const nextValue = value.slice(0, this.mentionStart) + token + value.slice(this.mentionCursor);
                    if (field === 'description') this.form.description = nextValue;
                    else this.newComment = nextValue;
                    this.closeMentionMenu();
                },

                closeMentionMenu() {
                    this.mentionOpen = false;
                    this.mentionField = null;
                    this.mentionResults = [];
                    this.mentionIndex = 0;
                },

                mentionIds(text) {
                    return [...text.matchAll(/@\[[^\]]+\]\(user:(\d+)\)/gu)].map(match => Number(match[1]));
                },

                formatMentionText(text) {
                    const escaped = String(text)
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                    return escaped
                        .replace(/@\[([^\]]+)\]\(user:\d+\)/gu, '<span class="inline-flex bg-[#EAF1FF] text-[#1668FF] font-bold rounded px-1.5 py-0.5">@$1</span>')
                        .replaceAll('\n', '<br>');
                },

                getTagClass(tagName) {
                    const tag = this.allTags.find(t => t.name === tagName);
                    if (!tag) return 'bg-[#F1F5F9] text-[#94A3B8]';
                    return tag.activeClass.split(' ').filter(c => c.startsWith('bg-') || c.startsWith('text-')).join(' ');
                },

                toggleTag(tagName) {
                    const idx = this.form.tags.indexOf(tagName);
                    if (idx > -1) this.form.tags.splice(idx, 1);
                    else this.form.tags.push(tagName);
                },

                toggleAssignee(name) {
                    const idx = this.form.assignees.indexOf(name);
                    if (idx > -1) this.form.assignees.splice(idx, 1);
                    else this.form.assignees.push(name);
                },

                removeAssignee(name) {
                    this.form.assignees = this.form.assignees.filter(n => n !== name);
                },

                toggleAllAssignees() {
                    if (this.form.assignees.length === this.assignees.length) {
                        this.form.assignees = [];
                    } else {
                        this.form.assignees = [...this.assignees];
                    }
                },

                filteredAssignees(search) {
                    if (!search.trim()) return this.assignees;
                    const s = search.trim().toLowerCase();
                    return this.assignees.filter(n => n.toLowerCase().includes(s));
                },

                checklistProgress() {
                    const total = this.form.checklist.length;
                    if (total === 0) return '0/0';
                    const done = this.form.checklist.filter(i => i.done).length;
                    return done + '/' + total;
                },

                checklistPercent() {
                    const total = this.form.checklist.length;
                    if (total === 0) return 0;
                    return Math.round((this.form.checklist.filter(i => i.done).length / total) * 100);
                },

                addCheckItem() {
                    if (!this.newCheckItem.trim()) return;
                    this.form.checklist.push({ text: this.newCheckItem.trim(), done: false });
                    this.newCheckItem = '';
                },

                removeCheckItem(idx) {
                    this.form.checklist.splice(idx, 1);
                },

                async addComment() {
                    if (!this.newComment.trim() || this.commentPosting) return;
                    const pendingComment = {
                        author: '{{ auth()->user()->full_name }}',
                        author_id: {{ auth()->id() }},
                        text: this.newComment.trim(),
                        mention_ids: this.mentionIds(this.newComment),
                        time: 'همین الان',
                    };
                    if (!this.editingTask) {
                        this.form.comments.push(pendingComment);
                        this.newComment = '';
                        this.closeMentionMenu();
                        return;
                    }
                    this.commentPosting = true;
                    try {
                        const response = await fetch('{{ route("board.task.comments.store", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', this.editingTask), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                text: pendingComment.text,
                                mention_ids: pendingComment.mention_ids,
                            }),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'ارسال پیام انجام نشد.');
                        this.form.comments.push(data.comment);
                        const task = this.columns.flatMap(column => column.tasks).find(item => item.dbId === this.editingTask);
                        if (task) task.comments = JSON.parse(JSON.stringify(this.form.comments));
                        this.showToast('پیام ارسال شد.');
                    } catch (error) {
                        this.showToast(error.message);
                    } finally {
                        this.commentPosting = false;
                    }
                    this.newComment = '';
                    this.closeMentionMenu();
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    const months = ['ژانویه','فوریه','مارس','آوریل','مه','ژوئن','ژوئیه','اوت','سپتامبر','اکتبر','نوامبر','دسامبر'];
                    return d.getDate() + ' ' + months[d.getMonth()];
                },

                isOverdue(dateStr) {
                    if (!dateStr) return false;
                    return new Date(dateStr) < new Date();
                },

                initSortable(columnId, variant = 'desktop') {
                    if (!this.canEdit) return;
                    const isMobile = window.matchMedia('(max-width: 767px)').matches;
                    if ((variant === 'mobile') !== isMobile) return;
                    if (this.sortableInstances.some(item => item.columnId === columnId && item.variant === variant)) return;
                    const el = document.getElementById(`col-${variant}-${columnId}`);
                    if (!el) return;
                    const self = this;
                    const instance = new Sortable(el, {
                        group: variant === 'mobile' ? false : 'tasks',
                        animation: 200,
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        direction: 'vertical',
                        draggable: '[data-id]',
                        handle: variant === 'mobile' ? '.task-drag-handle' : undefined,
                        delay: variant === 'mobile' ? 120 : 50,
                        delayOnTouchOnly: true,
                        touchStartThreshold: 4,
                        forceFallback: variant === 'mobile',
                        fallbackOnBody: variant === 'mobile',
                        fallbackTolerance: variant === 'mobile' ? 4 : 0,
                        scroll: variant !== 'mobile',
                        bubbleScroll: variant !== 'mobile',
                        scrollSensitivity: 72,
                        scrollSpeed: 14,
                        onClone(evt) {
                            if (variant === 'mobile') evt.clone.setAttribute('x-ignore', '');
                        },
                        onStart(evt) {
                            if (variant === 'mobile') {
                                window.Sortable.ghost?.setAttribute('x-ignore', '');
                                const sourceIndex = self.columns.findIndex(column => column.id === evt.from.id.replace('col-mobile-', ''));
                                if (sourceIndex >= 0) self.activeColumnIndex = sourceIndex;
                                self.startMobileDrag();
                            }
                        },
                        onMove(evt, originalEvent) {
                            if (variant === 'mobile' && originalEvent) {
                                const touch = originalEvent.touches?.[0] || originalEvent.changedTouches?.[0];
                                self.handleMobileDragPosition(
                                    touch?.clientX ?? originalEvent.clientX,
                                    touch?.clientY ?? originalEvent.clientY,
                                );
                            }
                            return true;
                        },
                        onEnd(evt) {
                            const taskId = Number(evt.item.getAttribute('data-id'));
                            const fromColId = evt.from.id.replace(`col-${variant}-`, '');
                            let toColId = evt.to.id.replace(`col-${variant}-`, '');
                            let newIndex = evt.newIndex;
                            if (variant === 'mobile') {
                                toColId = self.columns[self.activeColumnIndex]?.id || fromColId;
                                if (toColId !== fromColId) {
                                    newIndex = self.mobileDropIndex(toColId, self.mobileDragLastY);
                                }
                                self.endMobileDrag();
                            }
                            self.moveTask(fromColId, toColId, taskId, newIndex, evt.oldIndex);
                        }
                    });
                    this.sortableInstances.push({ instance, columnId, variant });
                },

                destroySortables() {
                    this.sortableInstances.forEach(item => item.instance.destroy());
                    this.sortableInstances = [];
                },

                setSortablesDisabled(disabled) {
                    this.sortableInstances.forEach(item => item.instance.option('disabled', disabled));
                },

                async moveTask(fromColId, toColId, taskId, newIndex, oldIndex = null) {
                    if (!this.canEdit || this.taskMovePending) return;
                    const fromCol = this.columns.find(c => c.id === fromColId);
                    const toCol = this.columns.find(c => c.id === toColId);
                    if (!fromCol || !toCol) return;
                    const idx = fromCol.tasks.findIndex(t => t.dbId === taskId);
                    if (idx === -1) return;
                    if (fromColId === toColId && Number(oldIndex) === Number(newIndex)) return;

                    const snapshot = this.columns.map(column => ({
                        id: column.id,
                        tasks: [...column.tasks],
                    }));
                    const [task] = fromCol.tasks.splice(idx, 1);
                    const safeIndex = Math.max(0, Math.min(Number(newIndex) || 0, toCol.tasks.length));
                    toCol.tasks.splice(safeIndex, 0, task);
                    this.activeColumnIndex = this.columns.findIndex(column => column.id === toColId);
                    this.taskMovePending = true;
                    this.setSortablesDisabled(true);

                    try {
                        const response = await fetch('{{ route("board.task.move", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', task.dbId), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ column_id: parseInt(toColId), position: safeIndex }),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'انتقال وظیفه انجام نشد.');
                        this.showToast(fromColId === toColId ? 'ترتیب وظیفه ذخیره شد' : 'وظیفه به ستون جدید منتقل شد');
                    } catch (error) {
                        snapshot.forEach(savedColumn => {
                            const column = this.columns.find(item => item.id === savedColumn.id);
                            if (column) column.tasks = [...savedColumn.tasks];
                        });
                        this.showToast(error.message || 'انتقال وظیفه انجام نشد.');
                        this.destroySortables();
                        this.$nextTick(() => {
                            this.columns.forEach(column => {
                                this.initSortable(column.id, this.boardMediaQuery?.matches ? 'mobile' : 'desktop');
                            });
                        });
                    } finally {
                        this.taskMovePending = false;
                        this.setSortablesDisabled(false);
                    }
                },

                openAddModal(columnId) {
                    if (!this.canEdit) return;
                    this.editingTask = null;
                    this.editingDescription = false;
                    this.form = { id: '', title: '', description: '', priority: 'متوسط', assignees: [], columnId: columnId || this.columns[0]?.id, dueDate: '', tags: [], checklist: [], comments: [] };
                    this.newCheckItem = '';
                    this.newComment = '';
                    this.showModal = true;
                },

                openEditModal(task, columnId) {
                    this.editingTask = task.dbId;
                    this.editingDescription = false;
                    const taskAssignees = task.assignees || (task.assignee ? [task.assignee] : []);
                    this.form = {
                        id: task.id, title: task.title, description: task.description || '', priority: task.priority,
                        assignees: [...taskAssignees], columnId: columnId, dueDate: task.dueDate || '',
                        tags: [...(task.tags || [])], checklist: JSON.parse(JSON.stringify(task.checklist || [])),
                        comments: JSON.parse(JSON.stringify(task.comments || []))
                    };
                    this.newCheckItem = '';
                    this.newComment = '';
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.editingDescription = false;
                },

                async saveTask() {
                    if (!this.canEdit) return;
                    if (!this.form.title.trim()) return;
                    const token = '{{ csrf_token() }}';
                    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' };

                    if (this.editingTask) {
                        const sourceCol = this.columns.find(c => c.tasks.some(t => t.dbId === this.editingTask));
                        const targetCol = this.columns.find(c => c.id === this.form.columnId);
                        const task = sourceCol?.tasks.find(t => t.dbId === this.editingTask);
                        if (task) {
                            const payload = { title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: this.form.assignees, due_date: this.form.dueDate, tags: this.form.tags, checklist: this.form.checklist, comments: this.form.comments, column_id: parseInt(this.form.columnId) };
                            await fetch('{{ route("board.task.update", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', task.dbId), { method: 'PUT', headers, body: JSON.stringify(payload) });
                            Object.assign(task, { title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: [...this.form.assignees], dueDate: this.form.dueDate, tags: [...this.form.tags], checklist: JSON.parse(JSON.stringify(this.form.checklist)), comments: JSON.parse(JSON.stringify(this.form.comments)) });
                            if (sourceCol && targetCol && sourceCol.id !== targetCol.id) {
                                sourceCol.tasks = sourceCol.tasks.filter(item => item.dbId !== task.dbId);
                                targetCol.tasks.push(task);
                                this.activeColumnIndex = this.columns.findIndex(column => column.id === targetCol.id);
                                this.$nextTick(() => this.scrollToColumn(this.activeColumnIndex));
                            }
                        }
                        this.showToast('تغییرات ذخیره شد');
                    } else {
                        const col = this.columns.find(c => c.id === this.form.columnId);
                        if (col) {
                            const payload = { column_id: parseInt(this.form.columnId), title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: this.form.assignees, due_date: this.form.dueDate, tags: this.form.tags, checklist: this.form.checklist, comments: this.form.comments };
                            const res = await fetch('{{ route("board.task.store", [$workspace->slug, $project->slug], false) }}', { method: 'POST', headers, body: JSON.stringify(payload) });
                            const data = await res.json();
                            col.tasks.push({ id: data.display_id, dbId: data.id, title: data.title, description: data.description || '', priority: data.priority, assignees: data.assignees || [], dueDate: data.due_date || '', tags: data.tags || [], checklist: data.checklist || [], comments: data.comments || [] });
                            this.showToast('وظیفه جدید ایجاد شد');
                        }
                    }
                    this.showModal = false;
                },

                openColumnModal() {
                    if (!this.canEdit) return;
                    this.columnFormTitle = '';
                    this.showColumnModal = true;
                    this.$nextTick(() => this.$refs.columnTitle?.focus());
                },

                closeColumnModal() {
                    this.showColumnModal = false;
                    this.columnFormTitle = '';
                },

                async addColumn() {
                    if (!this.canEdit || !this.columnFormTitle.trim()) return;
                    const response = await fetch('{{ route("board.column.store", [$workspace->slug, $project->slug], false) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ project_id: {{ $project->id }}, title: this.columnFormTitle.trim() }),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) { this.showToast(data.message || 'افزودن ستون انجام نشد.'); return; }
                    this.columns.push({ id: String(data.id), title: data.title, dotColor: 'bg-[#94A3B8]', badgeClass: 'bg-[#F1F5F9] text-[#64748B]', tasks: [], collapsed: false });
                    this.closeColumnModal();
                    this.showToast('ستون جدید اضافه شد');
                    this.$nextTick(() => this.initSortable(String(data.id), 'desktop'));
                },

                confirmDeleteColumn(column) {
                    if (!this.canEdit || this.columns.length <= 1) return;
                    this.columnDeleteTarget = { id: column.id, title: column.title, taskCount: column.tasks.length };
                    this.showColumnDeleteModal = true;
                },

                async deleteColumn() {
                    if (!this.canEdit || !this.columnDeleteTarget.id || this.columns.length <= 1) return;
                    const columnId = this.columnDeleteTarget.id;
                    const response = await fetch('{{ route("board.column.destroy", [$workspace->slug, $project->slug, "__COLUMN__"], false) }}'.replace('__COLUMN__', columnId), {
                        method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) { this.showToast(data.message || 'حذف ستون انجام نشد.'); return; }
                    this.columns = this.columns.filter(column => column.id !== columnId);
                    this.activeColumnIndex = Math.max(0, Math.min(this.activeColumnIndex, this.columns.length - 1));
                    this.showColumnDeleteModal = false;
                    this.columnDeleteTarget = { id: null, title: '', taskCount: 0 };
                    this.destroySortables();
                    this.$nextTick(() => this.columns.forEach(column => this.initSortable(column.id, this.boardMediaQuery?.matches ? 'mobile' : 'desktop')));
                    this.showToast('ستون حذف شد');
                },

                confirmDelete(columnId, taskId) {
                    if (!this.canEdit) return;
                    this.deleteTarget = { columnId, taskId };
                    this.showDeleteModal = true;
                },

                async deleteTask() {
                    if (!this.canEdit) return;
                    const col = this.columns.find(c => c.id === this.deleteTarget.columnId);
                    if (col) {
                        const task = col.tasks.find(t => t.dbId === this.deleteTarget.taskId);
                        if (task) {
                            await fetch('{{ route("board.task.destroy", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', task.dbId), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                        }
                        col.tasks = col.tasks.filter(t => t.dbId !== this.deleteTarget.taskId);
                        this.showToast('وظیفه حذف شد');
                    }
                    this.showDeleteModal = false;
                    this.deleteTarget = { columnId: null, taskId: null };
                },

                showToast(message) {
                    this.toast = { show: true, message };
                    setTimeout(() => { this.toast.show = false; }, 2500);
                }
            };
        }
    </script>
</body>
</html>
