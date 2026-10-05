<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تخته اسکرام</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/png/app-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body.modal-open { overflow: hidden !important; }
        .sortable-ghost { opacity: 0.4; background: #F0F0ED !important; border: 2px dashed #111111 !important; box-shadow: none !important; }
        .sortable-chosen { box-shadow: 0 4px 12px rgba(24,33,43,0.09) !important; transform: translateY(-1px); z-index: 50; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        .checklist-bar { height: 6px; border-radius: 3px; background: #E2E8F0; overflow: hidden; }
        .checklist-bar-fill { height: 100%; border-radius: 3px; background: #111111; transition: width 0.3s ease; }
        .neova-board .bg-white { background-color: #FFFFFF !important; }
        .neova-board .text-\[\#1A1D21\], .neova-board .text-\[\#18212B\] { color: #111111 !important; }
        .neova-board .text-\[\#18212B\], .neova-board .text-\[\#18212B\] { color: #111111 !important; }
        .neova-board .bg-\[\#18212B\], .neova-board .bg-\[\#18212B\] { background-color: #111111 !important; }
        .neova-board .bg-\[\#F1F3F2\], .neova-board .bg-\[\#F1F3F2\] { background-color: #F0F0ED !important; }
        .neova-board .hover\:bg-\[\#253342\]:hover,
        .neova-board .hover\:bg-\[\#000000\]:hover,
        .neova-board .hover\:bg-\[\#000000\]:hover { background-color: #000000 !important; }
        .neova-board .border-\[\#D7DDDA\] { border-color: #D7DDDA !important; }
        .neova-board .focus\:border-\[\#18212B\]:focus { border-color: #111111 !important; }
        .neova-board .focus\:ring-\[\#18212B\]\/20:focus { --tw-ring-color: rgb(0 105 217 / 0.2) !important; }
        .neova-board {
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: #F7F7F5;
            color: #111111;
        }
        .neova-board .border-\[\#E2E8F0\], .neova-board .border-\[\#E8EBE9\] { border-color: #E8EBE9 !important; }
        .neova-board .text-\[\#64748B\] { color: #66717A !important; }
        .check-item input[type="checkbox"]:checked + span { text-decoration: line-through; color: #94A3B8; }
        .mobile-board-track { scrollbar-width: none; scroll-padding-inline: 1rem; overscroll-behavior-inline: contain; }
        .mobile-board-track::-webkit-scrollbar { display: none; }
        .mobile-column-tabs { scrollbar-width: none; }
        .mobile-column-tabs::-webkit-scrollbar { display: none; }
        .mobile-board-column { scroll-snap-align: center; scroll-snap-stop: always; }
        .mobile-task-list { touch-action: pan-x pan-y; }
        .task-drag-handle { touch-action: none; }
        .column-drag-handle { touch-action: none; }
        .column-drag-handle { opacity: .7; transition: opacity 150ms ease, background-color 150ms ease, color 150ms ease; }
        .column-drag-handle:hover, .column-drag-handle:focus-visible { opacity: 1; }
        .column-order-dragging { scroll-snap-type: none !important; }
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
            background: linear-gradient(to right, rgba(24, 33, 43, .14), transparent);
        }
        .mobile-drag-edge--right {
            right: 0;
            background: linear-gradient(to left, rgba(24, 33, 43, .14), transparent);
        }
        .mobile-drag-edge.is-available { opacity: .55; }
        .mobile-drag-edge.is-active { opacity: 1; }
        @media (prefers-reduced-motion: reduce) {
            .mobile-board-track { scroll-behavior: auto !important; }
            .mobile-drag-edge { transition: none; }
        }
    </style>
</head>
<body
    class="app-page neova-board neova-product board-style-editorial board-direction min-h-screen overflow-x-hidden"
    x-data="board()"
    :class="{ 'board-is-compact': boardCompact }"
    x-cloak
>
    <x-workspace-shell :workspace="$workspace" active="board" board :active-project="$project->slug">

    @slot('context')
        <div class="board-topbar-context" aria-label="پروژه جاری">
            <div class="board-topbar-context__identity">
                <span x-show="projectState.key" class="board-topbar-context__key" x-text="projectState.key"></span>
                <strong :title="projectState.name" x-text="projectState.name"></strong><span x-show="!projectState.isActive" class="text-xs">بایگانی شده</span>
                @if ($project->visibility === 'private')<span class="board-topbar-context__private" title="پروژه خصوصی">خصوصی</span>@endif
            </div>
            <small class="board-topbar-context__workspace">فضای کاری {{ $workspace->name }}</small>
            @if ($canManageProject)
                <button type="button" class="board-topbar-context__manage" @click="openProjectDrawer()" aria-label="مدیریت پروژه" title="مدیریت پروژه">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="3.5"/><path d="M12 2.5v2m0 15v2M2.5 12h2m15 0h2M5.3 5.3l1.5 1.5m10.4 10.4 1.5 1.5m0-13.4-1.5 1.5M6.8 17.2l-1.5 1.5"/></svg>
                    <span class="sr-only">تنظیمات پروژه</span>
                </button>
            @endif
        </div>
    @endslot

    @slot('toolbar')
        <div class="board-topbar-actions">
            <div class="relative board-topbar-filter" @click.away="if (window.matchMedia('(min-width: 768px)').matches) filterPanelOpen = false">
                <button
                    type="button"
                    @click="filterPanelOpen = !filterPanelOpen"
                    class="board-nav-control board-topbar-filter__trigger"
                    :class="activeFilterCount() > 0 ? 'ring-2 ring-[#18212B]/15 border-[#18212B]/30' : ''"
                    :aria-expanded="filterPanelOpen"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 12h12M10 20h4"/></svg>
                    <span class="board-topbar-action-label">فیلتر</span>
                    <span x-show="activeFilterCount() > 0" class="min-w-5 h-5 px-1 rounded-full bg-[#18212B] text-white text-[10px] flex items-center justify-center" x-text="toPersianDigits(activeFilterCount())"></span>
                </button>
                <div x-show="filterPanelOpen" x-transition class="board-desktop-filter-panel absolute left-0 top-full mt-2 w-72 rounded-xl border border-[#E2E8F0] bg-white shadow-sm z-50 p-3 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-[12px] font-black text-[#18212B]">فیلتر تخته</p>
                        <button x-show="activeFilterCount() > 0" type="button" @click="clearAllFilters()" class="text-[11px] font-bold text-red-500">پاک کردن</button>
                    </div>
                    <div>
                        <p class="board-field-label mb-2">مسئول</p>
                        <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                            <template x-for="member in projectMembers" :key="'filter-a-' + member.id">
                                <button type="button" @click="toggleAssigneeFilter(member.name)" class="filter-chip" :class="isAssigneeFilterActive(member.name) ? 'is-active' : ''" x-text="member.name"></button>
                            </template>
                            <p x-show="projectMembers.length === 0" class="text-[11px] text-[#94A3B8]">عضوی در پروژه نیست</p>
                        </div>
                    </div>
                    <div>
                        <p class="board-field-label mb-2">اولویت</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="p in ['بالا', 'متوسط', 'پایین']" :key="'filter-p-' + p">
                                <button type="button" @click="togglePriorityFilter(p)" class="filter-chip" :class="filterByPriority.includes(p) ? 'is-active' : ''" x-text="p"></button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="board-field-label mb-2" for="desktop-due-filter">سررسید</label>
                        <select id="desktop-due-filter" x-model="filterByDue" class="w-full h-9 rounded-lg border border-[#E2E8F0] bg-white px-2 text-[11px]">
                            <option value="">همه سررسیدها</option><option value="overdue">عقب‌افتاده</option><option value="today">امروز</option><option value="next7">۷ روز آینده</option><option value="undated">بدون سررسید</option>
                        </select>
                    </div>
                    <div>
                        <p class="board-field-label mb-2">برچسب</p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="tag in allTags" :key="'filter-t-' + tag.name">
                                <button type="button" @click="toggleTagFilter(tag.name)" class="filter-chip" :class="filterByTag.includes(tag.name) ? 'is-active' : ''" x-text="tag.name"></button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            @if ($canEdit)
                <button type="button" @click="openAddModal(columns[activeColumnIndex]?.id || columns[0]?.id)" class="board-nav-control board-nav-control--primary board-topbar-create" aria-label="ایجاد وظیفه جدید" title="ایجاد وظیفه جدید">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 4v16m8-8H4"/></svg>
                    <span class="board-topbar-action-label">وظیفه جدید</span>
                </button>
            @else
                <span class="board-topbar-readonly">فقط مشاهده</span>
            @endif
        </div>
    @endslot

    <section class="board-overview" aria-labelledby="board-page-title">
        <div class="board-overview__identity">
            <p class="board-overview__eyebrow" x-text="activeCycle ? 'چرخه ' + toPersianDigits(activeCycle.number) + ' · ' + toPersianDigits(cycleDaysRemaining()) + ' روز باقی مانده' : 'جریان کار تیم شما'"></p>
            <h1 id="board-page-title">تخته پروژه</h1>
            <div class="board-overview__meta">
                <span x-text="toPersianDigits(totalTasks()) + ' وظیفه · ' + toPersianDigits(projectMembers.length) + ' نفر'"></span>
                <span aria-hidden="true">·</span>
                <span class="board-sync-status" role="status" aria-live="polite" x-text="syncStatusText()"></span>
                <button x-show="snapshotState !== 'current'" type="button" @click="realtimeRefresher.refreshNow()" class="board-overview__retry">تلاش دوباره</button>
            </div>
        </div>
        <div class="board-overview__controls">
            <div class="board-overview__progress">
                <div><strong x-text="toPersianDigits(boardProgress()) + '٪'"></strong><small x-text="activeCycle ? 'پیشرفت چرخه' : 'پیشرفت پروژه'"></small></div>
                <div class="board-overview__progress-track" role="progressbar" :aria-valuenow="boardProgress()" aria-valuemin="0" aria-valuemax="100" :aria-label="activeCycle ? 'پیشرفت چرخه' : 'پیشرفت پروژه'"><span :style="'width:' + boardProgress() + '%'"></span></div>
                <button type="button" @click="boardCompact = !boardCompact" :aria-pressed="boardCompact" class="board-density-toggle" x-text="boardCompact ? 'نمای کامل' : 'نمای فشرده'"></button>
            </div>
            <label class="board-local-search"><span class="sr-only">جستجو در این تخته</span><input type="search" x-model.debounce.200ms="boardSearchQuery" placeholder="جستجو در این تخته…" aria-label="جستجو در این تخته"></label>
        </div>
    </section>

    <div x-show="selectedTaskIds.length > 0" x-cloak class="board-bulk-bar">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center gap-2 px-3 sm:px-6 py-2.5">
            <strong class="text-[11px] text-[#111111]" x-text="toPersianDigits(selectedTaskIds.length) + ' وظیفه انتخاب شده' "></strong>
            <select x-model="bulkAction" class="h-8 rounded-lg border border-[#DCE8F2] bg-white px-2 text-[10px] font-bold text-[#475569]">
                <option value="">عملیات گروهی</option><option value="priority">تغییر اولویت</option><option value="assignee">تعیین مسئول</option><option value="tag">افزودن برچسب</option><option value="column">انتقال به ستون</option><option value="due_date">پاک کردن سررسید</option>
            </select>
            <select x-show="bulkAction === 'priority'" x-model="bulkValue" class="h-8 rounded-lg border border-[#DCE8F2] bg-white px-2 text-[10px]"><option value="بالا">بالا</option><option value="متوسط">متوسط</option><option value="پایین">پایین</option></select>
            <select x-show="bulkAction === 'assignee'" x-model="bulkValue" class="h-8 rounded-lg border border-[#DCE8F2] bg-white px-2 text-[10px]"><option value="">بدون مسئول</option><template x-for="member in projectMembers" :key="'bulk-' + member.id"><option :value="member.name" x-text="member.name"></option></template></select>
            <select x-show="bulkAction === 'column'" x-model="bulkValue" class="h-8 rounded-lg border border-[#DCE8F2] bg-white px-2 text-[10px]"><template x-for="column in columns" :key="'bulk-col-' + column.id"><option :value="column.id" x-text="column.title"></option></template></select>
            <input x-show="bulkAction === 'tag'" x-model="bulkValue" class="h-8 w-28 rounded-lg border border-[#DCE8F2] px-2 text-[10px]" placeholder="نام برچسب">
            <button type="button" @click="applyBulkAction()" :disabled="!bulkAction || bulkLoading" class="h-8 rounded-lg bg-[#111111] px-3 text-[10px] font-black text-white disabled:opacity-40">اعمال</button>
            <button type="button" @click="clearSelection()" class="mr-auto text-[10px] font-bold text-[#64788A]">لغو انتخاب</button>
        </div>
    </div>

    <p x-show="activeFilterCount() > 0 || boardSearchQuery.trim()" class="board-filter-order-note" role="status">برای مرتب‌سازی با کشیدن، فیلتر و جستجو را پاک کنید. انتقال به ستون همچنان در جزئیات وظیفه در دسترس است.</p>
    {{-- Active filters strip --}}
    <div x-show="activeFilterCount() > 0" x-cloak class="board-filter-bar">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 py-2.5">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[12px] font-bold text-[#64748B]">فیلتر فعال:</span>
                <template x-for="name in filterByAssignee" :key="'chip-a-' + name">
                    <button type="button" @click="toggleAssigneeFilter(name)" class="filter-chip is-active">
                        <span x-text="name"></span>
                        <span aria-hidden="true">×</span>
                    </button>
                </template>
                <template x-for="p in filterByPriority" :key="'chip-p-' + p">
                    <button type="button" @click="togglePriorityFilter(p)" class="filter-chip is-active">
                        <span x-text="'اولویت ' + p"></span>
                        <span aria-hidden="true">×</span>
                    </button>
                </template>
                <template x-for="tag in filterByTag" :key="'chip-t-' + tag">
                    <button type="button" @click="toggleTagFilter(tag)" class="filter-chip is-active">
                        <span x-text="tag"></span>
                        <span aria-hidden="true">×</span>
                    </button>
                </template>
                <button x-show="filterByDue" type="button" @click="filterByDue = ''" class="filter-chip is-active">سررسید: <span x-text="dueFilterLabel()"></span><span aria-hidden="true">×</span></button>
                <button type="button" @click="clearAllFilters()" class="text-[11px] font-bold text-red-500 mr-auto">پاک کردن همه</button>
            </div>
        </div>
    </div>

    {{-- Mobile filter sheet --}}
    <div x-show="filterPanelOpen" x-cloak class="md:hidden fixed inset-0 z-[45]" @keydown.escape.window="filterPanelOpen = false">
        <div class="absolute inset-0 bg-[#071B33]/40" @click="filterPanelOpen = false"></div>
        <div class="absolute inset-x-0 bottom-0 max-h-[calc(100dvh-1rem)] overflow-y-auto rounded-t-2xl bg-white border-t border-[#E2E8F0] p-4 pb-[max(1rem,env(safe-area-inset-bottom))] space-y-3 shadow-lg" @click.stop>
            <div class="flex items-center justify-between">
                <p class="text-[14px] font-black text-[#18212B]">فیلتر تخته</p>
                <button type="button" @click="filterPanelOpen = false" class="text-[12px] font-bold text-[#64748B] min-h-10 px-2">بستن</button>
            </div>
            <div>
                <p class="board-field-label">مسئول</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="member in projectMembers" :key="'m-filter-a-' + member.id">
                        <button type="button" @click="toggleAssigneeFilter(member.name)" class="filter-chip" :class="isAssigneeFilterActive(member.name) ? 'is-active' : ''" x-text="member.name"></button>
                    </template>
                    <p x-show="projectMembers.length === 0" class="text-[12px] text-[#94A3B8]">عضوی در پروژه نیست</p>
                </div>
            </div>
            <div>
                <p class="board-field-label">اولویت</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="p in ['بالا', 'متوسط', 'پایین']" :key="'m-filter-p-' + p">
                        <button type="button" @click="togglePriorityFilter(p)" class="filter-chip" :class="filterByPriority.includes(p) ? 'is-active' : ''" x-text="p"></button>
                    </template>
                </div>
            </div>
            <div>
                <label class="board-field-label" for="mobile-due-filter">سررسید</label>
                <select id="mobile-due-filter" x-model="filterByDue" class="w-full min-h-11 rounded-lg border border-[#E2E8F0] bg-white px-3 text-[12px]">
                    <option value="">همه سررسیدها</option><option value="overdue">عقب‌افتاده</option><option value="today">امروز</option><option value="next7">۷ روز آینده</option><option value="undated">بدون سررسید</option>
                </select>
            </div>
            <div>
                <p class="board-field-label">برچسب</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="tag in allTags" :key="'m-filter-t-' + tag.name">
                        <button type="button" @click="toggleTagFilter(tag.name)" class="filter-chip" :class="filterByTag.includes(tag.name) ? 'is-active' : ''" x-text="tag.name"></button>
                    </template>
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" @click="clearAllFilters()" class="flex-1 min-h-11 rounded-xl border border-[#E2E8F0] text-[12px] font-bold text-[#64748B]">پاک کردن</button>
                <button type="button" @click="filterPanelOpen = false" class="flex-1 min-h-11 rounded-xl bg-[#18212B] text-white text-[12px] font-black">اعمال</button>
            </div>
        </div>
    </div>

    {{-- Board --}}
    <div class="board-canvas w-full">

        {{-- Desktop board --}}
        <div id="desktop-column-track" class="hidden md:flex gap-3 items-start overflow-x-auto px-3 sm:px-6 pt-4 md:pt-5 pb-4" style="direction: rtl;" x-init="$nextTick(() => initColumnSortable('desktop'))">
            <template x-for="(column, colIdx) in columns" :key="column.id">
                <div
                    class="board-column board-column-shell flex flex-col shrink-0"
                    :data-column-id="column.id"
                    :style="columnStyle(column)"
                    @click="if (column.collapsed) column.collapsed = false"
                    :class="[column.collapsed ? '!w-14 cursor-pointer' : '', column.workflowRole === 'active' ? 'is-active-column' : '', column.workflowRole === 'done' ? 'is-done-column' : '']"
                    :title="column.collapsed ? 'باز کردن ستون «' + column.title + '»' : ''"
                >
                    <div x-show="column.collapsed" class="mt-14 flex min-h-[240px] flex-col items-center justify-start rounded-xl border border-[#E8EBE9] px-2 py-4 text-[#18212B] shadow-sm">
                        <div class="flex h-[170px] w-full flex-col items-center rounded-xl border border-white/60 px-1.5 py-3 text-white shadow-sm" :style="collapsedColumnStyle(column)">
                            <span class="text-sm font-black" x-text="toPersianDigits(column.tasks.length)"></span>
                            <span class="mt-4 [writing-mode:vertical-rl] rotate-180 text-xs font-black tracking-wide" x-text="column.title"></span>
                        </div>
                        <span class="mt-auto text-[11px] font-bold text-[#64748B]">باز کردن</span>
                    </div>
                    <div x-show="!column.collapsed" class="board-column-header">
                        <div class="board-column-header__identity">
                            <span class="board-column-header-accent" aria-hidden="true"></span>
                            <h2 class="board-column-header__title" x-text="column.title"></h2>
                            <span class="board-column-header__count" x-text="toPersianDigits(column.tasks.length) + (column.wipLimit ? ' / ' + toPersianDigits(column.wipLimit) : '')"></span>
                            <span x-show="column.wipLimit" class="board-column-header__wip" :class="column.wipLimit && column.tasks.length > column.wipLimit ? 'is-over' : ''" x-text="column.wipLimit ? 'ظرفیت ' + toPersianDigits(column.wipLimit) : ''"></span>
                        </div>
                        <div class="board-column-header__utilities">
                            @if ($canEdit)
                                <button type="button" class="column-drag-handle board-column-header__button cursor-grab active:cursor-grabbing" title="کشیدن برای جابه‌جایی ستون" aria-label="کشیدن برای جابه‌جایی ستون">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="6" r="1.5"/><circle cx="16" cy="6" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="18" r="1.5"/><circle cx="16" cy="18" r="1.5"/></svg>
                                </button>
                            @endif

                            <button @click.stop="column.collapsed = true" class="board-column-header__button" title="جمع کردن ستون" aria-label="جمع کردن ستون"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m9 18 6-6-6-6"/></svg></button>

                            @if ($canEdit)
                                <div class="relative" @click.away="if (openColumnMenuId === column.id) openColumnMenuId = null">
                                    <button @click.stop="openColumnMenuId = openColumnMenuId === column.id ? null : column.id" class="board-column-header__button" title="گزینه‌های ستون" aria-label="گزینه‌های ستون" :aria-expanded="openColumnMenuId === column.id"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="19" cy="12" r="1.8"/></svg></button>
                                    <div x-show="openColumnMenuId === column.id" x-transition class="absolute left-0 top-full mt-1 w-44 rounded-xl border border-[#E8EBE9] bg-white py-1 shadow-xl z-20" @click.stop>
                                        <button @click="openColumnMenuId = null; openEditColumnModal(column)" class="w-full px-3 py-2.5 text-right text-[12px] font-bold text-[#475569] hover:bg-[#FBFAF7]">ویرایش نام و رنگ</button>
                                        <div class="my-1 border-t border-[#F1F5F9]"></div>
                                        <button @click="openColumnMenuId = null; moveColumnByStep(column.id, -1)" :disabled="columnMovePending || colIdx === 0" class="w-full px-3 py-2 text-right text-[11px] font-bold text-[#475569] hover:bg-[#FBFAF7] disabled:cursor-not-allowed disabled:opacity-35">انتقال یک جایگاه به قبل</button>
                                        <button @click="openColumnMenuId = null; moveColumnByStep(column.id, 1)" :disabled="columnMovePending || colIdx === columns.length - 1" class="w-full px-3 py-2 text-right text-[11px] font-bold text-[#475569] hover:bg-[#FBFAF7] disabled:cursor-not-allowed disabled:opacity-35">انتقال یک جایگاه به بعد</button>
                                        <label class="block px-3 py-2 text-[11px] font-bold text-[#475569]">
                                            جایگاه دقیق
                                            <select :value="colIdx" :disabled="columnMovePending" @change="moveColumnToIndex(column.id, $event.target.value); openColumnMenuId = null" class="mt-1 w-full min-h-9 rounded-lg border border-[#E2E8F0] bg-white px-2">
                                                <template x-for="(destination, position) in columns" :key="'desktop-position-' + destination.id">
                                                    <option :value="position" x-text="toPersianDigits(position + 1) + ' — ' + destination.title"></option>
                                                </template>
                                            </select>
                                        </label>
                                        <div class="my-1 border-t border-[#F1F5F9]"></div>
                                        <button @click="openColumnMenuId = null; confirmDeleteColumn(column)" class="w-full px-3 py-2.5 text-right text-[12px] font-bold text-red-500 hover:bg-red-50">حذف</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div x-show="!column.collapsed" class="board-column-well">
                        <div class="board-task-list" :id="'col-desktop-' + column.id" x-init="$nextTick(() => initSortable(column.id, 'desktop'))">
                        <template x-for="task in filteredTasks(column)" :key="task.dbId">
                            <div
                                class="task-card group"
                                :class="[isTaskSelected(task.dbId) ? 'is-selected' : '', task.isBlocked ? 'is-blocked' : '']"
                                role="group"
                                :aria-label="'باز کردن وظیفه: ' + task.title"
                                :data-id="task.dbId"
                                :data-column="column.id"
                                @click="openEditModal(task, column.id)"
                            >
                                <div class="pr-2">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" :checked="isTaskSelected(task.dbId)" @click.stop="toggleTaskSelection(task.dbId)" class="board-task-select" aria-label="انتخاب وظیفه">
                                            <span class="task-card__id text-[11px] font-bold text-[#94A3B8]" x-text="task.id"></span>
                                        </div>
                                        <div class="flex flex-wrap gap-1 justify-end">
                                            <span
                                                class="task-card__priority-badge" x-show="task.priority === 'بالا'"
                                                :class="priorityBadgeClass(task.priority)"
                                                x-text="task.priority"
                                            ></span>
                                            <span x-show="task.isBlocked" class="task-card__blocked">مسدود</span>

                                        </div>
                                    </div>
                                    <button type="button" class="task-card__title text-right w-full" @click.stop="if (canOpenTaskFromCard()) openEditModal(task, column.id)" :aria-label="'باز کردن وظیفه: ' + task.title" x-html="highlightText(task.title, boardSearchQuery)"></button>
                                    <div class="task-card__tags" x-show="(task.tags || []).length">
                                            <template x-for="tag in visibleTags(task)" :key="tag">
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md" :class="getTagClass(tag)" x-text="tag"></span>
                                            </template>
                                            <span x-show="hiddenTagCount(task) > 0" class="text-[10px] font-bold text-[#94A3B8]" x-text="'+' + toPersianDigits(hiddenTagCount(task))"></span>
                                    </div>
                                    <p x-show="task.description" class="task-card__desc" x-html="highlightText(task.description, boardSearchQuery)"></p>
                                    <div class="task-card__checklist-bar" x-show="checklistTotal(task) > 0">
                                        <span :style="'width:' + checklistPercentFor(task) + '%'"></span>
                                    </div>
                                    <div class="flex items-center justify-between mt-2.5 pt-2 border-t border-[#F1F5F9]">
                                        <div class="flex items-center -space-x-1.5 space-x-reverse">
                                            <template x-for="(a, ai) in (task.assignees || []).slice(0, 3)" :key="ai">
                                                <div class="w-6 h-6 rounded-full bg-[#18212B] flex items-center justify-center shadow-sm ring-2 ring-white" :style="'z-index:' + (10 - ai)">
                                                    <span class="text-[8px] text-white font-bold" x-text="a.charAt(0)"></span>
                                                </div>
                                            </template>
                                            <span x-show="(task.assignees || []).length > 3" class="text-[10px] text-[#94A3B8] font-bold mr-1" x-text="'+' + toPersianDigits((task.assignees || []).length - 3)"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span x-show="checklistTotal(task) > 0" class="task-card__checklist-count" x-text="checklistLabel(task)"></span>
                                            <span x-show="(task.comments || []).length > 0" class="text-[10px] font-bold text-[#94A3B8]" x-text="toPersianDigits((task.comments || []).length) + ' نظر'"></span>
                                            <div class="flex items-center gap-1" x-show="task.dueDate">
                                                <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="text-[11px] font-medium" :class="isOverdue(task.dueDate, task.dueTime) ? 'text-red-500' : 'text-[#94A3B8]'" x-text="formatDate(task.dueDate, task.dueTime)"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="column.tasks.length > 0 && filteredTasks(column).length === 0" class="board-no-matches"><p>وظیفه‌ای با این فیلترها پیدا نشد.</p><button type="button" @click="clearAllFilters(); clearBoardSearch()">پاک کردن فیلتر و جستجو</button></div>
                        <div x-show="column.tasks.length === 0" class="board-empty-state">
                            @if ($canEdit)
                                <button type="button" @click.stop="openQuickComposer(column.id)" class="board-empty-state__action" :aria-label="'ایجاد اولین وظیفه در ستون ' + column.title">
                                    <span class="board-create-task__icon" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.2" d="M12 5v14m-7-7h14"/></svg>
                                    </span>
                                    <span class="board-create-task__copy">
                                        <strong>اولین وظیفه را ایجاد کنید</strong>
                                        <small x-text="'در ستون «' + column.title + '»'"></small>
                                    </span>
                                </button>
                            @else
                                <p class="board-empty-title">هنوز وظیفه‌ای در این ستون نیست</p>
                            @endif
                        </div>
                        @if ($canEdit)
                            <div x-show="column.tasks.length > 0" class="board-column-footer">
                                <button type="button" @click.stop="openQuickComposer(column.id)" class="board-create-task" :aria-label="'ایجاد وظیفه جدید در ستون ' + column.title">
                                    <span class="board-create-task__icon" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.2" d="M12 5v14m-7-7h14"/></svg>
                                    </span>
                                    <span class="board-create-task__copy">
                                        <strong>وظیفه جدید</strong>
                                        <small x-text="'در ستون «' + column.title + '»'"></small>
                                    </span>
                                </button>
                            </div>
                        @endif
                        <form x-show="!column.collapsed && String(quickComposerColumnId) === String(column.id)" x-cloak @submit.prevent="createQuickTask(column.id)" class="board-quick-composer">
                            <textarea x-model="quickTaskTitle" @keydown.escape.prevent="closeQuickComposer()" @keydown.enter.exact.prevent="createQuickTask(column.id)" rows="2" placeholder="چه کاری باید انجام شود؟" aria-label="عنوان وظیفه جدید"></textarea>
                            <div><button type="submit" :disabled="quickTaskSaving || !quickTaskTitle.trim()">افزودن</button><button type="button" @click="closeQuickComposer()">انصراف</button><button type="button" @click="createQuickTask(column.id, true)" :disabled="quickTaskSaving">جزئیات بیشتر</button></div>
                        </form>
                        </div>
                    </div>
                </div>
            </template>
            @if ($canEdit)
                <button @click="openColumnModal()" class="board-add-column mt-14 min-w-[72px] w-[72px] min-h-[240px] rounded-2xl border-2 border-dashed border-[#D7D1C5] hover:border-[#18212B] hover:bg-[#F1F3F2] text-[#64748B] hover:text-[#18212B] flex items-center justify-center transition-colors" title="افزودن ستون"><span class="[writing-mode:vertical-rl] text-xs font-black" x-text="'+ ستون جدید'"></span></button>
            @endif
        </div>

        {{-- Mobile column navigator --}}
        <section class="board-mobile-navigator md:hidden bg-white border-b border-[#DCE4EE] shadow-[0_5px_18px_rgba(7,27,51,0.05)] sticky top-[var(--board-header-height,58px)] z-20">
            <div id="mobile-column-tabs" x-ref="mobileColumnTabs" class="mobile-column-tabs flex gap-1.5 overflow-x-auto px-3 pt-2.5 pb-2" role="tablist" aria-label="ستون‌های تخته">
                <template x-for="(column, index) in columns" :key="'tab-' + column.id">
                    <button
                        @click="scrollToColumn(index)"
                        :data-tab-index="index"
                        class="min-h-11 px-3 rounded-xl border flex items-center gap-1.5 whitespace-nowrap text-[10px] font-black transition-colors"
                        :class="activeColumnIndex === index ? 'bg-[#F1F3F2] border-[#AEB8B2] text-[#18212B]' : 'bg-white border-[#E2E8F0] text-[#64748B]'"
                        :aria-current="activeColumnIndex === index ? 'true' : 'false'"
                        :aria-selected="activeColumnIndex === index ? 'true' : 'false'"
                        role="tab"
                    >
                        <span x-text="column.title"></span>
                        <span class="min-w-5 h-5 px-1 rounded-full flex items-center justify-center text-[9px]" :class="activeColumnIndex === index ? 'bg-[#18212B] text-white' : 'bg-[#F1F5F9] text-[#64748B]'" x-text="toPersianDigits(column.tasks.length)"></span>
                    </button>
                </template>
            </div>
            <div class="px-3 pb-2.5 flex items-center justify-between gap-2">
                <button type="button" @click="scrollToColumn(activeColumnIndex - 1)" :disabled="activeColumnIndex === 0" class="mobile-column-nav" aria-label="ستون قبلی">›</button>
                <span class="text-[9px] font-bold text-[#64748B]" x-text="toPersianDigits(activeColumnIndex + 1) + ' از ' + toPersianDigits(columns.length)"></span>
                <span class="text-[11px] font-bold text-[#64748B] truncate" x-text="columns[activeColumnIndex]?.title || ''"></span>
                <button type="button" @click="scrollToColumn(activeColumnIndex + 1)" :disabled="activeColumnIndex === columns.length - 1" class="mobile-column-nav" aria-label="ستون بعدی">‹</button>
            </div>
            @if ($canEdit)
                <div class="board-column-order-help px-3 pb-2 text-[9px] font-bold text-[#64748B] flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-[#18212B]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="6" r="1.5"/><circle cx="16" cy="6" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="18" r="1.5"/><circle cx="16" cy="18" r="1.5"/></svg>برای جابه‌جایی دقیق ستون، جایگاه آن را انتخاب کنید.</div>
            @endif
        </section>

        {{-- Mobile one-column swipe board --}}
        <div
            id="mobile-column-track"
            x-ref="mobileBoardTrack"
            @scroll.passive="handleMobileBoardScroll()"
            class="mobile-board-track md:hidden flex gap-3 overflow-x-auto snap-x snap-mandatory px-3 pt-4 pb-8"
            style="direction: rtl;"
            aria-label="تخته پروژه"
            x-init="$nextTick(() => initColumnSortable('mobile'))"
        >
            <template x-for="(column, colIdx) in columns" :key="'mobile-' + column.id">
                <section
                    class="mobile-board-column flex flex-col flex-none w-[calc(100%_-_24px)] min-w-[calc(100%_-_24px)]"
                    :class="{ 'is-active-column': column.workflowRole === 'active', 'is-done-column': column.workflowRole === 'done' }"
                    :data-column-index="colIdx"
                    :data-column-id="column.id"
                    :aria-label="column.title"
                    :style="columnStyle(column)"
                >
                    <div class="board-column-mobile-header">
                        <div class="board-column-header__identity">
                            <span class="board-column-header-accent" aria-hidden="true"></span>
                            <h2 class="board-column-header__title" x-text="column.title"></h2>
                            <span class="board-column-header__count" x-text="toPersianDigits(column.tasks.length) + (column.wipLimit ? ' / ' + toPersianDigits(column.wipLimit) : '')"></span>
                        </div>
                        <div class="board-column-header__utilities">
                            @if ($canEdit)
                                <button type="button" class="column-drag-handle board-column-header__button board-column-header__button--mobile cursor-grab" title="کشیدن برای جابه‌جایی ستون" aria-label="کشیدن برای جابه‌جایی ستون">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="6" r="1.5"/><circle cx="16" cy="6" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="18" r="1.5"/><circle cx="16" cy="18" r="1.5"/></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                    @if ($canEdit)
                        <label class="board-column-position">
                            <span>جایگاه ستون</span>
                            <select :aria-label="'جایگاه ستون ' + column.title" :value="colIdx"
                                    :disabled="columnMovePending || taskMovePending"
                                    @change="moveColumnToIndex(column.id, $event.target.value)">
                                <template x-for="(destination, position) in columns" :key="'position-' + destination.id">
                                    <option :value="position" :selected="position === colIdx" x-text="toPersianDigits(position + 1) + ' — ' + destination.title"></option>
                                </template>
                            </select>
                        </label>
                    @endif
                    <div
                        class="mobile-task-list flex flex-1 min-h-0 flex-col rounded-xl p-0 bg-[#F1F0EC] border transition-colors"
                        :class="mobileDragActive && activeColumnIndex === colIdx ? 'border-[#18212B]/60 bg-[#EEF1EF]/65' : 'border-[#CBD5E1]/50'"
                    >
                        <div class="mobile-task-scroll flex flex-1 min-h-0 flex-col gap-3" :id="'col-mobile-' + column.id" x-init="$nextTick(() => initSortable(column.id, 'mobile'))">
                        <template x-for="task in filteredTasks(column)" :key="'mobile-task-' + task.dbId">
                            <article
                                class="task-card group"
                                :class="[isTaskSelected(task.dbId) ? 'is-selected' : '', task.isBlocked ? 'is-blocked' : '']"
                                role="group"
                                :aria-label="'باز کردن وظیفه: ' + task.title"
                                :data-id="task.dbId"
                                :data-column="column.id"
                                @click="if (canOpenTaskFromCard()) openEditModal(task, column.id)"
                            >
                                <div class="pr-2">
                                    <div class="flex items-start justify-between gap-2 mb-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            <input type="checkbox" :checked="isTaskSelected(task.dbId)" @click.stop="toggleTaskSelection(task.dbId)" class="board-task-select" aria-label="انتخاب وظیفه">
                                            <span class="task-card__priority-badge" x-show="task.priority === 'بالا'" :class="priorityBadgeClass(task.priority)" x-text="task.priority"></span>
                                            <span x-show="task.isBlocked" class="task-card__blocked">مسدود</span>

                                        </div>
                                        @if ($canEdit)
                                            <button @click.stop class="task-drag-handle w-11 h-11 -mt-2.5 -ml-2.5 rounded-xl text-[#94A3B8] flex items-center justify-center active:bg-[#F1F5F9] cursor-grab" aria-label="جابجایی وظیفه">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="8" cy="6" r="1.5"/><circle cx="16" cy="6" r="1.5"/><circle cx="8" cy="12" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="8" cy="18" r="1.5"/><circle cx="16" cy="18" r="1.5"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                    <span class="task-card__id block text-[11px] font-bold text-[#94A3B8] mb-1.5" x-text="task.id"></span>
                                    <button type="button" class="task-card__title text-right w-full" @click.stop="if (canOpenTaskFromCard()) openEditModal(task, column.id)" :aria-label="'باز کردن وظیفه: ' + task.title" x-html="highlightText(task.title, boardSearchQuery)"></button>
                                    <div class="task-card__tags" x-show="(task.tags || []).length">
                                            <template x-for="tag in visibleTags(task)" :key="tag">
                                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md" :class="getTagClass(tag)" x-text="tag"></span>
                                            </template>
                                            <span x-show="hiddenTagCount(task) > 0" class="text-[10px] font-bold text-[#94A3B8]" x-text="'+' + toPersianDigits(hiddenTagCount(task))"></span>
                                    </div>
                                    <p x-show="task.description" class="task-card__desc" x-html="highlightText(task.description, boardSearchQuery)"></p>
                                    <div class="task-card__checklist-bar" x-show="checklistTotal(task) > 0">
                                        <span :style="'width:' + checklistPercentFor(task) + '%'"></span>
                                    </div>
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-[#EEF2F6]">
                                        <div class="flex items-center -space-x-1.5 space-x-reverse">
                                            <template x-for="(a, ai) in (task.assignees || []).slice(0, 3)" :key="ai">
                                                <div class="w-6 h-6 rounded-full bg-[#18212B] flex items-center justify-center shadow-sm ring-2 ring-white" :style="'z-index:' + (10 - ai)">
                                                    <span class="text-[8px] text-white font-bold" x-text="a.charAt(0)"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span x-show="checklistTotal(task) > 0" class="task-card__checklist-count" x-text="checklistLabel(task)"></span>
                                            <div class="flex items-center gap-1" x-show="task.dueDate">
                                                <svg class="w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="text-[11px] font-bold" :class="isOverdue(task.dueDate, task.dueTime) ? 'text-red-500' : 'text-[#64748B]'" x-text="formatDate(task.dueDate, task.dueTime)"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($canEdit)
                                        <label class="mobile-task-move" @click.stop>
                                            <span>انتقال به ستون</span>
                                            <select :disabled="taskMovePending || !canEdit" :aria-label="'انتقال وظیفه ' + task.title + ' به ستون'" @click.stop @change.stop="moveTask(column.id, $event.target.value, task.dbId, columns.find(c => String(c.id) === $event.target.value)?.tasks.length || 0); $event.target.value = ''">
                                                <option value="">انتخاب ستون</option>
                                                <template x-for="destination in columns.filter(c => c.id !== column.id)" :key="'move-' + task.dbId + '-' + destination.id">
                                                    <option :value="destination.id" x-text="destination.title"></option>
                                                </template>
                                            </select>
                                        </label>
                                    @endif
                                </div>
                            </article>
                        </template>
                        <div x-show="column.tasks.length > 0 && filteredTasks(column).length === 0" class="board-no-matches"><p>وظیفه‌ای با این فیلترها پیدا نشد.</p><button type="button" @click="clearAllFilters(); clearBoardSearch()">پاک کردن فیلتر و جستجو</button></div>
                        <div x-show="column.tasks.length === 0" class="board-empty-state board-empty-state--mobile">
                            @if ($canEdit)
                                <button type="button" @click="openQuickComposer(column.id)" class="board-empty-state__action" :aria-label="'ایجاد اولین وظیفه در ستون ' + column.title">
                                    <span class="board-create-task__icon" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.2" d="M12 5v14m-7-7h14"/></svg>
                                    </span>
                                    <span class="board-create-task__copy">
                                        <strong>اولین وظیفه را ایجاد کنید</strong>
                                        <small x-text="'در ستون «' + column.title + '»'"></small>
                                    </span>
                                </button>
                            @else
                                <p class="board-empty-title">هنوز وظیفه‌ای در این ستون نیست</p>
                            @endif
                        </div>
                        @if ($canEdit)
                            <div x-show="column.tasks.length > 0" class="board-column-footer">
                                <button type="button" @click="openQuickComposer(column.id)" class="board-create-task" :aria-label="'ایجاد وظیفه جدید در ستون ' + column.title">
                                    <span class="board-create-task__icon" aria-hidden="true">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2.2" d="M12 5v14m-7-7h14"/></svg>
                                    </span>
                                    <span class="board-create-task__copy">
                                        <strong>وظیفه جدید</strong>
                                        <small x-text="'در ستون «' + column.title + '»'"></small>
                                    </span>
                                </button>
                            </div>
                        @endif
                        <form x-show="String(quickComposerColumnId) === String(column.id)" x-cloak @submit.prevent="createQuickTask(column.id)" class="board-quick-composer">
                            <textarea x-model="quickTaskTitle" @keydown.escape.prevent="closeQuickComposer()" @keydown.enter.exact.prevent="createQuickTask(column.id)" rows="2" placeholder="چه کاری باید انجام شود؟" aria-label="عنوان وظیفه جدید"></textarea>
                            <div><button type="submit" :disabled="quickTaskSaving || !quickTaskTitle.trim()">افزودن</button><button type="button" @click="closeQuickComposer()">انصراف</button><button type="button" @click="createQuickTask(column.id, true)" :disabled="quickTaskSaving">جزئیات بیشتر</button></div>
                        </form>
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
                <svg class="w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7l-5 5 5 5m8-10l5 5-5 5M3 12h18"/></svg>
                برای جابه‌جایی بین ستون‌ها ورق بزنید
            </button>
        </div>
        <p class="sr-only" aria-live="polite" x-text="'ستون ' + (columns[activeColumnIndex]?.title || '')"></p>
    </div>

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
                <div x-show="activeColumnIndex < columns.length - 1" class="w-10 h-16 rounded-r-2xl bg-[#18212B] text-white shadow-lg flex items-center justify-center">
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
                <div x-show="activeColumnIndex > 0" class="w-10 h-16 rounded-l-2xl bg-[#18212B] text-white shadow-lg flex items-center justify-center">
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
            class="absolute inset-y-0 left-0 w-full sm:w-[430px] bg-white shadow-[8px_0_24px_rgba(24,33,43,0.12)] flex flex-col"
            @click.stop
            x-ref="projectDrawer"
            role="dialog" aria-modal="true" aria-labelledby="project-settings-title"
            @keydown="trapProjectFocus($event)"
        >
            <header class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
                <div>
                    <h2 id="project-settings-title" class="text-base font-black text-[#071B33]">تنظیمات پروژه</h2>
                    <p class="text-[11px] text-[#64748B] mt-1" x-text="projectState.name"></p>
                </div>
                <button x-ref="projectDrawerClose" aria-label="بستن تنظیمات پروژه" @click="closeProjectDrawer()" class="w-11 h-11 rounded-lg text-[#64748B] hover:text-[#071B33] hover:bg-[#F1F5F9] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </header>

            <div class="flex border-b border-[#E2E8F0] px-5" role="tablist" aria-label="بخش‌های تنظیمات پروژه" @keydown="handleProjectTabKeys($event)">
                <button type="button" id="project-tab-settings" role="tab" aria-controls="project-panel-settings" :aria-selected="projectDrawerTab === 'settings'" :tabindex="projectDrawerTab === 'settings' ? 0 : -1" @click="selectProjectTab('settings')" class="px-3 min-h-11 text-xs font-black border-b-2" :class="projectDrawerTab === 'settings' ? 'text-[#18212B] border-[#18212B]' : 'text-[#64748B] border-transparent'">عمومی</button>
                <button type="button" id="project-tab-members" role="tab" aria-controls="project-panel-members" :aria-selected="projectDrawerTab === 'members'" :tabindex="projectDrawerTab === 'members' ? 0 : -1" @click="selectProjectTab('members')" class="px-3 min-h-11 text-xs font-black border-b-2" :class="projectDrawerTab === 'members' ? 'text-[#18212B] border-[#18212B]' : 'text-[#64748B] border-transparent'">اعضای پروژه</button>
                <button type="button" id="project-tab-activity" role="tab" aria-controls="project-panel-activity" :aria-selected="projectDrawerTab === 'activity'" :tabindex="projectDrawerTab === 'activity' ? 0 : -1" @click="selectProjectTab('activity')" class="px-3 min-h-11 text-xs font-black border-b-2" :class="projectDrawerTab === 'activity' ? 'text-[#18212B] border-[#18212B]' : 'text-[#64748B] border-transparent'">فعالیت‌ها</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <section id="project-panel-members" role="tabpanel" aria-labelledby="project-tab-members" x-show="projectDrawerTab === 'members'">
                    <div class="mb-4">
                        <h3 class="text-sm font-black text-[#071B33]">تیم پروژه</h3>
                        <p class="text-[11px] leading-5 text-[#64748B] mt-1">افراد انتخاب‌شده می‌توانند به وظیفه‌ها تخصیص داده شوند و در گفتگوها منشن شوند.</p>
                    </div>
                    <div class="relative mb-4">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input x-model="projectMemberSearch" class="w-full text-xs border-2 border-[#E2E8F0] rounded-xl pr-10 pl-3 py-3 focus:outline-none focus:border-[#18212B]" placeholder="جستجوی اعضای فضای کاری…">
                    </div>
                    <div class="space-y-2">
                        <template x-for="person in filteredWorkspacePeople()" :key="person.id">
                            <button
                                @click="toggleProjectMember(person)"
                                :disabled="projectMemberSaving === person.id"
                                class="w-full flex items-center gap-3 rounded-xl border p-3 text-right transition-all disabled:opacity-60"
                                :class="isProjectMember(person.id) ? 'border-[#C5CCC7] bg-[#F5F7F6]' : 'border-[#E2E8F0] hover:border-[#B8C4D4] bg-white'"
                            >
                                <div class="w-9 h-9 rounded-full bg-[#071B33] text-white flex items-center justify-center text-xs font-black" x-text="person.name.charAt(0)"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] font-bold text-[#111111] truncate" x-text="person.name"></p>
                                    <p class="text-[10px] text-[#94A3B8] mt-0.5" x-text="person.phone"></p>
                                </div>
                                <span class="w-6 h-6 rounded-md flex items-center justify-center border-2" :class="isProjectMember(person.id) ? 'bg-[#18212B] border-[#18212B] text-white' : 'border-[#CBD5E1] text-transparent'">
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
                                    :class="isAssigneeFilterActive(member.name) ? 'bg-[#F1F3F2] border border-[#C5CCC7]' : 'hover:bg-[#F8FAFC]'"
                                >
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0" :class="isAssigneeFilterActive(member.name) ? 'bg-[#18212B] text-white' : 'bg-[#F1F3F2] text-[#18212B]'">
                                        <span class="text-[8px] font-bold" x-text="member.name.charAt(0)"></span>
                                    </div>
                                    <span class="text-[11px] font-bold" :class="isAssigneeFilterActive(member.name) ? 'text-[#18212B]' : 'text-[#475569]'" x-text="member.name"></span>
                                    <svg x-show="isAssigneeFilterActive(member.name)" class="w-3.5 h-3.5 text-[#18212B] mr-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </template>
                        </div>
                        <p x-show="filterByAssignee.length > 0" class="text-[9px] text-[#94A3B8] mt-2 text-center">نمایش وظایف <span x-text="filterByAssignee.length"></span> عضو</p>
                    </div>
                </section>

                <section id="project-panel-settings" role="tabpanel" aria-labelledby="project-tab-settings" x-show="projectDrawerTab === 'settings'" class="space-y-5">
                    <p x-show="projectSettingsError" role="alert" class="rounded-xl bg-red-50 p-3 text-sm text-red-700" x-text="projectSettingsError"></p>
                    <button type="button" x-show="projectSettingsError" @click="reloadProjectSettings()" class="text-xs font-bold min-h-11">بارگذاری تنظیمات جدید (کنار گذاشتن پیش‌نویس)</button>
                    <p x-show="projectSettingsDirty()" class="text-xs text-amber-800">تغییرات هنوز ذخیره نشده‌اند.</p>
                    <div>
                        <label for="project-settings-name" class="board-field-label">نام پروژه</label>
                        <input id="project-settings-name" x-model="projectForm.name" class="w-full text-sm font-bold border-2 border-[#E2E8F0] rounded-xl px-3.5 py-3 focus:outline-none focus:border-[#18212B]">
                    </div>
                    <div>
                        <label for="project-settings-key" class="board-field-label">کلید پروژه</label>
                        <input id="project-settings-key" x-model="projectForm.key" maxlength="10" dir="ltr" class="w-full text-sm font-bold uppercase border-2 border-[#E2E8F0] rounded-xl px-3.5 py-3 focus:outline-none focus:border-[#18212B]">
                    </div>
                    <div>
                        <label for="project-settings-description" class="board-field-label">توضیحات پروژه</label>
                        <textarea id="project-settings-description" x-model="projectForm.description" rows="5" class="w-full text-sm leading-7 border-2 border-[#E2E8F0] rounded-xl px-3.5 py-3 focus:outline-none focus:border-[#18212B] resize-none" placeholder="هدف و محدوده پروژه را توضیح دهید…"></textarea>
                    </div>
                    <div class="border-t border-[#E8EBE9] pt-5">
                        <label class="board-field-label">چرخه کاری <span class="font-normal text-[#94A3B8]">(اختیاری)</span></label>
                        <div class="flex items-center gap-2">
                            <select x-model="cycleLength" class="flex-1 text-xs border-2 border-[#E2E8F0] rounded-xl px-3 py-2.5 bg-white">
                                <option value="">بدون چرخه</option><option value="1">یک هفته</option><option value="2">دو هفته</option>
                            </select>
                            <button type="button" @click="saveCycleConfig()" class="text-[10px] font-bold text-[#111111] border border-[#BFD8EC] rounded-lg px-3 py-2.5">ذخیره</button>
                        </div>
                        <div x-show="activeCycle" class="mt-3 rounded-xl border border-[#DCE8F2] bg-[#F8FCFF] p-3">
                            <p class="text-xs font-black" x-text="activeCycle ? 'چرخه ' + toPersianDigits(activeCycle.number) : ''"></p>
                            <p class="mt-1 text-[10px] text-[#64748B]" x-text="activeCycle ? activeCycle.startsOn + ' تا ' + activeCycle.endsOn : ''"></p>
                            <button type="button" @click="finishCycle()" class="mt-3 text-[10px] font-bold text-white bg-[#111111] rounded-lg px-3 py-2">پایان و شروع چرخه بعد</button>
                        </div>
                        <button x-show="!activeCycle && cycleLength" type="button" @click="startCycle()" class="mt-3 text-[10px] font-bold text-[#111111]">شروع چرخه با وظیفه‌های باز فعلی</button>
                    </div>
                    <div class="border-t border-[#E8EBE9] pt-5">
                        <label class="board-field-label">نقش ستون‌ها در گردش کار</label>
                        <p class="text-[10px] leading-5 text-[#94A3B8] mb-3">نام ستون‌ها آزاد است؛ نئووا از این نقش‌ها برای امروز، کار فعال و انجام‌شده استفاده می‌کند.</p>
                        <div class="space-y-2">
                            <template x-for="column in columns" :key="'role-' + column.id">
                                <label class="flex items-center justify-between gap-3 text-[11px] font-bold text-[#334155]"><span x-text="column.title"></span><select x-model="column.workflowRole" @change="saveColumnRole(column)" class="text-[10px] border border-[#DCE8F2] rounded-lg px-2 py-1.5 bg-white"><option value="backlog">پس‌زمینه</option><option value="ready">آماده</option><option value="active">در حال انجام</option><option value="done">انجام شده</option><option value="other">سایر</option></select></label>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="board-field-label">برچسب‌های پروژه</label>
                        <p class="text-[11px] text-[#94A3B8] mb-2 leading-6">برچسب‌های سفارشی برای این پروژه. برچسب‌های پیش‌فرض همیشه در دسترس هستند.</p>
                        <div class="flex flex-wrap gap-1 mb-3">
                            <template x-for="tag in editableTags()" :key="tag.name">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-md border" :class="tag.activeClass">
                                    <span x-text="tag.name"></span>
                                    <template x-if="tag.isCustom">
                                        <button @click="removeCustomTag(tag.name)" class="hover:text-red-500 ml-0.5">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </template>
                                </span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <input
                                x-model="newTagName"
                                @keydown.enter="addCustomTag()"
                                type="text"
                                class="flex-1 text-[11px] border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#18212B] transition-colors placeholder:text-[#CBD5E1]"
                                placeholder="نام برچسب جدید..."
                                maxlength="20"
                            >
                            <div class="flex gap-1">
                                <template x-for="color in tagColors" :key="color">
                                    <button type="button" @click="newTagColor = color" class="w-5 h-5 rounded-full border-2 transition-all" :class="newTagColor === color ? 'border-[#18212B] scale-110' : 'border-transparent'" :style="'background-color:' + color"></button>
                                </template>
                            </div>
                            <button @click="addCustomTag()" :disabled="!newTagName.trim()" class="text-[10px] font-bold text-white bg-[#18212B] hover:bg-[#000000] disabled:opacity-40 px-3 py-2 rounded-lg transition-colors">افزودن</button>
                        </div>
                    </div>
                    @if ($canManageProject)
                        <div class="border-t border-[#E8EBE9] pt-5">
                            <h3 class="text-sm font-black">بایگانی و حذف پروژه</h3>
                            <p class="mt-2 text-xs leading-6">بایگانی، پروژه را از کارهای فعال کنار می‌گذارد و وظیفه‌ها و فایل‌ها را نگه می‌دارد.</p>
                            <button type="button" @click="archiveProject()" :disabled="projectSettingsSaving || projectSettingsDirty()" class="mt-3 rounded-lg border border-[#D8D8D3] px-3 min-h-11 text-xs font-bold" x-text="projectState.isActive ? 'بایگانی پروژه (قابل بازگشت)' : 'بازگرداندن پروژه'"></button>
                            <p class="mt-2 text-[11px] leading-6 text-[#64748B]">با حذف پروژه، ستون‌ها، وظیفه‌ها و فایل‌های آن نیز برای همیشه حذف می‌شوند.</p>
                            <button type="button" x-show="!confirmingDeletion" @click="confirmingDeletion = true; $nextTick(() => { $refs.projectDeleteForm.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); $refs.projectDeleteConfirmation.focus(); })" class="mt-3 rounded-lg border border-red-200 px-3 py-2 text-[11px] font-bold text-red-700 hover:bg-red-50">حذف پروژه</button>
                            <form x-ref="projectDeleteForm" x-show="confirmingDeletion" x-cloak method="POST" action="{{ route('dashboard.project.destroy', [$workspace->slug, $project->slug]) }}" class="mt-4 rounded-xl border border-red-200 bg-red-50/50 p-4">
                                @csrf
                                @method('DELETE')
<p class="text-xs text-red-700" x-show="projectSettingsDirty()">پیش از حذف، تغییرات تنظیمات را ذخیره کنید یا کنار بگذارید.</p>
                                <label for="project-delete-confirmation" class="block text-[11px] font-bold leading-6 text-[#334155]">برای تأیید، نام پروژه «<span x-text="projectState.name"></span>» را وارد کنید.</label>
                                <input x-ref="projectDeleteConfirmation" id="project-delete-confirmation" name="confirmation_name" type="text" x-model="confirmationName" required autocomplete="off" class="mt-2 w-full rounded-lg border border-red-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-red-600" spellcheck="false">
                                @error('confirmation_name')
                                    <p class="mt-2 text-[11px] text-red-700">{{ $message }}</p>
                                @enderror
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="submit" :disabled="confirmationName !== projectState.name || projectSettingsDirty()" class="rounded-lg bg-red-700 px-3 py-2 text-[11px] font-bold text-white disabled:cursor-not-allowed disabled:opacity-40">حذف دائمی پروژه</button>
                                    <button type="button" @click="confirmingDeletion = false; confirmationName = ''" class="rounded-lg px-3 py-2 text-[11px] font-bold text-[#475569] hover:bg-white">انصراف</button>
                                </div>
                            </form>
                        </div>
                    @endif
                </section>
                <section id="project-panel-activity" role="tabpanel" aria-labelledby="project-tab-activity" x-show="projectDrawerTab === 'activity'" class="space-y-4">
                    <div>
                        <h3 class="text-sm font-black text-[#071B33]">فعالیت‌های پروژه</h3>
                        <p class="text-[11px] leading-5 text-[#64748B] mt-1">تمام تغییرات پروژه، وظایف و اعضا</p>
                    </div>
                    <div class="relative">
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input x-model="activitySearch" @input.debounce.300ms="loadActivity(1)" class="w-full text-xs border-2 border-[#E2E8F0] rounded-xl pr-10 pl-3 py-3 focus:outline-none focus:border-[#18212B]" placeholder="جستجو در فعالیت‌ها…">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <select x-model="activityUserId" @change="loadActivity(1)" class="w-full text-[11px] border-2 border-[#E2E8F0] rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#18212B] bg-white">
                            <option value="">همه کاربران</option>
                            <template x-for="person in activityUsers" :key="person.id"><option :value="person.id" x-text="person.name"></option></template>
                        </select>
                        <select x-model="activityKind" @change="loadActivity(1)" class="w-full text-[11px] border-2 border-[#E2E8F0] rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#18212B] bg-white">
                            <option value="">همه رویدادها</option>
                            <template x-for="kind in activityKinds" :key="kind"><option :value="kind" x-text="activityKindLabel(kind)"></option></template>
                        </select>
                    </div>
                    <div x-show="activityLoading" class="py-8 text-center">
                        <svg class="animate-spin w-5 h-5 text-[#18212B] mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <p class="text-[11px] text-[#94A3B8] mt-2">در حال بارگذاری…</p>
                    </div>
                    <div x-show="!activityLoading" class="space-y-2">
                        <template x-for="item in activityItems" :key="item.id">
                            <div class="flex gap-3 p-3 rounded-xl border border-[#E2E8F0] hover:border-[#CBD5E1] transition-colors">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                     :class="{
                                         'bg-[#DCFCE7] text-[#16A34A]': item.kind.includes('added') || item.kind === 'task_created',
                                         'bg-[#F1F3F2] text-[#18212B]': item.kind.includes('changed'),
                                         'bg-[#FEF3C7] text-[#D97706]': item.kind.includes('moved'),
                                         'bg-[#FEE2E2] text-[#DC2626]': item.kind.includes('deleted') || item.kind.includes('removed'),
                                         'bg-[#F3E8FF] text-[#9333EA]': item.kind.includes('comment') || item.kind.includes('mentioned'),
                                     }">
                                    <span class="text-[10px] font-black" x-text="activityKindLabel(item.kind).slice(0, 1)"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] leading-5 text-[#334155]" x-html="highlightText(item.message || '', activitySearch)"></p>
                                    <p class="text-[10px] text-[#64748B] mt-1" x-text="item.actor"></p>
                                    <p class="text-[9px] text-[#94A3B8] mt-1" x-text="item.time || ''"></p>
                                </div>
                            </div>
                        </template>
                        <p x-show="activityLoading" role="status" class="text-sm">در حال بارگذاری…</p><div x-show="activityError" role="alert" class="text-sm text-red-700"><span x-text="activityError"></span><button type="button" @click="loadActivity()" class="min-h-11 px-3">تلاش دوباره</button></div>
                        <div x-show="activityItems.length === 0 && !activityLoading && !activityError" class="py-8 text-center">
                            <p class="text-[11px] text-[#94A3B8]">فعالیتی یافت نشد</p>
                        </div>
                    </div>
                    <div x-show="activityMeta.last_page > 1" class="flex items-center justify-between pt-2">
                        <button @click="loadActivity(activityMeta.current_page - 1)" :disabled="activityMeta.current_page <= 1 || activityLoading" class="text-[10px] font-bold text-[#64748B] disabled:opacity-40">قبلی</button>
                        <span class="text-[10px] text-[#94A3B8]" x-text="toPersianDigits(activityMeta.current_page) + ' از ' + toPersianDigits(activityMeta.last_page)"></span>
                        <button @click="loadActivity(activityMeta.current_page + 1)" :disabled="activityMeta.current_page >= activityMeta.last_page || activityLoading" class="text-[10px] font-bold text-[#64748B] disabled:opacity-40">بعدی</button>
                    </div>
                </section>
            </div>

            <footer class="border-t border-[#E2E8F0] p-4 bg-[#F8FAFC]">
                <button x-show="projectDrawerTab === 'settings'" @click="saveProjectSettings()" :disabled="projectSettingsSaving" class="w-full bg-[#18212B] hover:bg-[#000000] disabled:opacity-60 text-white text-xs font-black rounded-xl px-4 py-3">
                    <span x-text="projectSettingsSaving ? 'در حال ذخیره…' : 'ذخیره تغییرات'"></span>
                </button>
                <p x-show="projectDrawerTab === 'members'" class="text-[10px] text-[#64748B] text-center"><span x-text="projectMembers.length"></span> عضو در تیم پروژه</p>
            </footer>
        </aside>
    </div>

    {{-- Task modal --}}
    <div
        x-show="showModal"
        x-cloak
        x-transition:enter="transition-opacity ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="task-drawer-layer fixed inset-0 z-50 overflow-hidden"
        @click="requestCloseModal()"
        @keydown.escape.window="handleTaskEscape($event)"
        @resize.window="floatingMenuRevision++"
        x-effect="if (showModal) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open') }"
    >
        <div class="h-full min-h-0 flex items-stretch justify-start p-0">
            <div
                class="task-modal-shell relative"
                :class="editingTask ? 'task-modal-shell--editing' : 'task-modal-shell--create'"
                :style="modalAccentStyle()"
                x-transition:enter="transition-opacity ease-out duration-100"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                @click.stop
                role="dialog"
                aria-modal="true"
                aria-labelledby="task-modal-title"
                @keydown="trapModalFocus($event)"
            >
                <header class="task-modal-header task-workspace-header shrink-0">
                    <div class="task-workspace-identity">
                        <label id="task-modal-title" for="task-title" class="sr-only">عنوان وظیفه</label>
                        <textarea id="task-title" x-ref="taskTitle" x-model="form.title" rows="1" x-effect="form.title; showModal; $nextTick(() => { $el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 100) + 'px' })" :disabled="!canEdit || taskSaving" class="task-workspace-title" placeholder="عنوان وظیفه را بنویسید…" aria-required="true"></textarea>
                        <p class="task-workspace-context"><span>{{ $project->name }}</span><span x-show="editingTask" x-text="form.id"></span><span x-show="!editingTask">وظیفه جدید</span></p>
                    </div>
                    <button type="button" x-ref="taskDrawerClose" @click="requestCloseModal()" class="task-workspace-close" aria-label="بستن پنجره"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </header>
                <div x-show="form.isBlocked" x-cloak class="task-blocked-banner" role="status"><strong>این وظیفه مسدود است</strong><span x-text="form.blockedReason"></span></div>
                <div class="task-modal-body task-workspace-body" @scroll="floatingMenuRevision++">
                    <aside class="task-workspace-sidebar" aria-label="مشخصات و اقدامات وظیفه">
                        <button type="button" class="task-mobile-properties" @click="taskPropertiesOpen = !taskPropertiesOpen" :aria-expanded="taskPropertiesOpen" aria-controls="task-properties-content">
                            <span><strong x-text="columns.find(col => col.id === form.columnId)?.title || 'انتخاب ستون'"></strong><small x-text="(form.assignees.length ? form.assignees.join('، ') : 'بدون مسئول') + ' · ' + (form.dueDate ? formatDateInput(form.dueDate) : 'بدون سررسید')"></small></span>
                            <span x-text="taskPropertiesOpen ? 'بستن مشخصات −' : 'ویرایش مشخصات +' "></span>
                        </button>
                        <div id="task-properties-content" class="task-properties-content" :class="{ 'is-open': taskPropertiesOpen }">
                    {{-- Optional details --}}
                    <section class="task-modal-section task-properties-section" aria-labelledby="task-settings-title">
                        <div class="task-modal-section__heading">
                            <div class="task-modal-section__title" id="task-settings-title">مشخصات وظیفه</div>

                        </div>
                    <div class="task-settings-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-start">
                        {{-- Column --}}
                        <div>
                            <label for="task-column" class="board-field-label">ستون / وضعیت</label>
                            <select id="task-column" x-model="form.columnId" :disabled="!canEdit" class="w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#18212B] transition-colors bg-white disabled:bg-[#F1F5F9]">
                                <template x-for="col in columns" :key="col.id">
                                    <option :value="col.id" x-text="col.title"></option>
                                </template>
                            </select>
                            @if ($canEdit)
                            <button x-show="editingTask" type="button" @click="stateTask(form.workflowRole === 'done' ? 'reopen' : 'complete')" :disabled="taskSaving" class="task-complete-action" x-text="form.workflowRole === 'done' ? 'باز کردن دوباره' : '✓ انجام شد'"></button>
                            @endif
                        </div>
                    {{-- Assignees --}}
                        <div x-data="{ assigneeOpen: false, assigneeSearch: '' }" @click.away="assigneeOpen = false" class="relative">
                            <label class="board-field-label">مسئولین</label>
                            <div
                                x-ref="assigneeTrigger"
                                role="button" tabindex="0" :aria-expanded="assigneeOpen" aria-label="انتخاب مسئول"
                                @keydown.enter.prevent="if (canEdit) assigneeOpen = !assigneeOpen"
                                @keydown.space.prevent="if (canEdit) assigneeOpen = !assigneeOpen"
                                @click="if (canEdit) assigneeOpen = !assigneeOpen"
                                class="w-full min-h-[36px] border-2 border-[#E2E8F0] rounded-lg px-2.5 py-1.5 transition-colors bg-white flex flex-wrap items-center gap-1"
                                :class="canEdit ? 'cursor-pointer hover:border-[#CBD5E1]' : 'cursor-default bg-[#F1F5F9]'"
                            >
                                <template x-for="name in form.assignees" :key="name">
                                    <span class="inline-flex items-center gap-1 bg-[#F1F3F2] text-[#000000] text-[10px] font-bold px-2 py-0.5 rounded-md">
                                        <span x-text="name"></span>
                                        @if ($canEdit)
                                            <button @click.stop="removeAssignee(name)" class="hover:text-red-500 ml-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        @endif
                                    </span>
                                </template>
                                <span x-show="form.assignees.length === 0" class="text-xs text-[#CBD5E1]">انتخاب مسئول</span>
                                <svg class="w-3.5 h-3.5 text-[#94A3B8] mr-auto shrink-0" :class="assigneeOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <div
                                x-show="assigneeOpen"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="task-floating-menu bg-white border-2 border-[#E2E8F0] rounded-xl shadow-lg shadow-black/10 overflow-hidden"
                                :style="floatingMenuStyle($refs.assigneeTrigger, Math.max($refs.assigneeTrigger?.offsetWidth || 240, 280), 320)"
                            >
                                <div class="p-2 border-b border-[#F1F5F9]">
                                    <div class="relative">
                                        <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <input
                                            x-model="assigneeSearch"
                                            @keydown.escape="assigneeOpen = false"
                                            type="text"
                                            class="w-full text-xs border border-[#E2E8F0] rounded-lg pr-7 pl-2 py-1.5 focus:outline-none focus:border-[#18212B] transition-colors placeholder:text-[#CBD5E1]"
                                            placeholder="جستجو..."
                                            x-effect="if (assigneeOpen) $nextTick(() => $el.focus({ preventScroll: true }))"
                                        >
                                    </div>
                                </div>
                                <button
                                    @click="toggleAllAssignees()"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-[11px] font-semibold hover:bg-[#F8FAFC] transition-colors border-b border-[#F1F5F9]"
                                    :class="form.assignees.length === assignees.length ? 'text-[#18212B]' : 'text-[#64748B]'"
                                >
                                    <div class="w-4 h-4 rounded border-2 flex items-center justify-center transition-colors" :class="form.assignees.length === assignees.length ? 'border-[#18212B] bg-[#18212B]' : 'border-[#CBD5E1]'">
                                        <svg x-show="form.assignees.length === assignees.length" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <span x-text="form.assignees.length === assignees.length ? 'حذف همه' : 'انتخاب همه'"></span>
                                </button>
                                <div class="max-h-[180px] overflow-y-auto">
                                    <template x-for="name in filteredAssignees(assigneeSearch)" :key="name">
                                        <button
                                            @click="toggleAssignee(name)"
                                            class="w-full flex items-center gap-2.5 px-3 py-2 hover:bg-[#F8FAFC] transition-colors"
                                            :class="form.assignees.includes(name) ? 'bg-[#F1F3F2]/50' : ''"
                                        >
                                            <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors" :class="form.assignees.includes(name) ? 'border-[#18212B] bg-[#18212B]' : 'border-[#CBD5E1]'">
                                                <svg x-show="form.assignees.includes(name)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <div class="w-5 h-5 rounded-full bg-[#18212B] flex items-center justify-center shrink-0">
                                                <span class="text-[7px] text-white font-bold" x-text="name.charAt(0)"></span>
                                            </div>
                                            <span class="text-[11px] font-semibold" :class="form.assignees.includes(name) ? 'text-[#000000]' : 'text-[#475569]'" x-text="name"></span>
                                        </button>
                                    </template>
                                    <div x-show="filteredAssignees(assigneeSearch).length === 0" class="px-3 py-4 text-center">
                                        <p class="text-[11px] text-[#94A3B8]">نتیجه‌ای یافت نشد</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Due Date --}}
                        <div class="relative">
                            <label class="board-field-label">سررسید</label>
                            <div class="relative">
                                <input :value="formatDateInput(form.dueDate)" x-ref="dueDateInput" @click="if (canEdit) openJalaliDatePicker()" type="text" :disabled="!canEdit" readonly class="jalali-date-input w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg pr-9 pl-8 py-2 focus:outline-none transition-colors bg-white disabled:bg-[#F1F5F9]" placeholder="انتخاب تاریخ">
                                <svg class="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#94A3B8] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 4h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <button x-show="form.dueDate && canEdit" type="button" @click="clearJalaliDate()" class="absolute left-2 top-1/2 -translate-y-1/2 text-[#94A3B8] hover:text-red-500" aria-label="پاک کردن تاریخ">×</button>
                            </div>
                            <div x-show="jalaliDatePicker.open" x-cloak @click.outside="closeJalaliDatePicker()" class="jalali-picker task-floating-menu absolute right-0 top-full mt-2 w-[min(290px,calc(100vw-3rem))] rounded-xl border border-[#E2E8F0] bg-white p-3 shadow-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <button type="button" @click="changeJalaliMonth(1)" class="jalali-picker__nav" aria-label="ماه بعد">‹</button>
                                    <span class="text-xs font-black text-[#18212B]" x-text="jalaliMonthLabel()"></span>
                                    <button type="button" @click="changeJalaliMonth(-1)" class="jalali-picker__nav" aria-label="ماه قبل">›</button>
                                </div>
                                <div class="grid grid-cols-7 gap-1 mb-1 text-center">
                                    <template x-for="day in jalaliWeekdays" :key="day"><span class="text-[10px] font-bold text-[#94A3B8]" x-text="day"></span></template>
                                </div>
                                <div class="grid grid-cols-7 gap-1">
                                    <template x-for="(day, index) in jalaliCalendarDays()" :key="index">
                                        <button type="button" @click="day && selectJalaliDate(day)" :disabled="!day" class="jalali-picker__day" :class="[!day ? 'invisible' : '', day && isSelectedJalaliDay(day) ? 'jalali-picker__day--selected' : '', day && isTodayJalaliDay(day) ? 'jalali-picker__day--today' : '']" x-text="day ? toPersianDigits(day) : ''"></button>
                                    </template>
                                </div>
                                <button type="button" @click="selectTodayJalaliDate()" class="w-full mt-3 pt-2 border-t border-[#F1F5F9] text-[10px] font-bold text-[#64748B] hover:text-[#18212B]">امروز</button>
                            </div>
                            <div x-show="form.dueDate" class="task-due-time">
                                <label for="task-due-time">ساعت (اختیاری)</label>
                                <input id="task-due-time" type="time" x-model="form.dueTime" :disabled="!canEdit || !form.dueDate" aria-label="ساعت سررسید">
                                <button type="button" x-show="form.dueTime && canEdit" @click="form.dueTime = ''">بدون ساعت</button>
                            </div>
                            <p x-show="form.dueDate" class="task-due-timezone">به وقت {{ $workspace->timezone ?: 'Asia/Tehran' }}</p>
                            <p x-show="form.dueDate && isOverdue(form.dueDate, form.dueTime)" class="text-[10px] text-red-500 font-bold mt-1">سررسید گذشته</p>
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label class="board-field-label mb-2">اولویت</label>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="p in [{name:'بالا', color:'bg-red-500'}, {name:'متوسط', color:'bg-violet-500'}, {name:'پایین', color:'bg-slate-400'}]" :key="p.name">
                                    <label class="flex items-center gap-1.5 text-[10px] cursor-pointer px-2 py-1.5 rounded-lg border transition-all duration-150" :class="form.priority === p.name ? 'border-[#18212B] bg-[#F1F3F2]' : 'border-transparent hover:bg-white'">
                                        <input type="radio" :value="p.name" x-model="form.priority" :disabled="!canEdit" class="hidden">
                                        <span class="w-2 h-2 rounded-full" :class="p.color"></span>
                                        <span class="font-semibold" :class="form.priority === p.name ? 'text-[#000000]' : 'text-[#64748B]'" x-text="p.name"></span>
                                    </label>
                                </template>
                            </div>
                        </div>

                        {{-- Tags --}}
                        <div x-data="{ labelsOpen: false, tagManagerOpen: false, newTagName: '', newTagColor: '#8B5CF6', tagColors: [{ hex: '#8B5CF6', active: 'border-purple-400 bg-purple-50 text-purple-700', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-purple-200 hover:text-purple-500' }, { hex: '#475569', active: 'border-gray-500 bg-gray-100 text-gray-800', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-gray-300 hover:text-gray-700' }, { hex: '#F59E0B', active: 'border-amber-400 bg-amber-50 text-amber-700', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-amber-200 hover:text-amber-500' }, { hex: '#22C55E', active: 'border-green-400 bg-green-50 text-green-700', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-green-200 hover:text-green-500' }, { hex: '#EF4444', active: 'border-red-400 bg-red-50 text-red-700', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-red-200 hover:text-red-500' }, { hex: '#14B8A6', active: 'border-teal-400 bg-teal-50 text-teal-700', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-teal-200 hover:text-teal-500' }, { hex: '#EC4899', active: 'border-pink-400 bg-pink-50 text-pink-700', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-pink-200 hover:text-pink-500' }, { hex: '#3B82F6', active: 'border-blue-400 bg-blue-50 text-blue-700', inactive: 'border-[#F1F5F9] text-[#94A3B8] hover:border-blue-200 hover:text-blue-500' }] }">
                            <div class="flex items-center justify-between mb-2">
                                <label class="board-field-label mb-0">برچسب‌ها</label>
                                @if ($canEdit)
                                    <button type="button" @click="tagManagerOpen = !tagManagerOpen" class="text-[10px] font-bold text-[#64748B] hover:text-[#18212B] transition-colors" x-text="tagManagerOpen ? 'بستن' : 'مدیریت'"></button>
                                @endif
                            </div>
                            <div class="task-selected-labels">
                                <template x-for="name in form.tags" :key="name"><span x-text="name" :class="allTags.find(tag => tag.name === name)?.activeClass || ''"></span></template>
                                <span x-show="!form.tags.length" class="task-no-labels">بدون برچسب</span>
                            </div>
                            @if ($canEdit)
                            <button type="button" @click="labelsOpen = !labelsOpen" :aria-expanded="labelsOpen" class="task-label-picker-toggle" x-text="labelsOpen ? 'بستن انتخاب برچسب' : 'انتخاب برچسب +'"></button>
                            @endif
                            <div x-show="labelsOpen" x-cloak class="flex flex-wrap gap-1 task-label-options">

                                <template x-for="tag in allTags" :key="tag.name">
                                    <button type="button" @click="if (canEdit) toggleTag(tag.name)" :disabled="!canEdit" class="text-[9px] font-bold px-2 py-1 rounded-md border transition-all duration-150" :class="form.tags.includes(tag.name) ? tag.activeClass : tag.inactiveClass" x-text="tag.name"></button>
                                </template>
                            </div>
                            {{-- Tag Manager --}}
                            <div x-show="tagManagerOpen" x-transition class="mt-3 p-3 bg-[#F8FAFC] rounded-xl border border-[#E2E8F0] space-y-3">
                                <template x-for="(tag, idx) in allTags" :key="'mgr-' + tag.name">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-[#475569]" x-text="tag.name"></span>
                                        <button type="button" @click="const tagName = allTags[idx].name; allTags.splice(idx, 1); const ti = form.tags.indexOf(tagName); if (ti !== -1) form.tags.splice(ti, 1);" class="text-[10px] text-red-400 hover:text-red-600 transition-colors">حذف</button>
                                    </div>
                                </template>
                                <div class="border-t border-[#E2E8F0] pt-3 space-y-2">
                                    <input
                                        x-model="newTagName"
                                        type="text"
                                        class="w-full text-[11px] border border-[#E2E8F0] rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-[#18212B] transition-colors placeholder:text-[#CBD5E1]"
                                        placeholder="نام برچسب جدید..."
                                        @keydown.enter="if (newTagName.trim()) { allTags.push({ name: newTagName.trim(), activeClass: tagColors.find(c => c.hex === newTagColor)?.active || '', inactiveClass: tagColors.find(c => c.hex === newTagColor)?.inactive || '' }); newTagName = '' }"
                                    >
                                    <div class="flex items-center gap-1.5">
                                        <template x-for="tc in tagColors" :key="tc.hex">
                                            <button type="button" @click="newTagColor = tc.hex" class="w-5 h-5 rounded-full border-2 transition-all" :class="newTagColor === tc.hex ? 'border-[#18212B] scale-110' : 'border-transparent'" :style="'background-color:' + tc.hex"></button>
                                        </template>
                                    </div>
                                    <button type="button" @click="if (newTagName.trim()) { allTags.push({ name: newTagName.trim(), activeClass: tagColors.find(c => c.hex === newTagColor)?.active || '', inactiveClass: tagColors.find(c => c.hex === newTagColor)?.inactive || '' }); newTagName = '' }" class="w-full text-[10px] font-bold text-white bg-[#18212B] hover:bg-[#253342] rounded-lg py-1.5 transition-colors" :disabled="!newTagName.trim()">افزودن برچسب</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    </section>

                        @if ($canEdit)
                        <div x-show="editingTask" class="task-sidebar-actions">
                            <div x-data="{ actionsOpen: false }" @click.outside="actionsOpen = false" class="task-secondary-actions">
                                <button type="button" @click="actionsOpen = !actionsOpen" :aria-expanded="actionsOpen" class="task-more-action">سایر اقدامات ···</button>
                                <div x-show="actionsOpen" x-cloak class="task-actions-menu">
                                    <button type="button" @click="actionsOpen = false; stateTask(form.isBlocked ? 'unblock' : 'block')" :disabled="taskSaving" x-text="form.isBlocked ? 'رفع انسداد' : 'مسدود کردن'"></button>
                                    <button type="button" @click="actionsOpen = false; requestDeleteFromTaskModal()" :disabled="taskSaving" class="task-delete-action">حذف وظیفه</button>
                                </div>
                            </div>
                        </div>
                        @endif
                        </div>
                    </aside>
                    <main class="task-workspace-main" @scroll="floatingMenuRevision++">
                    <div x-show="showUnsavedWarning" x-cloak class="task-unsaved-warning" role="alert">
                        <p>تغییرات این وظیفه هنوز ذخیره نشده‌اند.</p>
                        <div><button type="button" @click="saveTask()">ذخیره</button><button type="button" @click="discardTaskChanges()">کنار گذاشتن</button><button type="button" @click="showUnsavedWarning = false">ادامه ویرایش</button></div>
                    </div>
                    {{-- Description --}}
                    <section class="task-modal-section task-modal-section--description" aria-labelledby="task-description-title">
                        <div class="task-modal-section__heading">
                            <div>
                                <h4 id="task-description-title" class="task-modal-section__title">توضیحات</h4>

                            </div>
                            <button x-show="canEdit && form.description && !editingDescription" type="button" @click="editTaskDescription()" class="task-section-action">ویرایش</button>
                        </div>
                        <p x-show="form.description && !editingDescription" class="task-description-preview" x-html="formatMentionText(form.description)"></p>
                        <button x-show="canEdit && !form.description && !editingDescription" type="button" @click="editTaskDescription()" class="task-empty-action">＋ افزودن توضیحات</button>
                        <textarea
                            x-show="editingDescription" x-cloak
                            id="task-description-input" x-ref="descriptionMentionTrigger"
                            x-model="form.description"
                            rows="5"
                            aria-label="توضیحات وظیفه"
                            :disabled="!canEdit"
                            class="w-full text-sm text-[#1A1D21] border-2 border-[#E2E8F0] rounded-xl px-3.5 py-3 focus:outline-none focus:border-[#111111] transition-colors resize-none leading-relaxed placeholder:text-[#CBD5E1]"
                            placeholder="توضیحات کارت را بنویسید..."
                            @input="handleMentionInput('description', $event)"
                            @keydown.down.prevent="moveMentionSelection(1)"
                            @keydown.up.prevent="moveMentionSelection(-1)"
                            @keydown.enter="if (mentionOpen) { $event.preventDefault(); selectActiveMention() }"
                            @keydown.escape="mentionOpen ? closeMentionMenu() : null"
                            @paste="handleAttachmentPaste($event, 'description')"
                        ></textarea>
                        <div x-show="mentionOpen && mentionField === 'description'" x-cloak class="task-floating-menu bg-white border border-[#D8E0EB] rounded-xl shadow-xl overflow-hidden" :style="floatingMenuStyle($refs.descriptionMentionTrigger, 360, 220)">
                                <template x-for="(person, index) in mentionResults" :key="person.id">
                                    <button @click="selectMention(person)" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-right" :class="mentionIndex === index ? 'bg-[#F1F3F2]' : 'hover:bg-[#F8FAFC]'">
                                        <span class="w-7 h-7 rounded-full bg-[#071B33] text-white flex items-center justify-center text-[9px] font-bold" x-text="person.name.charAt(0)"></span>
                                        <span class="text-[11px] font-bold text-[#111111]" x-text="person.name"></span>
                                    </button>
                                </template>
                        </div>
                        <p x-show="editingDescription" class="text-[10px] text-[#94A3B8] mt-1.5">برای اشاره به هم‌تیمی‌ها @ تایپ کنید.</p>
                        <button x-show="editingDescription" type="button" @click="editingDescription = false; closeMentionMenu()" class="task-section-action task-description-finish">پایان ویرایش</button>
                    </section>
                    {{-- Checklist stays visible independently of advanced details. --}}
                    <section x-show="canEdit || form.checklist.length" class="task-modal-section task-checklist-section" aria-labelledby="task-checklist-title">
                        <div class="task-checklist-heading">
                            <h4 id="task-checklist-title">چک‌لیست</h4>
                            <span x-show="form.checklist.length" x-text="checklistProgress()" aria-live="polite"></span>
                        </div>
                        <div x-show="form.checklist.length" class="task-checklist-progress" role="progressbar" :aria-valuenow="checklistPercent()" aria-valuemin="0" aria-valuemax="100" aria-label="پیشرفت چک‌لیست">
                            <span :style="'width:' + checklistPercent() + '%'"></span>
                        </div>
                        <div class="task-checklist-items">
                            <template x-for="(item, idx) in form.checklist" :key="idx">
                                <div class="task-checklist-item" :class="{ 'is-done': item.done }">
                                    <input type="checkbox" x-model="item.done" :disabled="!canEdit || taskSaving" :aria-label="'انجام شد: ' + item.text" class="task-checklist-checkbox">
                                    @if ($canEdit)
                                        <button x-show="editingCheckItemIndex !== idx" type="button" @click="startCheckItemEdit(idx)" :disabled="taskSaving" class="task-checklist-text" :aria-label="'ویرایش مورد: ' + item.text" x-text="item.text"></button>
                                        <input x-show="editingCheckItemIndex === idx" x-cloak :id="'checklist-edit-' + idx" x-model="checkItemDraft" @keydown.enter.prevent="if (!$event.isComposing) finishCheckItemEdit()" @keydown.escape.stop.prevent="cancelCheckItemEdit()" @blur="if ($event.relatedTarget) finishCheckItemEdit(idx)" :disabled="taskSaving" class="task-checklist-edit" aria-label="متن مورد چک‌لیست">
                                        <button type="button" @click="removeCheckItem(idx)" :disabled="taskSaving" class="task-checklist-delete" :aria-label="'حذف مورد: ' + item.text" title="حذف مورد">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 7h12M10 4h4M8 7l1 13h6l1-13M10 10v7m4-7v7"/></svg>
                                        </button>
                                    @else
                                        <span class="task-checklist-text" x-text="item.text"></span>
                                    @endif
                                </div>
                            </template>
                        </div>
                        @if ($canEdit)
                            <button x-show="!checklistComposerOpen && !form.checklist.length" type="button" @click="openChecklistComposer()" :disabled="taskSaving" class="task-checklist-start" aria-controls="checklist-composer" :aria-expanded="checklistComposerOpen">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                افزودن چک‌لیست
                            </button>
                            <div x-show="checklistComposerOpen || form.checklist.length" id="checklist-composer" class="task-checklist-composer">
                                <label class="sr-only" for="checklist-new-item">مورد جدید چک‌لیست</label>
                                <div>
                                    <input id="checklist-new-item" x-model="newCheckItem" @keydown.enter.prevent="if (!$event.isComposing) addCheckItem()" :disabled="taskSaving" type="text" placeholder="مثلاً بررسی نسخه موبایل" autocomplete="off">
                                    <button type="button" @click="addCheckItem()" :disabled="!newCheckItem.trim() || taskSaving">افزودن</button>
                                </div>
                                <p>برای افزودن مورد بعدی، Enter بزنید.</p>
                            </div>
                        @endif
                    </section>

                    {{-- Task file library --}}
                    <section class="task-modal-section task-files-section" aria-labelledby="task-attachments-title">
                        <div class="task-modal-section__heading">
                            <div><div class="task-modal-section__title" id="task-attachments-title">پیوست‌ها</div></div>
                            <span class="text-[10px] text-[#94A3B8]" x-text="toPersianDigits(descriptionAttachments().length) + ' فایل'"></span>
                        </div>
                        @if ($canEdit)
                        <div
                            class="mt-3 rounded-xl border-2 border-dashed px-3 py-3 transition-colors"
                            :class="attachmentDragTarget === 'description' ? 'border-[#0069D9] bg-[#F0F8FF]' : 'border-[#D8E0EB] bg-[#FAFCFE]'"
                            @dragover.prevent="attachmentDragTarget = 'description'"
                            @dragleave.prevent="attachmentDragTarget = null"
                            @drop.prevent="attachmentDragTarget = null; queueAttachmentFiles($event.dataTransfer.files, 'description')"
                        >
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-[11px] font-bold text-[#334155]">افزودن فایل</p>
                                    <p class="mt-0.5 text-[9px] text-[#94A3B8]">فایل‌ها را رها کنید، تصویر را بچسبانید یا تا ۱۰ فایل انتخاب کنید.</p>
                                </div>
                                <button type="button" @click="$refs.descriptionFiles.click()" class="rounded-lg border border-[#BFD8EC] bg-white px-3 py-1.5 text-[10px] font-bold text-[#111111]">افزودن فایل</button>
                                <input x-ref="descriptionFiles" type="file" multiple class="hidden" @change="queueAttachmentFiles($event.target.files, 'description'); $event.target.value = ''">
                            </div>
                            <div x-show="pendingDescriptionFiles.length" x-cloak class="mt-3 grid gap-2 sm:grid-cols-2">
                                <template x-for="item in pendingDescriptionFiles" :key="item.localId">
                                    <div class="flex min-w-0 items-center gap-2 rounded-lg border border-[#E2E8F0] bg-white p-2">
                                        <button type="button" @click="openAttachmentPreview(item)" class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#F1F5F9] text-[9px] font-black text-[#64748B]">
                                            <img x-show="item.category === 'image'" :src="item.previewUrl" class="h-full w-full object-cover" alt="">
                                            <span x-show="item.category !== 'image'" x-text="attachmentLabel(item)"></span>
                                        </button>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[10px] font-bold text-[#334155]" x-text="item.name"></p>
                                            <p class="text-[9px] text-[#94A3B8]" x-text="attachmentStatusText(item)"></p>
                                            <div x-show="item.status === 'uploading'" class="mt-1 h-1 overflow-hidden rounded bg-[#E2E8F0]"><div class="h-full bg-[#0069D9]" :style="`width:${item.progress}%`"></div></div>
                                        </div>
                                        <button type="button" @click="removePendingAttachment(item, 'description')" class="shrink-0 text-[9px] font-bold text-red-500" x-text="item.status === 'uploading' ? 'لغو' : 'حذف'"></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        @endif
                        <div x-show="descriptionAttachments().length" class="mb-3 flex flex-wrap gap-1.5">
                            <template x-for="filter in attachmentFilters" :key="filter.value"><button type="button" @click="attachmentFilter = filter.value" class="rounded-full border px-2.5 py-1 text-[9px] font-bold" :class="attachmentFilter === filter.value ? 'border-[#18212B] bg-[#18212B] text-white' : 'border-[#E2E8F0] bg-white text-[#64748B]'" x-text="filter.label"></button></template>
                        </div>
                        <div class="grid gap-2 sm:grid-cols-2">
                            <template x-for="attachment in filteredAttachments().filter(item => item.context !== 'comment')" :key="attachment.id">
                                <div class="flex min-w-0 items-center gap-2.5 border border-[#E8EBE9] rounded-xl p-2.5">
                                    <button type="button" @click="openAttachmentPreview(attachment)" class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#F1F5F9] text-[9px] font-black text-[#64748B]">
                                        <img x-show="attachment.category === 'image'" :src="attachment.previewUrl" class="h-full w-full object-cover" alt="">
                                        <span x-show="attachment.category !== 'image'" x-text="attachmentLabel(attachment)"></span>
                                    </button>
                                    <div class="min-w-0 flex-1"><p class="truncate text-[10px] font-bold text-[#334155]" x-text="attachment.name"></p><p class="mt-0.5 text-[8px] text-[#94A3B8]"><span x-text="attachment.context === 'comment' ? 'گفتگو' : 'توضیحات'"></span> · <span x-text="formatFileSize(attachment.size)"></span></p><div class="mt-1 flex gap-2"><button x-show="attachment.previewable" type="button" @click="openAttachmentPreview(attachment)" class="text-[8px] font-bold text-[#0069D9]">پیش‌نمایش</button><a :href="attachment.downloadUrl" class="text-[8px] font-bold text-[#334155]">دانلود</a>@if ($canEdit)<button type="button" @click="deleteAttachment(attachment)" class="text-[8px] font-bold text-red-500">حذف</button>@endif</div></div>
                                </div>
                            </template>
                            <p x-show="descriptionAttachments().length === 0" class="text-[11px] text-[#94A3B8]">فایلی پیوست نشده است.</p>
                            <p x-show="descriptionAttachments().length && filteredAttachments().filter(item => item.context !== 'comment').length === 0" class="text-[11px] text-[#94A3B8]">فایلی در این دسته وجود ندارد.</p>
                        </div>
                    </section>
                    {{-- Comments --}}
                    <section x-show="editingTask" x-cloak class="task-modal-section" aria-labelledby="task-comments-title">
                        <div class="task-modal-section__heading">
                            <div class="task-modal-section__title" id="task-comments-title">گفتگو</div>
                            <span x-show="form.comments.length" class="text-[10px] text-[#94A3B8]" x-text="toPersianDigits(form.comments.length) + ' پیام'"></span>
                        </div>
                        @if ($canEdit)
                        <div class="mt-3 flex gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-[#18212B] flex items-center justify-center shrink-0 shadow-sm">
                                <span class="text-[9px] text-white font-bold">ش</span>
                            </div>
                            <div class="flex-1 relative">
                                <textarea
                                    x-ref="commentMentionTrigger"
                                    x-model="newComment"
                                    rows="2"
                                    class="w-full text-sm border-2 border-[#E2E8F0] rounded-xl px-3 py-2 focus:outline-none focus:border-[#18212B] transition-colors resize-none placeholder:text-[#CBD5E1]"
                                    placeholder="پیام بنویسید..."
                                    @input="handleMentionInput('comment', $event)"
                                    @keydown.down.prevent="moveMentionSelection(1)"
                                    @keydown.up.prevent="moveMentionSelection(-1)"
                                    @keydown.enter="if (mentionOpen) { $event.preventDefault(); selectActiveMention() }"
                                    @keydown.escape="closeMentionMenu()"
                                    @keydown.meta.enter="addComment()"
                                    @keydown.ctrl.enter="addComment()"
                                ></textarea>
                                <div x-show="mentionOpen && mentionField === 'comment'" x-cloak class="task-floating-menu bg-white border border-[#D8E0EB] rounded-xl shadow-xl overflow-hidden" :style="floatingMenuStyle($refs.commentMentionTrigger, Math.max($refs.commentMentionTrigger?.offsetWidth || 280, 320), 220)">
                                    <template x-for="(person, index) in mentionResults" :key="person.id">
                                        <button @click="selectMention(person)" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-right" :class="mentionIndex === index ? 'bg-[#F1F3F2]' : 'hover:bg-[#F8FAFC]'">
                                            <span class="w-7 h-7 rounded-full bg-[#071B33] text-white flex items-center justify-center text-[9px] font-bold" x-text="person.name.charAt(0)"></span>
                                            <span class="text-[11px] font-bold text-[#111111]" x-text="person.name"></span>
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[10px] text-[#94A3B8] mt-1">برای اشاره به هم‌تیمی‌ها @ تایپ کنید.</p>
                                <div class="mt-2 flex flex-wrap gap-2" x-show="pendingCommentFiles.length">
                                    <template x-for="item in pendingCommentFiles" :key="item.localId">
                                        <div class="flex max-w-full items-center gap-2 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] px-2 py-1.5">
                                            <button type="button" @click="openAttachmentPreview(item)" class="text-[9px] font-black text-[#64748B]" x-text="attachmentLabel(item)"></button>
                                            <span class="max-w-36 truncate text-[9px] font-bold text-[#334155]" x-text="item.name"></span>
                                            <button type="button" @click="removePendingAttachment(item, 'comment')" class="text-[9px] text-red-500">×</button>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between mt-1.5">
                                    <button type="button" @click="$refs.commentFiles.click()" class="text-[10px] font-bold text-[#64748B] hover:text-[#18212B]">＋ افزودن فایل</button>
                                    <input x-ref="commentFiles" type="file" multiple class="hidden" @change="queueAttachmentFiles($event.target.files, 'comment'); $event.target.value = ''">
                                    <button x-show="newComment.trim() || pendingCommentFiles.length" @click="addComment()" :disabled="commentPosting" class="text-[10px] font-bold text-white bg-[#18212B] hover:bg-[#000000] disabled:opacity-60 px-3 py-1 rounded-lg transition-all" x-text="commentPosting ? 'در حال ارسال…' : 'ارسال (Ctrl+Enter)'"></button>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="task-comment-history space-y-3">
                            <template x-for="(comment, idx) in form.comments" :key="idx">
                                <div class="flex gap-3">
                                    <div class="w-7 h-7 rounded-full bg-[#18212B] flex items-center justify-center shrink-0 shadow-sm">
                                        <span class="text-[9px] text-white font-bold" x-text="comment.author.charAt(0)"></span>
                                    </div>
                                    <div class="flex-1 bg-[#F8FAFC] rounded-xl px-3.5 py-2.5 border border-[#F1F5F9]">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[11px] font-bold text-[#1A1D21]" x-text="comment.author"></span>
                                            <span class="text-[9px] text-[#94A3B8]" x-text="comment.time"></span>
                                        </div>
                                        <p class="text-[12px] text-[#475569] leading-relaxed" x-html="formatMentionText(comment.text)"></p>
                                        <p x-show="!comment.text && !(comment.attachments || []).length" class="text-[10px] text-[#94A3B8]">پیوست حذف شده است.</p>
                                        <div x-show="(comment.attachments || []).length" class="mt-2 grid gap-2 sm:grid-cols-2">
                                            <template x-for="attachment in (comment.attachments || [])" :key="attachment.id">
                                                <button type="button" @click="openAttachmentPreview(attachment)" class="flex min-w-0 items-center gap-2 rounded-lg border border-[#E2E8F0] bg-white p-2 text-right">
                                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-md bg-[#F1F5F9] text-[8px] font-black text-[#64748B]">
                                                        <img x-show="attachment.category === 'image'" :src="attachment.previewUrl" class="h-full w-full object-cover" alt="">
                                                        <span x-show="attachment.category !== 'image'" x-text="attachmentLabel(attachment)"></span>
                                                    </span>
                                                    <span class="min-w-0"><span class="block truncate text-[9px] font-bold text-[#334155]" x-text="attachment.name"></span><span class="text-[8px] text-[#94A3B8]" x-text="formatFileSize(attachment.size)"></span></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>

                    </main>
                </div>

                {{-- Footer --}}
                <div class="task-modal-footer sticky bottom-0 bg-white border-t border-[#E2E8F0] px-4 md:px-6 py-3 flex items-center justify-between">
                    @if ($canEdit)
                        <div class="task-save-actions flex items-center gap-2">
                            <p x-show="taskError" x-text="taskError" class="text-[10px] leading-5 text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2" role="alert"></p>
                            <button type="button" @click="requestCloseModal()" :disabled="taskSaving" class="task-modal-cancel text-[11px] font-bold text-[#64748B] border-2 border-[#E2E8F0] hover:bg-[#F8FCFF] disabled:opacity-60 px-5 py-2.5 rounded-xl transition-colors">انصراف</button>
                            <button type="button" @click="saveTask()" :disabled="taskSaving" :aria-busy="taskSaving" class="task-modal-primary text-[11px] font-bold text-white bg-[#18212B] hover:bg-[#253342] disabled:opacity-60 disabled:cursor-wait px-5 py-2.5 rounded-xl shadow-sm transition-colors">
                                <span x-text="taskSaving ? 'در حال ذخیره…' : (editingTask ? 'ذخیره تغییرات' : 'ایجاد وظیفه')"></span>
                            </button>
                        </div>
                    @else
                        <div class="flex-1 text-[11px] leading-5 text-[#64748B] bg-white border border-[#E2E8F0] rounded-lg px-3 py-2.5">شما دسترسی مشاهده دارید و نمی‌توانید این وظیفه را تغییر دهید.</div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Attachment preview --}}
    <div x-show="attachmentPreview" x-cloak class="fixed inset-0 z-[90] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-[#07111F]/75 backdrop-blur-sm" @click="closeAttachmentPreview()"></div>
        <div class="relative flex max-h-[90vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-label="پیش‌نمایش فایل" @click.stop>
            <div class="flex items-center justify-between gap-3 border-b border-[#E2E8F0] px-4 py-3">
                <div class="min-w-0"><p class="truncate text-xs font-black text-[#18212B]" x-text="attachmentPreview?.name"></p><p class="mt-0.5 text-[9px] text-[#94A3B8]" x-text="formatFileSize(attachmentPreview?.size)"></p></div>
                <div class="flex items-center gap-2"><a x-show="attachmentPreview?.downloadUrl" :href="attachmentPreview?.downloadUrl" class="rounded-lg border border-[#D8E0EB] px-3 py-1.5 text-[10px] font-bold text-[#334155]">دانلود</a><button type="button" @click="closeAttachmentPreview()" class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#F1F5F9] text-lg text-[#64748B]" aria-label="بستن">×</button></div>
            </div>
            <div class="flex min-h-64 flex-1 items-center justify-center overflow-auto bg-[#F8FAFC] p-4">
                <template x-if="attachmentPreview?.category === 'image'"><img :src="attachmentPreview?.previewUrl" class="max-h-[72vh] max-w-full rounded-lg object-contain" :alt="attachmentPreview?.name"></template>
                <template x-if="attachmentPreview?.category === 'pdf' || attachmentPreview?.category === 'text'"><iframe :src="attachmentPreview?.previewUrl" class="h-[72vh] w-full rounded-lg border-0 bg-white" title="پیش‌نمایش فایل"></iframe></template>
                <template x-if="attachmentPreview?.category === 'audio'"><audio :src="attachmentPreview?.previewUrl" controls class="w-full max-w-xl"></audio></template>
                <template x-if="attachmentPreview?.category === 'video'"><video :src="attachmentPreview?.previewUrl" controls class="max-h-[72vh] max-w-full rounded-lg bg-black"></video></template>
                <template x-if="attachmentPreview && !['image','pdf','text','audio','video'].includes(attachmentPreview.category)"><div class="text-center"><div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-white text-lg font-black text-[#64748B] shadow-sm" x-text="attachmentLabel(attachmentPreview)"></div><p class="mt-4 text-xs font-bold text-[#334155]">برای این نوع فایل پیش‌نمایش مرورگر موجود نیست.</p></div></template>
            </div>
        </div>
    </div>

    {{-- Add Column Modal --}}
    <div x-show="showColumnModal" x-cloak x-transition:enter="transition-opacity ease-out duration-100" x-transition:leave="transition-opacity ease-in duration-75" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.escape.window="closeColumnModal()">
        <div class="column-modal-backdrop absolute inset-0" @click="closeColumnModal()"></div>
        <form @submit.prevent="addColumn()" class="board-form-modal relative bg-white w-full max-w-md rounded-xl shadow-lg overflow-hidden" @click.stop role="dialog" aria-modal="true" aria-labelledby="column-modal-title" @keydown="trapModalFocus($event)">
            <div class="p-6 border-b border-[#F1EFEA]"><h4 id="column-modal-title" class="text-base font-black text-[#18212B]" x-text="columnEditingId ? 'ویرایش ستون' : 'افزودن ستون'"></h4><p class="text-xs text-[#64748B] mt-1" x-text="columnEditingId ? 'نام، رنگ و ظرفیت این مرحله را تنظیم کنید.' : 'یک مرحله جدید برای جریان کار پروژه بسازید.'"></p></div>
            <button type="button" @click="closeColumnModal()" class="modal-close-button absolute left-4 top-4" aria-label="بستن پنجره"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg></button>
            <div class="p-6 space-y-5"><div><label class="block text-[11px] font-black text-[#64748B] mb-2">نام ستون</label><input x-ref="columnTitle" x-model="columnFormTitle" type="text" maxlength="100" required class="w-full h-12 rounded-xl border-2 border-[#E8EBE9] px-4 text-sm font-bold text-[#18212B] outline-none focus:border-[#18212B]" placeholder="مثلاً آماده انتشار"></div><div><label class="block text-[11px] font-black text-[#64748B] mb-2">ظرفیت کار هم‌زمان <span class="font-normal text-[#94A3B8]">(اختیاری)</span></label><input x-model="columnFormWipLimit" type="number" min="1" max="999" class="w-full h-11 rounded-xl border-2 border-[#E8EBE9] px-4 text-sm font-bold text-[#18212B] outline-none focus:border-[#18212B]" placeholder="مثلاً ۵"></div><fieldset class="column-color-picker">
                    <legend>رنگ ستون</legend>
                    <p>این رنگ برای پس‌زمینه ستون و نشان کارت‌ها استفاده می‌شود.</p>
                    <div class="column-color-picker__presets">
                        <template x-for="color in columnColors" :key="color.hex">
                            <button type="button" @click="columnFormColor = color.hex" :aria-label="color.name" :title="color.name" :aria-pressed="columnFormColor.toLowerCase() === color.hex.toLowerCase()" :class="{ 'is-selected': columnFormColor.toLowerCase() === color.hex.toLowerCase() }" :style="'--swatch-color:' + color.hex"><svg x-show="columnFormColor.toLowerCase() === color.hex.toLowerCase()" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></button>
                        </template>
                    </div>
                    <label class="column-color-picker__custom" for="column-custom-color"><span>رنگ دلخواه</span><input id="column-custom-color" type="color" x-model="columnFormColor"><code x-text="columnFormColor.toUpperCase()"></code></label>
                    <div class="column-color-preview" :style="columnStyle({ dotHex: columnFormColor })" aria-label="پیش‌نمایش رنگ ستون"><div><i></i><strong x-text="columnFormTitle || 'ستون جدید'"></strong><small>۲</small></div><span>پیش‌نمایش یک وظیفه در این ستون</span></div>
                </fieldset><p x-show="columnError" x-text="columnError" class="text-[10px] leading-5 text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2" role="alert"></p></div>
            <div class="flex gap-2.5 px-6 pb-6"><button type="button" @click="closeColumnModal()" :disabled="columnSaving" class="flex-1 h-11 rounded-xl border-2 border-[#E8EBE9] text-xs font-bold text-[#64748B] disabled:opacity-50">انصراف</button><button type="submit" :disabled="columnSaving" :aria-busy="columnSaving" class="flex-1 h-11 rounded-xl bg-[#18212B] text-white text-xs font-black hover:bg-[#253342] disabled:opacity-60 disabled:cursor-wait" x-text="columnSaving ? 'در حال ذخیره…' : (columnEditingId ? 'ذخیره تغییرات' : 'افزودن ستون')"></button></div>
        </form>
    </div>

    {{-- Delete Column Confirmation Modal --}}
    <div x-show="showColumnDeleteModal" x-cloak x-transition:enter="transition-opacity ease-out duration-100" x-transition:leave="transition-opacity ease-in duration-75" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.escape.window="showColumnDeleteModal = false">
        <div class="absolute inset-0 bg-[#18212B]/55" @click="showColumnDeleteModal = false"></div>
        <div class="board-confirm-modal relative bg-white w-full max-w-sm rounded-xl shadow-lg overflow-hidden" @click.stop role="dialog" aria-modal="true" aria-label="تأیید حذف ستون" @keydown="trapModalFocus($event)">
            <button type="button" @click="showColumnDeleteModal = false" class="modal-close-button absolute left-4 top-4" aria-label="بستن پنجره"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg></button>
            <div class="p-6 text-center"><div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4"><svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.8" d="M12 9v3m0 4h.01M5.2 19h13.6c1.5 0 2.4-1.6 1.65-2.9L13.65 4.2a1.9 1.9 0 0 0-3.3 0L3.55 16.1C2.8 17.4 3.7 19 5.2 19Z"/></svg></div><h4 class="text-sm font-black text-[#18212B]">حذف ستون «<span x-text="columnDeleteTarget.title"></span>»؟</h4><p class="text-xs leading-6 text-[#64748B] mt-2 mb-5">این ستون و <span class="font-black text-red-500" x-text="columnDeleteTarget.taskCount"></span> وظیفه داخل آن حذف می‌شوند و قابل بازگشت نیستند.</p><p x-show="columnError" x-text="columnError" class="text-[10px] leading-5 text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2 mb-3" role="alert"></p><div class="flex gap-2.5"><button @click="showColumnDeleteModal = false" :disabled="columnDeleting" class="flex-1 h-11 rounded-xl border-2 border-[#E8EBE9] text-xs font-bold text-[#64748B] disabled:opacity-50">انصراف</button><button @click="deleteColumn()" :disabled="columnDeleting" :aria-busy="columnDeleting" class="flex-1 h-11 rounded-xl bg-red-500 hover:bg-red-600 disabled:opacity-60 text-white text-xs font-black" x-text="columnDeleting ? 'در حال حذف…' : 'حذف ستون'"></button></div></div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div
        x-show="showDeleteModal"
        x-cloak
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
        <div class="board-confirm-modal relative bg-white w-full max-w-sm rounded-xl shadow-lg overflow-hidden" @click.stop role="dialog" aria-modal="true" aria-label="تأیید حذف وظیفه" @keydown="trapModalFocus($event)">
            <button type="button" @click="showDeleteModal = false" class="modal-close-button absolute left-4 top-4" aria-label="بستن پنجره"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg></button>
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-[#1A1D21] mb-1">حذف وظیفه</h4>
                <p class="text-xs text-[#64748B] mb-5">آیا از حذف این وظیفه مطمئن هستید؟ این عمل قابل بازگشت نیست.</p>
                <div class="flex gap-2.5">
                    <button @click="showDeleteModal = false" class="flex-1 text-xs font-semibold text-[#64748B] hover:text-[#1A1D21] px-4 py-2.5 rounded-xl border-2 border-[#E2E8F0] hover:border-[#CBD5E1] transition-all">انصراف</button>
                    <button @click="deleteTask()" :disabled="taskDeleting" :aria-busy="taskDeleting" class="flex-1 text-xs font-bold text-white bg-red-500 hover:bg-red-600 disabled:opacity-60 disabled:cursor-wait px-4 py-2.5 rounded-xl shadow-md shadow-red-500/25 transition-all active:scale-[0.97]" x-text="taskDeleting ? 'در حال حذف…' : 'حذف'"></button>
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
        :role="toast.type === 'error' ? 'alert' : 'status'"
        :aria-live="toast.type === 'error' ? 'assertive' : 'polite'"
    >
        <div class="flex items-center gap-2 bg-[#1A1D21] text-white text-xs font-medium px-4 py-2.5 rounded-xl shadow-lg shadow-black/20">
            <svg x-show="toast.type === 'success'" aria-hidden="true" class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span x-show="toast.type === 'error'" class="text-red-300" aria-hidden="true">!</span><span x-text="toast.message"></span><button type="button" @click="toast.show = false" aria-label="بستن پیام" class="min-w-11 min-h-11">×</button>
        </div>
    </div>

    @php($boardRealtimeSnapshotUrl = route('board.realtime.snapshot', [$workspace->slug, $project->slug], false))
    <script>
        function board() {
            const serverColumns = @json($columnsData);

            const serverMembers = @json($membersData);
            const serverWorkspacePeople = @json($workspacePeopleData);

            return {
                canEdit: @json($canEdit),
                canManageProject: @json($canManageProject),
                projectId: @json($project->id),
                projectBoardStyleDefault: @json($boardStyle ?? 'simple'),
                boardStyle: @json($boardStyle ?? 'simple'),
                mobileActionsOpen: false,
                openColumnMenuId: null,
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
                columnMovePending: false,
                taskSaving: false,
                taskError: '',
                columnSaving: false,
                columnError: '',
                taskDeleting: false,
                columnDeleting: false,
                modalLastFocused: null,
                modalSnapshot: null,
                floatingMenuRevision: 0,
                realtimeRefresher: null,
                realtimeDragActive: false,
                pendingRealtimeSnapshot: null,
                showModal: false,
                showUnsavedWarning: false,
                extraTaskDetailsOpen: false,
                quickComposerColumnId: null,
                quickTaskTitle: '',
                quickTaskSaving: false,
                showDeleteModal: false,
                showColumnModal: false,
                showColumnDeleteModal: false,
                projectDrawerOpen: @json($errors->has('confirmation_name')),
                projectDrawerTab: 'settings',
                confirmingDeletion: @json($errors->has('confirmation_name')),
                confirmationName: @json(old('confirmation_name', '')),
                projectSettingsError: '',
                projectBaseline: '',
                projectBaselineVersion: @json((int) $project->edit_version),
                projectLastFocused: null,
                projectState: { name: @json($project->name), key: @json($project->key), isActive: @json((bool) $project->is_active), version: @json((int) $project->edit_version) },
                initialized: false,
                destroyed: false,
                snapshotState: 'current',
                connectionState: 'connecting',
                lastSnapshotAt: '',
                mutationSnapshot: null,
                mutationTimer: null,
                projectMemberSearch: '',
                boardSearchQuery: '',
                boardSearchOpen: false,
                filterPanelOpen: false,
                filterByAssignee: [],
                filterByPriority: [],
                filterByTag: [],
                filterByDue: '',
                selectedTaskIds: [],
                bulkAction: '',
                bulkValue: '',
                bulkLoading: false,
                activityTab: 'members',
                activitySearch: '',
                activityItems: [],
                activityLoading: false,
                activityError: '',
                activityUserId: '',
                activityKind: '',
                activityUsers: [],
                activityKinds: [],
                activityMeta: { current_page: 1, last_page: 1, total: 0 },
                projectMemberSaving: null,
                projectSettingsSaving: false,
                cycleLength: @json($project->cycle_length_weeks ?? ''),
                activeCycle: @json($activeCycle),
                editingTask: null,
                editingDescription: false,
                taskPropertiesOpen: false,
                descriptionBeforeEdit: '',
                deleteTarget: { columnId: null, taskId: null },
                columnDeleteTarget: { id: null, title: '', taskCount: 0 },
                columnFormTitle: '',
                columnFormColor: '#8B938E',
                columnFormWipLimit: '',
                columnEditingId: null,
                toast: { show: false, message: '', type: 'success' },
                newCheckItem: '',
                checklistComposerOpen: false,
                editingCheckItemIndex: null,
                checkItemDraft: '',
                newComment: '',
                commentPosting: false,
                attachmentUploading: false,
                attachmentDragTarget: null,
                attachmentPreview: null,
                attachmentFilter: 'all',
                attachmentFilters: [
                    { value: 'all', label: 'همه' }, { value: 'image', label: 'تصاویر' },
                    { value: 'document', label: 'اسناد' }, { value: 'media', label: 'رسانه' },
                    { value: 'archive', label: 'بایگانی' },
                ],
                pendingDescriptionFiles: [],
                pendingCommentFiles: [],
                mentionOpen: false,
                showTagManager: false,
                newTagName: '',
                newTagColor: '#8B5CF6',
                tagColors: ['#8B5CF6', '#475569', '#F59E0B', '#22C55E', '#EF4444', '#14B8A6', '#EC4899', '#3B82F6'],
                mentionField: null,
                mentionQuery: '',
                mentionResults: [],
                mentionIndex: 0,
                mentionStart: null,
                mentionCursor: null,
                form: { id: '', title: '', description: '', priority: 'متوسط', assignees: [], columnId: '', dueDate: '', dueTime: '', tags: [], checklist: [], comments: [], attachments: [], isBlocked: false, blockedReason: '', workflowRole: '' },
                workspaceTimezone: @json($workspace->timezone ?: 'Asia/Tehran'),
                jalaliDatePicker: { open: false, year: 1400, month: 1 },
                jalaliWeekdays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],

                projectMembers: serverMembers,
                workspacePeople: serverWorkspacePeople,
                assignees: serverMembers.map(member => member.name),
                projectForm: {
                    name: @json($project->name),
                    key: @json($project->key),
                    description: @json($project->description ?? ''),
                    board_style: @json($boardStyle ?? 'simple'),
                },

                allTags: [
                    { name: 'طراحی', activeClass: 'border-purple-400 bg-purple-50 text-purple-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-purple-200 hover:text-purple-500' },
                    { name: 'توسعه', activeClass: 'border-gray-500 bg-gray-100 text-gray-800', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-gray-300 hover:text-gray-700' },
                    { name: 'بک‌اند', activeClass: 'border-amber-400 bg-amber-50 text-amber-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-amber-200 hover:text-amber-500' },
                    { name: 'فرانت‌اند', activeClass: 'border-green-400 bg-green-50 text-green-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-green-200 hover:text-green-500' },
                    { name: 'باگ', activeClass: 'border-red-400 bg-red-50 text-red-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-red-200 hover:text-red-500' },
                    { name: 'بهبود', activeClass: 'border-teal-400 bg-teal-50 text-teal-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-teal-200 hover:text-teal-500' },
                ],

                customTags: @json($customTags ?? []),

                boardCompact: false,
                columnColors: [
                    { name: 'خاکستری گرم', hex: '#8B938E', surface: '#ECEBE6' },
                    { name: 'آبی آرام', hex: '#7183A3', surface: '#E7EAF0' },
                    { name: 'سبز جنگلی', hex: '#4E6B5C', surface: '#E7EEE9' },
                    { name: 'سبز مریم‌گلی', hex: '#77A98E', surface: '#EDF1EC' },
                    { name: 'شنی', hex: '#AB8D64', surface: '#EEE8DF' },
                    { name: 'بنفش ملایم', hex: '#9581A5', surface: '#EDE8F1' },
                    { name: 'آجری', hex: '#9A4A3A', surface: '#F5ECE8' },
                    { name: 'فیروزه‌ای', hex: '#588D91', surface: '#E6EFF0' },
                ],

                columnStyle(column) {
                    const candidate = column.dotHex || '#8B938E';
                    const accent = /^#[0-9a-f]{6}$/i.test(candidate) ? candidate.toUpperCase() : '#8B938E';
                    const preset = this.columnColors.find(color => color.hex === accent);
                    const paper = [247, 245, 239];
                    const surface = preset?.surface || '#' + paper.map((channel, index) => {
                        const color = parseInt(accent.slice(1 + index * 2, 3 + index * 2), 16);
                        return Math.round(channel * 0.86 + color * 0.14).toString(16).padStart(2, '0');
                    }).join('');
                    return `--column-accent:${accent};--column-surface:${surface};`;
                },

                boardProgress() {
                    const cycleIds = this.activeCycle ? new Set(this.activeCycle.taskIds.map(Number)) : null;
                    const tasks = this.columns.flatMap(column => column.tasks.map(task => ({ task, done: column.workflowRole === 'done' })));
                    const included = cycleIds ? tasks.filter(item => cycleIds.has(Number(item.task.dbId))) : tasks;
                    return included.length ? Math.round(included.filter(item => item.done).length / included.length * 100) : 0;
                },

                cycleDaysRemaining() {
                    if (!this.activeCycle) return 0;
                    return Math.max(0, Math.ceil((Date.parse(this.activeCycle.endsOn) - Date.parse(this.workspaceNowParts().date)) / 86400000));
                },

                columns: serverColumns.map(column => Object.assign({}, column, { collapsed: false })),
                sortableInstances: [],
                columnSortableInstances: [],

                totalTasks() { return this.columns.reduce((sum, col) => sum + col.tasks.length, 0); },

                collapsedColumnStyle(column) {
                    const color = column.dotHex || '#94A3B8';
                    return `background: ${color};`;
                },

                filteredTasks(column) {
                    return column.tasks.filter(task => {
                        const matchesAssignee = this.filterByAssignee.length === 0 ||
                            (task.assignees || []).some(a => this.filterByAssignee.includes(a));
                        const matchesPriority = this.filterByPriority.length === 0 ||
                            this.filterByPriority.includes(task.priority);
                        const matchesTag = this.filterByTag.length === 0 ||
                            (task.tags || []).some(t => this.filterByTag.includes(t));
                        const matchesDue = this.matchesDueFilter(task);
                        const q = this.normalizeSearch(this.boardSearchQuery);
                        const matchesSearch = !q ||
                            this.normalizeSearch(task.title).includes(q) ||
                            this.normalizeSearch(task.description).includes(q) ||
                            this.normalizeSearch(task.id).includes(q);
                        return matchesAssignee && matchesPriority && matchesTag && matchesDue && matchesSearch;
                    });
                },

                dueFilterLabel() {
                    return { overdue: 'عقب‌افتاده', today: 'امروز', next7: '۷ روز آینده', undated: 'بدون سررسید' }[this.filterByDue] || '';
                },

                matchesDueFilter(task) {
                    if (!this.filterByDue) return true;
                    if (this.filterByDue === 'undated') return !task.dueDate;
                    if (!task.dueDate) return false;
                    if (this.filterByDue === 'overdue') return this.isOverdue(task.dueDate, task.dueTime);
                    const today = this.workspaceNowParts().date;
                    if (this.filterByDue === 'today') return task.dueDate === today;
                    const [year, month, day] = today.split('-').map(Number);
                    const weekEnd = new Date(Date.UTC(year, month - 1, day + 7)).toISOString().slice(0, 10);
                    return task.dueDate >= today && task.dueDate <= weekEnd;
                },

                boardStyleStorageKey() {
                    return 'neova-board-style:' + this.projectId;
                },

                setBoardStyle(style, { persistLocal = true } = {}) {
                    if (!['simple', 'creative'].includes(style)) return;
                    this.boardStyle = style;
                    if (persistLocal) {
                        try {
                            localStorage.setItem(this.boardStyleStorageKey(), style);
                        } catch (error) {
                            // ignore private browsing storage errors
                        }
                    }
                },

                resolveBoardStyle() {
                    try {
                        const local = localStorage.getItem(this.boardStyleStorageKey());
                        if (local === 'simple' || local === 'creative') {
                            this.boardStyle = local;
                            return;
                        }
                    } catch (error) {
                        // ignore
                    }
                    this.boardStyle = this.projectBoardStyleDefault || 'simple';
                },

                selectSearchResult(task, columnId) {
                    this.boardSearchOpen = false;
                    this.boardSearchQuery = '';
                    this.openEditModal(task, columnId);
                },

                activeFilterCount() {
                    return this.filterByAssignee.length + this.filterByPriority.length + this.filterByTag.length + (this.filterByDue ? 1 : 0);
                },

                isTaskSelected(taskId) {
                    return this.selectedTaskIds.includes(Number(taskId));
                },

                toggleTaskSelection(taskId) {
                    const id = Number(taskId);
                    this.selectedTaskIds = this.selectedTaskIds.includes(id)
                        ? this.selectedTaskIds.filter(selected => selected !== id)
                        : this.selectedTaskIds.concat([id]);
                },

                clearSelection() {
                    this.selectedTaskIds = [];
                    this.bulkAction = '';
                    this.bulkValue = '';
                },

                async applyBulkAction() {
                    if (!this.bulkAction || !this.selectedTaskIds.length || this.bulkLoading) return;
                    if (this.bulkAction === 'tag' && !this.bulkValue.trim()) return;
                    this.bulkLoading = true;
                    try {
                        const response = await window.neovaFetch('{{ route("board.tasks.bulk", [$workspace->slug, $project->slug], false) }}', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ task_ids: this.selectedTaskIds, action: this.bulkAction, value: this.bulkValue || null }),
                        });
                        if (!response.ok) { const error = await response.json().catch(() => ({})); throw new Error(error.message || 'ذخیره تغییرات گروهی انجام نشد'); }
                        const selected = new Set(this.selectedTaskIds);
                        for (const column of this.columns) {
                            for (const task of column.tasks) {
                                if (!selected.has(Number(task.dbId))) continue;
                                if (this.bulkAction === 'priority') task.priority = this.bulkValue;
                                if (this.bulkAction === 'assignee') task.assignees = this.bulkValue ? [this.bulkValue] : [];
                                if (this.bulkAction === 'tag' && !(task.tags || []).includes(this.bulkValue.trim())) task.tags = (task.tags || []).concat([this.bulkValue.trim()]);
                                if (this.bulkAction === 'due_date') { task.dueDate = ''; task.dueTime = ''; }
                            }
                        }
                        if (this.bulkAction === 'column') {
                            const target = this.columns.find(column => String(column.id) === String(this.bulkValue));
                            const moved = [];
                            this.columns.forEach(column => {
                                column.tasks = column.tasks.filter(task => {
                                    if (!selected.has(Number(task.dbId))) return true;
                                    moved.push(task);
                                    return false;
                                });
                            });
                            if (target) target.tasks = target.tasks.concat(moved);
                        }
                        this.showToast('تغییرات گروهی ذخیره شد');
                        this.clearSelection();
                    } catch (error) {
                        this.showToast(error.message || 'ذخیره تغییرات گروهی انجام نشد', 'error');
                    } finally {
                        this.bulkLoading = false;
                    }
                },

                clearAllFilters() {
                    this.filterByAssignee = [];
                    this.filterByPriority = [];
                    this.filterByTag = [];
                    this.filterByDue = '';
                },

                togglePriorityFilter(priority) {
                    const idx = this.filterByPriority.indexOf(priority);
                    if (idx === -1) this.filterByPriority.push(priority);
                    else this.filterByPriority.splice(idx, 1);
                },

                toggleTagFilter(tag) {
                    const idx = this.filterByTag.indexOf(tag);
                    if (idx === -1) this.filterByTag.push(tag);
                    else this.filterByTag.splice(idx, 1);
                },

                visibleTags(task) {
                    const tags = task.tags || [];
                    return tags.slice(0, 3);
                },

                hiddenTagCount(task) {
                    const tags = task.tags || [];
                    return Math.max(0, tags.length - 3);
                },

                priorityBadgeClass(priority) {
                    if (priority === 'بالا') return 'priority-high';
                    if (priority === 'متوسط') return 'priority-medium';
                    return 'priority-low';
                },

                modalAccentStyle() {
                    const col = this.columns.find(c => String(c.id) === String(this.form.columnId));
                    return '--modal-accent:' + (col?.dotHex || '#18212B');
                },

                floatingMenuStyle(trigger, preferredWidth = null, preferredHeight = 320) {
                    this.floatingMenuRevision;
                    if (!trigger) return { visibility: 'hidden' };

                    const rect = trigger.getBoundingClientRect();
                    const gutter = 12;
                    const gap = 6;
                    const viewportWidth = window.innerWidth;
                    const viewportHeight = window.innerHeight;
                    const width = Math.min(Number(preferredWidth) || rect.width, viewportWidth - (gutter * 2));
                    const left = Math.min(Math.max(gutter, rect.right - width), viewportWidth - width - gutter);
                    const roomBelow = Math.max(0, viewportHeight - rect.bottom - gutter - gap);
                    const roomAbove = Math.max(0, rect.top - gutter - gap);
                    const openAbove = roomBelow < Math.min(preferredHeight, 220) && roomAbove > roomBelow;
                    const availableHeight = Math.max(120, Math.min(preferredHeight, openAbove ? roomAbove : roomBelow));
                    return {
                        position: 'fixed',
                        left: `${left}px`,
                        right: 'auto',
                        width: `${width}px`,
                        maxHeight: `${availableHeight}px`,
                        top: openAbove ? 'auto' : `${Math.min(viewportHeight - gutter, rect.bottom + gap)}px`,
                        bottom: openAbove ? `${Math.max(gutter, viewportHeight - rect.top + gap)}px` : 'auto',
                        visibility: 'visible',
                    };
                },

                checklistTotal(task) {
                    return (task.checklist || []).length;
                },

                checklistDone(task) {
                    return (task.checklist || []).filter(i => i.done).length;
                },

                checklistPercentFor(task) {
                    const total = this.checklistTotal(task);
                    if (!total) return 0;
                    return Math.round((this.checklistDone(task) / total) * 100);
                },

                checklistLabel(task) {
                    return this.toPersianDigits(this.checklistDone(task)) + '/' + this.toPersianDigits(this.checklistTotal(task));
                },

                normalizeSearch(value) {
                    return String(value || '').normalize('NFKC').toLowerCase().replace(/ي/g, 'ی').replace(/ك/g, 'ک').replace(/[۰-۹]/g, digit => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit))).replace(/[\u200c\s]+/g, '');
                },

                highlightText(text, query) {
                    text = String(text || '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
                    if (!query || !text) return text;
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

                async loadActivity(page = 1) {
                    if (this.activityLoading) return;
                    this.activityLoading = true;
                    this.activityError = '';
                    try {
                        const params = new URLSearchParams({ page, search: this.activitySearch, user_id: this.activityUserId, kind: this.activityKind });
                        const res = await window.neovaFetch('{{ route("board.activity", [$workspace->slug, $project->slug], false) }}?' + params.toString(), {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const payload = await res.json();
                        if (!res.ok) throw new Error(payload.message || 'بارگذاری فعالیت‌ها انجام نشد.');
                        this.activityItems = payload.data || [];
                        this.activityMeta = payload.meta || { current_page: 1, last_page: 1, total: 0 };
                        this.activityUsers = payload.filters?.users || this.activityUsers;
                        this.activityKinds = payload.filters?.kinds || this.activityKinds;
                    } catch (e) {
                        this.activityError = e.message || 'بارگذاری فعالیت‌ها انجام نشد.';
                    } finally {
                        this.activityLoading = false;
                    }
                },

                activityKindLabel(kind) {
                    const labels = {
                        task_created: 'ایجاد وظیفه', task_description_changed: 'توضیحات', task_description_removed: 'حذف توضیحات',
                        task_priority_changed: 'اولویت', task_due_date_changed: 'سررسید', task_assignees_added: 'افزودن مسئول', task_assignees_removed: 'حذف مسئول',
                        task_tags_added: 'افزودن برچسب', task_tags_removed: 'حذف برچسب', task_checklist_changed: 'چک‌لیست', task_comment_added: 'گفتگو',
                        task_checklist_item_added: 'افزودن چک‌لیست', task_checklist_item_removed: 'حذف چک‌لیست', task_checklist_item_completed: 'تکمیل چک‌لیست', task_checklist_item_reopened: 'بازکردن چک‌لیست',
                        task_moved: 'جابجایی وظیفه', task_deleted: 'حذف وظیفه', column_created: 'ایجاد ستون', column_title_changed: 'تغییر ستون',
                        column_color_changed: 'رنگ ستون', column_order_changed: 'ترتیب ستون‌ها', column_deleted: 'حذف ستون', project_member_added: 'افزودن عضو',
                        project_member_removed: 'حذف عضو', project_name_changed: 'نام پروژه', project_key_changed: 'کلید پروژه', project_description_changed: 'توضیحات پروژه',
                        project_board_style_changed: 'سبک تخته', project_custom_tags_changed: 'برچسب‌های پروژه'
                    };
                    return labels[kind] || kind;
                },

                boardMutationBusy() {
                    return this.realtimeDragActive || this.taskMovePending || this.columnMovePending || this.taskSaving || this.quickTaskSaving || this.columnSaving || this.taskDeleting || this.columnDeleting || this.bulkLoading || this.projectSettingsSaving || this.projectMemberSaving || this.commentPosting || this.attachmentUploading;
                },

                queueMutationSnapshot(payload) {
                    if (this.mutationSnapshot?.generatedAt && payload.generatedAt < this.mutationSnapshot.generatedAt) return;
                    this.mutationSnapshot = payload;
                    clearTimeout(this.mutationTimer);
                    const flush = () => {
                        if (this.destroyed) return;
                        if (this.boardMutationBusy()) { this.mutationTimer = setTimeout(flush, 25); return; }
                        this.flushMutationSnapshot();
                    };
                    this.mutationTimer = setTimeout(flush, 0);
                },

                flushMutationSnapshot() {
                    if (this.boardMutationBusy() || !this.mutationSnapshot) return;
                    clearTimeout(this.mutationTimer);
                    const snapshot = this.mutationSnapshot;
                    this.mutationSnapshot = null;
                    this.applyRealtimeSnapshot(snapshot, { ownMutation: true });
                    if (this.pendingRealtimeSnapshot) {
                        const pending = this.pendingRealtimeSnapshot;
                        this.pendingRealtimeSnapshot = null;
                        this.applyRealtimeSnapshot(pending);
                    }
                },

                applyRealtimeSnapshot(payload, { ownMutation = false } = {}) {
                    // A delayed read must never beat the acknowledgement of our write.
                    if (!ownMutation) this.flushMutationSnapshot();
                    if (!payload || (this.lastSnapshotAt && payload.generatedAt && payload.generatedAt < this.lastSnapshotAt)) return;
                    if (this.boardMutationBusy()) {
                        if (this.pendingRealtimeSnapshot?.generatedAt && payload.generatedAt < this.pendingRealtimeSnapshot.generatedAt) return;
                        this.pendingRealtimeSnapshot = payload;
                        clearTimeout(this.remoteSnapshotTimer);
                        this.remoteSnapshotTimer = setTimeout(() => {
                            if (this.destroyed || !this.pendingRealtimeSnapshot) return;
                            const pending = this.pendingRealtimeSnapshot;
                            this.pendingRealtimeSnapshot = null;
                            this.applyRealtimeSnapshot(pending);
                        }, 50);
                        return;
                    }
                    this.lastSnapshotAt = payload.generatedAt || this.lastSnapshotAt;
                    if (typeof payload.canEdit === 'boolean') this.canEdit = payload.canEdit;
                    const activeId = this.columns[this.activeColumnIndex]?.id;
                    const collapsed = new Set(this.columns.filter(column => column.collapsed).map(column => String(column.id)));
                    const remoteTask = this.editingTask
                        ? payload.columns.flatMap(column => column.tasks).find(task => Number(task.dbId) === Number(this.editingTask))
                        : null;
                    const draftIsDirty = this.showModal && this.modalSnapshot && this.formFingerprint() !== this.modalSnapshot;
                    const attachmentBusy = this.attachmentUploading || this.commentPosting || this.pendingDescriptionFiles.length || this.pendingCommentFiles.length;
                    const remoteChanged = !remoteTask || (remoteTask.version != null && this.form.version != null ? Number(remoteTask.version) !== Number(this.form.version) : remoteTask.updatedAt !== this.form.updatedAt);
                    this.columns = payload.columns.map(column => ({ ...column, collapsed: collapsed.has(String(column.id)) }));
                    this.activeColumnIndex = Math.max(0, this.columns.findIndex(column => column.id === activeId));
                    const existingIds = new Set(this.columns.flatMap(column => column.tasks.map(task => Number(task.dbId))));
                    this.selectedTaskIds = this.selectedTaskIds.filter(id => existingIds.has(Number(id)));
                    this.projectMembers = payload.members;
                    this.workspacePeople = payload.workspacePeople;
                    this.assignees = payload.members.map(member => member.name);
                    this.customTags = payload.project.customTags || [];
                    this.activeCycle = payload.activeCycle;
                    Object.assign(this.projectState, payload.project);
                    const baselineSettings = this.projectBaseline ? JSON.parse(this.projectBaseline) : {};
                    const settingsUnchanged = baselineSettings.name === payload.project.name && baselineSettings.key === payload.project.key && baselineSettings.description === payload.project.description && baselineSettings.board_style === payload.project.boardStyle;
                    if (settingsUnchanged) this.projectBaselineVersion = payload.project.version;
                    if (!this.projectSettingsDirty()) {
                      Object.assign(this.projectForm, {
                        name: payload.project.name,
                        key: payload.project.key,
                        description: payload.project.description,
                        board_style: payload.project.boardStyle,
                    });
                      this.projectBaseline = JSON.stringify(this.projectForm);
                      this.projectBaselineVersion = payload.project.version;
                    }
                    if (this.showModal && remoteTask) { this.form.updatedAt = remoteTask.updatedAt; this.form.version = remoteTask.version; }
                    if (this.showModal && remoteTask) {
                        this.form.comments = JSON.parse(JSON.stringify(remoteTask.comments || []));
                        this.form.attachments = JSON.parse(JSON.stringify(remoteTask.attachments || []));
                    }
                    if (this.showModal && this.editingTask && !draftIsDirty && !attachmentBusy && remoteTask && remoteChanged) {
                        const column = this.columns.find(item => item.tasks.some(task => Number(task.dbId) === Number(this.editingTask)));
                        this.openEditModal(remoteTask, column.id, { preserveFocus: true });
                    }
                    if (this.showModal && this.editingTask && !remoteTask && !draftIsDirty && !attachmentBusy) this.closeModal();
                    this.destroySortables();
                    this.destroyColumnSortables();
                    this.$nextTick(() => {
                        this.columns.forEach(column => this.initSortable(column.id, this.boardMediaQuery?.matches ? 'mobile' : 'desktop'));
                        this.initColumnSortable(this.boardMediaQuery?.matches ? 'mobile' : 'desktop');
                        if (this.boardMediaQuery?.matches) this.initMobileBoardObserver();
                    });
                },

                finishRealtimeDrag() {
                    window.setTimeout(() => {
                        this.realtimeDragActive = false;
                        if (this.taskMovePending || this.columnMovePending) {
                            window.setTimeout(() => this.finishRealtimeDrag(), 50);
                            return;
                        }
                        if (!this.pendingRealtimeSnapshot) return;
                        const payload = this.pendingRealtimeSnapshot;
                        this.pendingRealtimeSnapshot = null;
                        this.applyRealtimeSnapshot(payload);
                    }, 0);
                },

                init() {
                    if (this.initialized) return;
                    this.initialized = true;
                    ['filterByAssignee', 'filterByPriority', 'filterByTag', 'filterByDue', 'boardSearchQuery'].forEach(key => this.$watch(key, () => this.setSortablesDisabled(this.taskMovePending || this.columnMovePending)));
                    this.projectBaseline = JSON.stringify(this.projectForm);
                    this.resolveBoardStyle();
                    if (this.projectDrawerOpen) document.body.classList.add('modal-open');
                    this.realtimeRefresher = window.createRealtimeRefresher({
                        url: @json($boardRealtimeSnapshotUrl),
                        apply: payload => this.applyRealtimeSnapshot(payload),
                        onState: detail => { this.snapshotState = detail.state; this.snapshotAccessStatus = detail.status; },
                    });
                    this.realtimeChannel = window.subscribeProjectRealtime(this.projectId, () => this.realtimeRefresher.schedule());
                    this.realtimeChannel?.subscribed(() => { this.connectionState = 'connected'; this.realtimeRefresher.refreshNow(); });
                    this.realtimeChannel?.error(() => { this.connectionState = 'failed'; this.snapshotState = 'stale'; });
                    this.connectionState = window.Echo?.connector?.pusher?.connection?.state || 'unavailable';
                    this.reconnectHandler = () => this.realtimeRefresher.refreshNow();
                    this.connectionHandler = event => { this.connectionState = event.detail.state; };
                    this.mutationHandler = event => this.queueMutationSnapshot(event.detail);
                    this.visibilityHandler = () => { if (!document.hidden) this.realtimeRefresher.refreshNow(); };
                    window.addEventListener('neova:realtime-reconnected', this.reconnectHandler);
                    window.addEventListener('neova:realtime-state', this.connectionHandler);
                    window.addEventListener('neova:mutation-snapshot', this.mutationHandler);
                    document.addEventListener('visibilitychange', this.visibilityHandler);
                    this.pollTimer = setInterval(() => {
                        if (!document.hidden && this.connectionState !== 'connected' && this.snapshotState !== 'access-error') this.realtimeRefresher.refreshNow();
                    }, 30000);
                    this.keyboardHandler = event => {
                        const target = event.target;
                        const typing = target && (target.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName));
                        if (!typing && !event.metaKey && !event.ctrlKey && !event.altKey && event.key.toLowerCase() === 'c' && !this.showModal && !this.projectDrawerOpen && !this.showColumnModal && !this.showDeleteModal && !this.showColumnDeleteModal) {
                            event.preventDefault();
                            this.openQuickComposer(this.columns[this.activeColumnIndex]?.id || this.columns[0]?.id);
                        }
                        if (event.key === 'Escape' && !this.showModal && !this.showDeleteModal && !this.showColumnModal && !this.showColumnDeleteModal) {
                            this.clearBoardSearch();
                            this.filterPanelOpen = false;
                        }
                    };
                    window.addEventListener('keydown', this.keyboardHandler);
                    try {
                        this.swipeHintVisible = window.innerWidth < 768 && localStorage.getItem('neova-board-swipe-hint') !== 'dismissed';
                    } catch (error) {
                        this.swipeHintVisible = window.innerWidth < 768;
                    }

                    this.boardMediaQuery = window.matchMedia('(max-width: 767px)');
                    this.mediaHandler = () => {
                        this.destroySortables();
                        this.destroyColumnSortables();
                        this.$nextTick(() => {
                            this.columns.forEach(column => {
                                this.initSortable(column.id, this.boardMediaQuery.matches ? 'mobile' : 'desktop');
                            });
                            this.initColumnSortable(this.boardMediaQuery.matches ? 'mobile' : 'desktop');
                            if (this.boardMediaQuery.matches) this.initMobileBoardObserver();
                            else this.destroyMobileBoardObserver();
                        });
                    };
                    this.boardMediaQuery.addEventListener('change', this.mediaHandler);

                    this.$nextTick(() => {
                        if (this.boardMediaQuery.matches) this.initMobileBoardObserver();
                        this.columnHeightHandler = () => this.updateColumnHeight();
                        this.columnHeightObserver = new ResizeObserver(this.columnHeightHandler);
                        document.querySelectorAll('.workspace-topbar, .workspace-main').forEach(element => this.columnHeightObserver.observe(element));
                        window.addEventListener('resize', this.columnHeightHandler);
                        this.updateColumnHeight();
                    });

                    this.$nextTick(() => {
                        this.initJalaliDatePicker();
                        const section = @json(request()->query('settings'));
                        if (this.canManageProject && ['general', 'members', 'activity', 'delete'].includes(section)) {
                            this.openProjectDrawer(section === 'members' || section === 'activity' ? section : 'settings');
                            if (section === 'delete') { this.confirmingDeletion = true; this.$nextTick(() => this.$refs.projectDeleteForm?.scrollIntoView({ block: 'center' })); }
                        }
                        const requestedTask = Number(@json(request()->query('task')) || 0);
                        if (requestedTask) {
                            for (const column of this.columns) {
                                const task = column.tasks.find(item => Number(item.dbId) === requestedTask);
                                if (task) { this.openEditModal(task, column.id); break; }
                            }
                        }
                    });
                },

                destroy() {
                    this.columnHeightObserver?.disconnect();
                    window.removeEventListener('resize', this.columnHeightHandler);
                    this.destroyed = true;
                    clearTimeout(this.mutationTimer); clearTimeout(this.remoteSnapshotTimer); clearTimeout(this.toastTimer); clearInterval(this.pollTimer);
                    this.realtimeRefresher?.dispose();
                    window.Echo?.leave(`project.${this.projectId}`);
                    window.removeEventListener('keydown', this.keyboardHandler);
                    window.removeEventListener('neova:realtime-reconnected', this.reconnectHandler);
                    window.removeEventListener('neova:realtime-state', this.connectionHandler);
                    window.removeEventListener('neova:mutation-snapshot', this.mutationHandler);
                    document.removeEventListener('visibilitychange', this.visibilityHandler);
                    this.boardMediaQuery?.removeEventListener('change', this.mediaHandler);
                    this.destroySortables(); this.destroyColumnSortables(); this.destroyMobileBoardObserver();
                    this.endMobileDrag(); clearTimeout(this.mobileScrollTimer);
                },

                updateColumnHeight() {
                    const tracks = [document.getElementById('desktop-column-track'), document.getElementById('mobile-column-track')];
                    for (const track of tracks) {
                        if (!track || !track.getClientRects().length) continue;
                        const offset = Math.ceil(track.getBoundingClientRect().top + window.scrollY + parseFloat(window.getComputedStyle(track).paddingTop));
                        track.style.setProperty('--board-column-offset', `${offset}px`);
                    }
                },

                syncStatusText() {
                    if (this.snapshotState === 'access-error') return this.snapshotAccessStatus === 404 ? 'پروژه دیگر در دسترس نیست؛ پیش‌نویس شما حفظ شد' : 'دسترسی قطع شد؛ پیش‌نویس شما حفظ شد';
                    if (this.snapshotState === 'stale') return 'به‌روزرسانی انجام نشد';
                    return this.connectionState === 'connected' ? 'به‌روز' : 'به‌روزرسانی زنده قطع است';
                },

                clearBoardSearch() {
                    this.boardSearchQuery = '';
                    this.boardSearchOpen = false;
                },

                toPersianDigits(value) {
                    return String(value).replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
                },

                initJalaliDatePicker() {
                    const today = moment().locale('fa');
                    this.jalaliDatePicker.year = Number(today.format('jYYYY'));
                    this.jalaliDatePicker.month = Number(today.format('jM'));
                },

                jalaliDateParts(dateStr) {
                    const date = dateStr
                        ? moment(dateStr, 'YYYY-MM-DD').locale('fa')
                        : moment().locale('fa');
                    return {
                        year: Number(date.format('jYYYY')),
                        month: Number(date.format('jM')),
                        day: Number(date.format('jD')),
                    };
                },

                formatDateInput(dateStr) {
                    if (!dateStr) return '';
                    const date = moment(dateStr, 'YYYY-MM-DD').locale('fa');
                    return this.toPersianDigits(date.format('YYYY/MM/DD'));
                },

                jalaliMonthLabel() {
                    const date = moment(`${this.jalaliDatePicker.year}/${this.jalaliDatePicker.month}/1`, 'jYYYY/jM/jD').locale('fa');
                    return this.toPersianDigits(date.format('jMMMM jYYYY'));
                },

                jalaliMonthDays(year, month) {
                    const date = moment(`${year}/${month}/1`, 'jYYYY/jM/jD').locale('fa');
                    return Number(date.endOf('jMonth').format('jD'));
                },

                jalaliCalendarDays() {
                    const { year, month } = this.jalaliDatePicker;
                    const firstDay = moment(`${year}/${month}/1`, 'jYYYY/jM/jD').toDate();
                    const offset = (firstDay.getDay() + 1) % 7;
                    return Array(offset).fill(null).concat(Array.from({ length: this.jalaliMonthDays(year, month) }, (_, index) => index + 1));
                },

                openJalaliDatePicker() {
                    const current = this.jalaliDateParts(this.form.dueDate);
                    this.jalaliDatePicker.year = current.year;
                    this.jalaliDatePicker.month = current.month;
                    this.jalaliDatePicker.open = true;
                },

                closeJalaliDatePicker() {
                    this.jalaliDatePicker.open = false;
                },

                changeJalaliMonth(step) {
                    let month = this.jalaliDatePicker.month + step;
                    let year = this.jalaliDatePicker.year;
                    if (month < 1) { month = 12; year--; }
                    if (month > 12) { month = 1; year++; }
                    this.jalaliDatePicker.year = year;
                    this.jalaliDatePicker.month = month;
                },

                selectJalaliDate(day) {
                    this.form.dueDate = moment.from(`${this.jalaliDatePicker.year}/${this.jalaliDatePicker.month}/${day}`, 'fa', 'YYYY/M/D').format('YYYY-MM-DD');
                    this.closeJalaliDatePicker();
                },

                clearJalaliDate() {
                    this.form.dueDate = '';
                    this.form.dueTime = '';
                    this.closeJalaliDatePicker();
                },

                selectTodayJalaliDate() {
                    const today = moment().locale('fa');
                    this.jalaliDatePicker.year = Number(today.format('jYYYY'));
                    this.jalaliDatePicker.month = Number(today.format('jM'));
                    this.selectJalaliDate(Number(today.format('jD')));
                },

                isSelectedJalaliDay(day) {
                    if (!this.form.dueDate) return false;
                    const selected = this.jalaliDateParts(this.form.dueDate);
                    return selected.year === this.jalaliDatePicker.year && selected.month === this.jalaliDatePicker.month && selected.day === day;
                },

                isTodayJalaliDay(day) {
                    const today = this.jalaliDateParts();
                    return today.year === this.jalaliDatePicker.year && today.month === this.jalaliDatePicker.month && today.day === day;
                },

                dismissSwipeHint() {
                    this.swipeHintVisible = false;
                    try {
                        localStorage.setItem('neova-board-swipe-hint', 'dismissed');
                    } catch (error) {
                        // Storage can be unavailable in private browsing.
                    }
                },

                scrollToColumn(index, behavior = 'smooth') {
                    if (index < 0 || index >= this.columns.length) return;
                    const track = document.getElementById('mobile-column-track');
                    const target = track?.querySelector(`[data-column-index="${index}"]`);
                    if (!target) return;
                    target.scrollIntoView({
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : behavior,
                        block: 'nearest',
                        inline: 'center',
                    });
                    this.activeColumnIndex = index;
                    this.revealActiveColumnTab();
                },

                revealActiveColumnTab() {
                    this.$nextTick(() => document.getElementById('mobile-column-tabs')
                        ?.querySelector(`[data-tab-index="${this.activeColumnIndex}"]`)
                        ?.scrollIntoView({ block: 'nearest', inline: 'nearest' }));
                },

                handleMobileBoardScroll() {
                    window.clearTimeout(this.mobileScrollTimer);
                    this.mobileScrollTimer = window.setTimeout(() => {
                        const track = document.getElementById('mobile-column-track');
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
                        this.revealActiveColumnTab();
                        this.dismissSwipeHint();
                    }, 80);
                },

                initMobileBoardObserver() {
                    this.destroyMobileBoardObserver();
                    const track = document.getElementById('mobile-column-track');
                    if (!track || !window.IntersectionObserver) return;
                    this.mobileBoardObserver = new IntersectionObserver(entries => {
                        const visible = entries
                            .filter(entry => entry.isIntersecting)
                            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
                        if (visible) {
                            this.activeColumnIndex = Number(visible.target.dataset.columnIndex);
                            this.revealActiveColumnTab();
                        }
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
                    const track = document.getElementById('mobile-column-track');
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
                    const cards = Array.from(list.querySelectorAll(':scope > article[data-id]'));
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

                async reloadProjectSettings() {
                    if (this.projectSettingsDirty() && !window.confirm('پیش‌نویس تنظیمات کنار گذاشته و نسخه جدید بارگذاری شود؟')) return;
                    // Preserve the draft if the request fails.
                    const response = await window.neovaFetch(@json($boardRealtimeSnapshotUrl), { headers: { Accept: 'application/json' }, cache: 'no-store' }).catch(() => null);
                    if (!response?.ok) { this.projectSettingsError = 'بارگذاری تنظیمات انجام نشد. دوباره تلاش کنید.'; return; }
                    const payload = await response.json();
                    this.projectForm = { name: payload.project.name, key: payload.project.key, description: payload.project.description, board_style: payload.project.boardStyle };
                    this.projectBaseline = JSON.stringify(this.projectForm);
                    this.projectBaselineVersion = payload.project.version;
                    this.projectSettingsError = '';
                    this.applyRealtimeSnapshot(payload);
                },

                async archiveProject() {
                    if (!this.canManageProject || this.projectSettingsSaving || this.projectSettingsDirty()) return;
                    this.projectSettingsSaving = true;
                    try {
                        const response = await window.neovaFetch('{{ route("board.project.archive", [$workspace->slug, $project->slug], false) }}', {
                            method: 'PATCH', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ is_active: !this.projectState.isActive }),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'بایگانی انجام نشد.');
                        window.location.assign('{{ route("projects.index", $workspace->slug, false) }}' + (this.projectState.isActive ? '?archived=1' : ''));
                    } catch (error) { this.projectSettingsError = error.message; }
                    finally { this.projectSettingsSaving = false; }
                },

                projectSettingsDirty() {
                    return !!this.projectBaseline && JSON.stringify(this.projectForm) !== this.projectBaseline;
                },

                selectProjectTab(tab) {
                    this.projectDrawerTab = tab;
                    if (tab === 'activity' && !this.activityItems.length) this.loadActivity();
                },

                handleProjectTabKeys(event) {
                    const tabs = ['settings', 'members', 'activity'];
                    const index = tabs.indexOf(this.projectDrawerTab);
                    let next;
                    if (event.key === 'ArrowLeft') next = (index + 1) % tabs.length;
                    if (event.key === 'ArrowRight') next = (index + tabs.length - 1) % tabs.length;
                    if (event.key === 'Home') next = 0;
                    if (event.key === 'End') next = tabs.length - 1;
                    if (next === undefined) return;
                    event.preventDefault();
                    this.selectProjectTab(tabs[next]);
                    document.getElementById('project-tab-' + tabs[next])?.focus();
                },

                trapProjectFocus(event) {
                    if (event.key !== 'Tab') return;
                    const controls = Array.from(this.$refs.projectDrawer.querySelectorAll('button, input, textarea, select, a[href], [tabindex="0"]')).filter(el => !el.disabled && el.getClientRects().length);
                    const first = controls[0], last = controls.at(-1);
                    if (event.shiftKey && (document.activeElement === first || !this.$refs.projectDrawer.contains(document.activeElement))) { event.preventDefault(); last?.focus(); }
                    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
                },

                openProjectDrawer(tab = 'settings') {
                    if (!this.canManageProject) return;
                    this.projectLastFocused = document.activeElement;
                    this.projectDrawerOpen = true;
                    this.projectDrawerTab = tab;
                    if (tab === 'activity') this.loadActivity();
                    document.body.classList.add('modal-open');
                    this.$nextTick(() => this.$refs.projectDrawerClose?.focus());
                },

                closeProjectDrawer() {
                    if (!this.projectDrawerOpen) return;
                    if (this.projectSettingsSaving) return;
                    if (this.projectSettingsDirty() && !window.confirm('تغییرات تنظیمات ذخیره نشده‌اند. کنار گذاشته شوند؟')) return;
                    if (this.projectSettingsDirty()) {
                        const baseline = JSON.parse(this.projectBaseline);
                        this.projectForm = { name: this.projectState.name, key: this.projectState.key, description: this.projectState.description ?? baseline.description, board_style: this.projectState.boardStyle ?? baseline.board_style };
                        this.projectBaseline = JSON.stringify(this.projectForm);
                        this.projectBaselineVersion = this.projectState.version;
                    }
                    this.projectDrawerOpen = false;
                    document.body.classList.remove('modal-open');
                    this.$nextTick(() => this.projectLastFocused?.focus?.());
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
                        const response = await window.neovaFetch(url, {
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
                        this.showToast(error.message, 'error');
                    } finally {
                        this.projectMemberSaving = null;
                    }
                },

                async saveProjectSettings() {
                    if (!this.canManageProject || this.projectSettingsSaving) return;
                    this.projectSettingsSaving = true;
                    this.projectSettingsError = '';
                    try {
                        const response = await window.neovaFetch('{{ route("board.project.update", [$workspace->slug, $project->slug], false) }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ ...this.projectForm, expected_version: this.projectBaselineVersion }),
                        });
                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'ذخیره تنظیمات انجام نشد.');
                        this.projectForm = Object.assign({}, this.projectForm, data.project);
                        this.projectBaseline = JSON.stringify(this.projectForm);
                        this.projectBaselineVersion = data.board?.project.version;
                        Object.assign(this.projectState, data.board?.project || data.project);
                        if (data.project?.board_style) {
                            this.projectBoardStyleDefault = data.project.board_style;
                            this.setBoardStyle(data.project.board_style, { persistLocal: true });
                        }
                        this.showToast(data.message);
                    } catch (error) {
                        this.projectSettingsError = error.message;
                        this.showToast(error.message, 'error');
                    } finally {
                        this.projectSettingsSaving = false;
                    }
                },

                async saveCycleConfig() {
                    try {
                        const response = await window.neovaFetch('{{ route("cycles.configure", [$workspace->slug, $project->slug], false) }}', {
                            method:'PATCH', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                            body:JSON.stringify({cycle_length_weeks:this.cycleLength || null}),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'ذخیره چرخه انجام نشد.');
                        this.cycleLength = data.cycleLengthWeeks || ''; this.showToast('تنظیم چرخه ذخیره شد');
                    } catch (error) { this.showToast(error.message, 'error'); }
                },

                async startCycle() {
                    const taskIds = this.columns.filter(column => column.workflowRole !== 'done').flatMap(column => column.tasks.map(task => task.dbId));
                    try {
                        const response = await window.neovaFetch('{{ route("cycles.start", [$workspace->slug, $project->slug], false) }}', {
                            method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({task_ids:taskIds}),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'شروع چرخه انجام نشد.');
                        this.activeCycle = { id:data.cycle.id, number:data.cycle.number, startsOn:data.cycle.startsOn, endsOn:data.cycle.endsOn, taskIds:data.cycle.tasks.map(task => task.id), openTaskIds:data.cycle.tasks.map(task => task.id) };
                        this.showToast('چرخه شروع شد');
                    } catch (error) { this.showToast(error.message, 'error'); }
                },

                async finishCycle() {
                    if (!this.activeCycle || !window.confirm('چرخه پایان یابد و همه کارهای باز به چرخه بعد منتقل شوند؟')) return;
                    try {
                        const url = '{{ route("cycles.finish", [$workspace->slug, $project->slug, "__CYCLE__"], false) }}'.replace('__CYCLE__', this.activeCycle.id);
                        const response = await window.neovaFetch(url, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({carry_task_ids:this.activeCycle.openTaskIds || [],removed_task_ids:[],start_next:true}) });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'پایان چرخه انجام نشد.');
                        this.activeCycle = data.nextCycle ? { id:data.nextCycle.id, number:data.nextCycle.number, startsOn:data.nextCycle.startsOn, endsOn:data.nextCycle.endsOn, taskIds:data.nextCycle.tasks.map(task => task.id), openTaskIds:data.nextCycle.tasks.map(task => task.id) } : null;
                        this.showToast('چرخه جدید شروع شد');
                    } catch (error) { this.showToast(error.message, 'error'); }
                },

                async saveColumnRole(column) {
                    try {
                        const url = '{{ route("board.column.update", [$workspace->slug, $project->slug, "__COLUMN__"], false) }}'.replace('__COLUMN__', column.id);
                        const response = await window.neovaFetch(url, { method:'PATCH', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body:JSON.stringify({title:column.title,color:column.dotHex,wip_limit:column.wipLimit || null,workflow_role:column.workflowRole}) });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'ذخیره نقش ستون انجام نشد.');
                        column.workflowRole = data.workflow_role; this.showToast('نقش ستون ذخیره شد');
                    } catch (error) { this.showToast(error.message, 'error'); }
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
                    return Array.from(text.matchAll(/@\[[^\]]+\]\(user:(\d+)\)/gu)).map(match => Number(match[1]));
                },

                formatMentionText(text) {
                    const escaped = String(text)
                        .replaceAll('&', '&amp;')
                        .replaceAll('<', '&lt;')
                        .replaceAll('>', '&gt;')
                        .replaceAll('"', '&quot;')
                        .replaceAll("'", '&#039;');
                    return escaped
                        .replace(/@\[([^\]]+)\]\(user:\d+\)/gu, '<span class="inline-flex bg-[#F1F3F2] text-[#18212B] font-bold rounded px-1.5 py-0.5">@$1</span>')
                        .replaceAll('\n', '<br>');
                },

                getTagClass(tagName) {
                    const tag = this.allTags.find(t => t.name === tagName);
                    if (!tag) return 'bg-[#F1F5F9] text-[#94A3B8]';
                    return tag.activeClass.split(' ').filter(c => c.startsWith('bg-') || c.startsWith('text-')).join(' ');
                },

                editableTags() {
                    const hardcoded = this.allTags.map(t => Object.assign({}, t, { isCustom: false }));
                    const custom = (this.customTags || []).map(t => ({
                        name: t.name,
                        activeClass: `border-[${t.color}] bg-[${t.color}]/10 text-[${t.color}]`,
                        inactiveClass: `border-[#F1F5F9] text-[#94A3B8] hover:border-[${t.color}]/30`,
                        isCustom: true,
                    }));
                    return hardcoded.concat(custom);
                },

                addCustomTag() {
                    const name = this.newTagName.trim();
                    if (!name || this.customTags.some(t => t.name === name)) return;
                    this.customTags.push({ name, color: this.newTagColor });
                    this.newTagName = '';
                    this.saveCustomTags();
                },

                removeCustomTag(name) {
                    this.customTags = this.customTags.filter(t => t.name !== name);
                    this.form.tags = this.form.tags.filter(t => t !== name);
                    this.saveCustomTags();
                },

                async saveCustomTags() {
                    try {
                        await window.neovaFetch('{{ route("board.project.update", [$workspace->slug, $project->slug], false) }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ custom_tags: this.customTags }),
                        });
                    } catch (e) {
                        console.error('Failed to save custom tags');
                    }
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
                        this.form.assignees = this.assignees.slice();
                    }
                },

                filteredAssignees(search) {
                    if (!search.trim()) return this.assignees;
                    const s = search.trim().toLowerCase();
                    return this.assignees.filter(n => n.toLowerCase().includes(s));
                },

                editTaskDescription() {
                    if (!this.canEdit || this.taskSaving) return;
                    this.editingDescription = true;
                    this.$nextTick(() => requestAnimationFrame(() => requestAnimationFrame(() => document.getElementById('task-description-input')?.focus())));
                },

                checklistProgress() {
                    const total = this.form.checklist.length;
                    if (total === 0) return '';
                    const done = this.form.checklist.filter(i => i.done).length;
                    return this.toPersianDigits(done) + ' از ' + this.toPersianDigits(total) + ' مورد انجام شده';
                },

                checklistPercent() {
                    const total = this.form.checklist.length;
                    if (total === 0) return 0;
                    return Math.round((this.form.checklist.filter(i => i.done).length / total) * 100);
                },

                openChecklistComposer() {
                    if (!this.canEdit || this.taskSaving) return;
                    this.checklistComposerOpen = true;
                    this.$nextTick(() => requestAnimationFrame(() => document.getElementById('checklist-new-item')?.focus()));
                },

                addCheckItem({ focus = true } = {}) {
                    if (!this.canEdit || this.taskSaving || !this.newCheckItem.trim()) return;
                    this.form.checklist.push({ text: this.newCheckItem.trim(), done: false });
                    this.newCheckItem = '';
                    this.checklistComposerOpen = true;
                    if (focus) this.$nextTick(() => requestAnimationFrame(() => document.getElementById('checklist-new-item')?.focus()));
                },

                startCheckItemEdit(idx) {
                    if (!this.canEdit || this.taskSaving || !this.form.checklist[idx]) return;
                    this.finishCheckItemEdit();
                    this.editingCheckItemIndex = idx;
                    this.checkItemDraft = this.form.checklist[idx].text;
                    this.$nextTick(() => requestAnimationFrame(() => requestAnimationFrame(() => {
                        const input = document.getElementById('checklist-edit-' + idx);
                        input?.focus();
                        input?.select();
                    })));
                },

                finishCheckItemEdit(idx = this.editingCheckItemIndex) {
                    if (idx !== this.editingCheckItemIndex) return;
                    const item = this.form.checklist[this.editingCheckItemIndex];
                    if (this.canEdit && !this.taskSaving && item && this.checkItemDraft.trim()) item.text = this.checkItemDraft.trim();
                    this.editingCheckItemIndex = null;
                    this.checkItemDraft = '';
                },

                cancelCheckItemEdit() {
                    this.editingCheckItemIndex = null;
                    this.checkItemDraft = '';
                },

                removeCheckItem(idx) {
                    if (!this.canEdit || this.taskSaving) return;
                    this.finishCheckItemEdit();
                    this.form.checklist.splice(idx, 1);
                },

                async addComment() {
                    if ((!this.newComment.trim() && !this.pendingCommentFiles.length) || this.commentPosting || !this.editingTask) return;
                    this.commentPosting = true;
                    try {
                        const data = await this.postCommentRequest();
                        this.form.comments.push(data.comment);
                        this.form.attachments.push(...(data.comment.attachments || []));
                        const task = this.columns.flatMap(column => column.tasks).find(item => item.dbId === this.editingTask);
                        if (task) { task.comments = JSON.parse(JSON.stringify(this.form.comments)); task.attachments = JSON.parse(JSON.stringify(this.form.attachments)); }
                        this.clearPendingFiles('comment');
                        this.newComment = '';
                        this.closeMentionMenu();
                        this.showToast('پیام ارسال شد.');
                    } catch (error) {
                        this.showToast(error.message, 'error');
                    } finally {
                        this.commentPosting = false;
                    }
                },

                postCommentRequest() {
                    return new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        const body = new FormData();
                        body.append('text', this.newComment.trim());
                        this.mentionIds(this.newComment).forEach(id => body.append('mention_ids[]', id));
                        this.pendingCommentFiles.forEach(item => { body.append('files[]', item.file); item.status = 'uploading'; });
                        xhr.open('POST', '{{ route("board.task.comments.store", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', this.editingTask));
                        xhr.setRequestHeader('Accept', 'application/json'); xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                        const socketId = window.Echo?.socketId?.(); if (socketId) xhr.setRequestHeader('X-Socket-ID', socketId);
                        xhr.timeout = 30000; xhr.ontimeout = () => reject(new Error('ارتباط کند است. وضعیت را بررسی و دوباره تلاش کنید.'));
                        xhr.upload.onprogress = event => { if (event.lengthComputable) this.pendingCommentFiles.forEach(item => item.progress = Math.round(event.loaded / event.total * 100)); };
                        xhr.onload = () => { try { const data = JSON.parse(xhr.responseText || '{}'); xhr.status >= 200 && xhr.status < 300 ? resolve(window.neovaMutationResponse(data)) : reject(new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'ارسال پیام انجام نشد.')); } catch { reject(new Error('پاسخ سرور معتبر نیست.')); } };
                        xhr.onerror = () => reject(new Error('ارتباط هنگام ارسال فایل قطع شد.'));
                        xhr.send(body);
                    });
                },

                formatDate(dateStr, timeStr = '') {
                    if (!dateStr) return '';
                    try {
                        const jalali = moment(dateStr, 'YYYY-MM-DD').locale('fa').format('YYYY/MM/DD');
                        return this.toPersianDigits(jalali + (timeStr ? ' · ' + timeStr.slice(0, 5) : ''));
                    } catch {
                        return dateStr;
                    }
                },

                workspaceNowParts() {
                    const parts = new Intl.DateTimeFormat('en-US', {
                        timeZone: this.workspaceTimezone, year: 'numeric', month: '2-digit', day: '2-digit',
                        hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
                    }).formatToParts(new Date());
                    const value = type => parts.find(part => part.type === type)?.value || '';
                    return { date: `${value('year')}-${value('month')}-${value('day')}`, time: `${value('hour')}:${value('minute')}` };
                },

                isOverdue(dateStr, timeStr = '') {
                    if (!dateStr) return false;
                    const now = this.workspaceNowParts();
                    if (dateStr !== now.date) return dateStr < now.date;
                    return !!timeStr && timeStr.slice(0, 5) < now.time;
                },

                initColumnSortable(variant = 'desktop') {
                    if (!this.canEdit) return;
                    const isMobile = window.matchMedia('(max-width: 767px)').matches;
                    if ((variant === 'mobile') !== isMobile) return;
                    if (this.columnSortableInstances.some(item => item.variant === variant)) return;
                    const el = variant === 'mobile' ? document.getElementById('mobile-column-track') : document.getElementById('desktop-column-track');
                    if (!el) return;
                    const self = this;
                    let originalOrder = [];
                    const instance = new Sortable(el, {
                        animation: 220,
                        ghostClass: 'column-sortable-ghost',
                        chosenClass: 'column-sortable-chosen',
                        dragClass: 'column-sortable-drag',
                        direction: 'horizontal',
                        swapThreshold: 0.5,
                        draggable: variant === 'mobile' ? '.mobile-board-column' : '.board-column',
                        handle: '.column-drag-handle',
                        delay: variant === 'mobile' ? 140 : 0,
                        delayOnTouchOnly: true,
                        touchStartThreshold: 4,
                        forceFallback: true,
                        fallbackOnBody: true,
                        fallbackTolerance: variant === 'mobile' ? 5 : 3,
                        scroll: true,
                        bubbleScroll: true,
                        scrollSensitivity: 72,
                        scrollSpeed: 14,
                        onStart(evt) {
                            originalOrder = Array.from(el.children);
                            self.realtimeDragActive = true;
                            el.classList.add('column-order-dragging');
                            document.body.classList.add('column-reorder-active');
                            if (variant === 'mobile') {
                                self.mobileDragSuppressClickUntil = Date.now() + 500;
                            }
                        },
                        onEnd(evt) {
                            el.classList.remove('column-order-dragging');
                            document.body.classList.remove('column-reorder-active');
                            const selector = variant === 'mobile' ? '.mobile-board-column' : '.board-column';
                            const orderedIds = Array.from(el.querySelectorAll(`:scope > ${selector}`))
                                .map(column => Number(column.dataset.columnId))
                                .filter(Number.isFinite);
                            // Restore Alpine's keyed DOM before changing its source array.
                            originalOrder.forEach(child => el.appendChild(child));
                            if (orderedIds.length === self.columns.length
                                && !orderedIds.every((id, index) => id === Number(self.columns[index]?.id))) {
                                self.reorderColumns(orderedIds);
                            }
                            self.finishRealtimeDrag();
                        },
                    });
                    this.columnSortableInstances.push({ instance, variant });
                },

                destroyColumnSortables() {
                    this.columnSortableInstances.forEach(item => item.instance.destroy());
                    this.columnSortableInstances = [];
                },

                moveColumnByStep(columnId, direction) {
                    const currentIndex = this.columns.findIndex(column => Number(column.id) === Number(columnId));
                    return this.moveColumnToIndex(columnId, currentIndex + Number(direction));
                },

                moveColumnToIndex(columnId, position) {
                    if (!this.canEdit || this.columnMovePending || this.taskMovePending) return;
                    const currentIndex = this.columns.findIndex(column => Number(column.id) === Number(columnId));
                    const targetIndex = Number(position);
                    if (!Number.isInteger(targetIndex) || currentIndex < 0 || targetIndex < 0
                        || targetIndex >= this.columns.length || currentIndex === targetIndex) return;

                    const orderedIds = this.columns.map(column => Number(column.id));
                    const [movedId] = orderedIds.splice(currentIndex, 1);
                    orderedIds.splice(targetIndex, 0, movedId);
                    return this.reorderColumns(orderedIds);
                },

                async reorderColumns(orderedIds) {
                    if (!this.canEdit || this.columnMovePending || this.taskMovePending) return;
                    const snapshot = this.columns.slice();
                    const activeColumnId = snapshot[this.activeColumnIndex]?.id;
                    const columnsById = new Map(snapshot.map(column => [Number(column.id), column]));
                    const reorderedColumns = orderedIds.map(id => columnsById.get(Number(id))).filter(Boolean);
                    if (reorderedColumns.length !== snapshot.length || new Set(reorderedColumns).size !== snapshot.length) return;
                    this.columns = reorderedColumns;
                    this.activeColumnIndex = Math.max(0, this.columns.findIndex(column => column.id === activeColumnId));
                    this.columnMovePending = true;
                    this.setSortablesDisabled(true);
                    this.setColumnSortablesDisabled(true);
                    this.$nextTick(() => this.scrollToColumn(this.activeColumnIndex, 'auto'));
                    try {
                        const response = await window.neovaFetch('{{ route("board.columns.reorder", [$workspace->slug, $project->slug], false) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ column_ids: orderedIds }),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || response.redirected) throw new Error(data.message || 'ترتیب ستون‌ها ذخیره نشد.');
                        this.showToast('ترتیب ستون‌ها ذخیره شد');
                    } catch (error) {
                        this.columns = snapshot;
                        this.activeColumnIndex = Math.max(0, this.columns.findIndex(column => column.id === activeColumnId));
                        this.$nextTick(() => this.scrollToColumn(this.activeColumnIndex, 'auto'));
                        this.showToast(error.message || 'ترتیب ستون‌ها ذخیره نشد.', 'error');
                        this.destroySortables();
                        this.destroyColumnSortables();
                        this.$nextTick(() => {
                            const variant = this.boardMediaQuery?.matches ? 'mobile' : 'desktop';
                            this.columns.forEach(column => this.initSortable(column.id, variant));
                            this.initColumnSortable(variant);
                        });
                    } finally {
                        this.columnMovePending = false;
                        this.setSortablesDisabled(false);
                        this.setColumnSortablesDisabled(false);
                    }
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
                        disabled: this.taskMovePending || this.columnMovePending || this.activeFilterCount() > 0 || !!this.boardSearchQuery.trim(),
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
                            self.realtimeDragActive = true;
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
                            let newIndex = evt.newDraggableIndex;
                            if (variant === 'mobile') {
                                toColId = self.columns[self.activeColumnIndex]?.id || fromColId;
                                if (toColId !== fromColId) {
                                    newIndex = self.mobileDropIndex(toColId, self.mobileDragLastY);
                                }
                                self.endMobileDrag();
                            }
                            // Return the dragged node before Alpine reconciles the task arrays.
                            const siblings = Array.from(evt.from.children).filter(child => child.matches('[data-id]') && child !== evt.item);
                            evt.from.insertBefore(evt.item, siblings[evt.oldDraggableIndex] || evt.from.querySelector('.board-empty-state'));
                            self.moveTask(fromColId, toColId, taskId, newIndex, evt.oldDraggableIndex);
                            self.finishRealtimeDrag();
                        }
                    });
                    this.sortableInstances.push({ instance, columnId, variant });
                },

                destroySortables() {
                    this.sortableInstances.forEach(item => item.instance.destroy());
                    this.sortableInstances = [];
                },

                setSortablesDisabled(disabled) {
                    const filtered = this.activeFilterCount() > 0 || !!this.boardSearchQuery.trim();
                    this.sortableInstances.forEach(item => item.instance.option('disabled', disabled || filtered || !this.canEdit));
                },

                setColumnSortablesDisabled(disabled) {
                    this.columnSortableInstances.forEach(item => item.instance.option('disabled', disabled));
                },

                async moveTask(fromColId, toColId, taskId, newIndex, oldIndex = null) {
                    if (!this.canEdit || this.taskMovePending) return;
                    this.flushMutationSnapshot();
                    const fromCol = this.columns.find(c => c.id === fromColId);
                    const toCol = this.columns.find(c => c.id === toColId);
                    if (!fromCol || !toCol) return;
                    const idx = fromCol.tasks.findIndex(t => t.dbId === taskId);
                    if (idx === -1) return;
                    if (fromColId === toColId && Number(oldIndex) === Number(newIndex)) return;

                    const moving = fromCol.tasks[idx];
                    if (fromCol.workflowRole !== 'active' && toCol.workflowRole === 'active') {
                        const overloaded = (moving.assignees || []).filter(name => this.columns
                            .filter(column => column.workflowRole === 'active')
                            .reduce((count, column) => count + column.tasks.filter(task => (task.assignees || []).includes(name)).length, 0) >= 3);
                        if (overloaded.length && !window.confirm(overloaded.join('، ') + ' هم‌اکنون حداقل ۳ وظیفه در حال انجام دارد. با این حال شروع شود؟')) return;
                    }
                    if (toCol.wipLimit && fromColId !== toColId && toCol.tasks.length >= Number(toCol.wipLimit)
                        && !window.confirm('ظرفیت پیشنهادی این ستون پر شده است. با این حال وظیفه منتقل شود؟')) return;

                    const snapshot = this.columns.map(column => ({
                        id: column.id,
                        tasks: column.tasks.slice(),
                    }));
                    const previousColumnIndex = this.activeColumnIndex;
                    const [task] = fromCol.tasks.splice(idx, 1);
                    const safeIndex = Math.max(0, Math.min(Number(newIndex) || 0, toCol.tasks.length));
                    toCol.tasks.splice(safeIndex, 0, task);
                    this.activeColumnIndex = this.columns.findIndex(column => column.id === toColId);
                    this.taskMovePending = true;
                    this.setSortablesDisabled(true);

                    try {
                        const response = await window.neovaFetch('{{ route("board.task.move", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', task.dbId), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ column_id: parseInt(toColId), position: safeIndex }),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || response.redirected) throw new Error(data.message || 'انتقال وظیفه انجام نشد.');
                        this.showToast(fromColId === toColId ? 'ترتیب وظیفه ذخیره شد' : 'وظیفه به ستون جدید منتقل شد');
                        if (this.boardMediaQuery?.matches) this.$nextTick(() => this.scrollToColumn(this.activeColumnIndex));
                    } catch (error) {
                        snapshot.forEach(savedColumn => {
                            const column = this.columns.find(item => item.id === savedColumn.id);
                            if (column) column.tasks = savedColumn.tasks.slice();
                        });
                        this.activeColumnIndex = previousColumnIndex;
                        this.showToast(error.message || 'انتقال وظیفه انجام نشد.', 'error');
                        this.destroySortables();
                        this.$nextTick(() => {
                            this.columns.forEach(column => {
                                this.initSortable(column.id, this.boardMediaQuery?.matches ? 'mobile' : 'desktop');
                            });
                        });
                    } finally {
                        this.taskMovePending = false;
                        this.flushMutationSnapshot();
                        this.setSortablesDisabled(false);
                    }
                },

                formFingerprint() {
                    const { id, workflowRole, comments, attachments, updatedAt, version, ...fields } = this.form;
                    const checklist = (fields.checklist || []).map((item, idx) => idx === this.editingCheckItemIndex && this.checkItemDraft.trim()
                        ? { ...item, text: this.checkItemDraft.trim() } : item);
                    return JSON.stringify({ ...fields, checklist, pendingCheckItem: (this.newCheckItem || '').trim() });
                },

                openAddModal(columnId, initialTitle = '') {
                    if (!this.canEdit) return;
                    this.editingTask = null;
                    this.editingDescription = false;
                    this.taskPropertiesOpen = false;
                    this.form = { id: '', title: initialTitle, description: '', priority: 'متوسط', assignees: [], columnId: columnId || this.columns[0]?.id, dueDate: '', dueTime: '', tags: [], checklist: [], comments: [], attachments: [], isBlocked: false, blockedReason: '', workflowRole: '' };
                    this.newCheckItem = '';
                    this.checklistComposerOpen = this.form.checklist.length > 0;
                    this.editingCheckItemIndex = null;
                    this.checkItemDraft = '';
                    this.newComment = '';
                    this.clearPendingFiles('description');
                    this.clearPendingFiles('comment');
                    this.attachmentFilter = 'all';
                    this.taskError = '';
                    this.modalLastFocused = document.activeElement;
                    this.modalSnapshot = null;
                    this.showUnsavedWarning = false;
                    this.extraTaskDetailsOpen = false;
                    this.showModal = true;
                    this.$nextTick(() => {
                        this.modalSnapshot = this.formFingerprint();
                        document.getElementById('task-title')?.focus();
                    });
                },

                openEditModal(task, columnId, { preserveFocus = false } = {}) {
                    this.flushMutationSnapshot();
                    const currentColumn = this.columns.find(column => column.tasks.some(item => Number(item.dbId) === Number(task.dbId)));
                    if (currentColumn) {
                        task = currentColumn.tasks.find(item => Number(item.dbId) === Number(task.dbId));
                        columnId = currentColumn.id;
                    }
                    this.editingTask = task.dbId;
                    this.editingDescription = false;
                    this.taskPropertiesOpen = false;
                    const taskAssignees = task.assignees || (task.assignee ? [task.assignee] : []);
                    this.form = {
                        id: task.id, title: task.title, description: task.description || '', priority: task.priority,
                        assignees: Array.from(taskAssignees), columnId: columnId, dueDate: task.dueDate || '', dueTime: task.dueTime || '',
                        tags: Array.from(task.tags || []), checklist: JSON.parse(JSON.stringify(task.checklist || [])),
                        comments: JSON.parse(JSON.stringify(task.comments || [])), attachments: JSON.parse(JSON.stringify(task.attachments || [])), isBlocked: !!task.isBlocked,
                        blockedReason: task.blockedReason || '', workflowRole: this.columns.find(c => c.id === columnId)?.workflowRole || '', updatedAt: task.updatedAt || null, version: task.version || null
                    };
                    this.newCheckItem = '';
                    this.checklistComposerOpen = this.form.checklist.length > 0;
                    this.editingCheckItemIndex = null;
                    this.checkItemDraft = '';
                    this.newComment = '';
                    this.clearPendingFiles('description');
                    this.clearPendingFiles('comment');
                    this.attachmentFilter = 'all';
                    this.taskError = '';
                    if (!this.showModal) this.modalLastFocused = document.activeElement;
                    this.modalSnapshot = null;
                    this.showUnsavedWarning = false;
                    this.extraTaskDetailsOpen = false;
                    this.showModal = true;
                    this.$nextTick(() => {
                        this.modalSnapshot = this.formFingerprint();
                        if (!preserveFocus) document.querySelector('.task-workspace-close')?.focus();
                    });
                },

                closeModal() {
                    this.showModal = false;
                    this.showUnsavedWarning = false;
                    this.closeJalaliDatePicker();
                    this.editingDescription = false;
                    this.taskError = '';
                    this.modalSnapshot = null;
                    this.clearPendingFiles('description');
                    this.clearPendingFiles('comment');
                    this.closeAttachmentPreview();
                    const target = this.modalLastFocused;
                    this.modalLastFocused = null;
                    this.$nextTick(() => target?.focus?.());
                },

                requestCloseModal() {
                    if (this.taskSaving) return;
                    const dirty = (this.modalSnapshot && this.formFingerprint() !== this.modalSnapshot) || this.pendingDescriptionFiles.length || this.pendingCommentFiles.length;
                    if (dirty) {
                        this.showUnsavedWarning = true;
                        return;
                    }
                    this.closeModal();
                },

                discardTaskChanges() {
                    this.modalSnapshot = null;
                    this.showUnsavedWarning = false;
                    this.closeModal();
                },

                openQuickComposer(columnId) {
                    if (!this.canEdit) return;
                    this.quickComposerColumnId = columnId;
                    this.quickTaskTitle = '';
                    this.$nextTick(() => {
                        const composer = Array.from(document.querySelectorAll('.board-quick-composer'))
                            .find(form => form.getClientRects().length > 0);
                        composer?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        composer?.querySelector('textarea')?.focus();
                    });
                },

                closeQuickComposer() {
                    this.quickComposerColumnId = null;
                    this.quickTaskTitle = '';
                },

                async createQuickTask(columnId, openDetails = false) {
                    if (!this.canEdit || this.quickTaskSaving) return;
                    if (openDetails) {
                        const title = this.quickTaskTitle.trim();
                        this.closeQuickComposer();
                        this.openAddModal(columnId, title);
                        return;
                    }
                    if (!this.quickTaskTitle.trim()) return;
                    const column = this.columns.find(item => String(item.id) === String(columnId));
                    if (!column) return;
                    this.quickTaskSaving = true;
                    try {
                        const response = await window.neovaFetch('{{ route("board.task.store", [$workspace->slug, $project->slug], false) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ column_id: Number(columnId), title: this.quickTaskTitle.trim(), assignees: [] }),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok || response.redirected) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'ایجاد وظیفه انجام نشد.');
                        const task = { id: data.display_id, dbId: data.id, title: data.title, description: data.description || '', priority: data.priority || 'متوسط', assignees: data.assignees || [], dueDate: data.due_date || '', dueTime: data.due_time?.slice(0, 5) || '', tags: data.tags || [], checklist: data.checklist || [], comments: data.comments || [], attachments: [], updatedAt: data.updated_at || null, version: data.edit_version };
                        column.tasks.push(task);
                        this.closeQuickComposer();
                        this.showToast('وظیفه اضافه شد');
                    } catch (error) {
                        this.showToast(error.message || 'ایجاد وظیفه انجام نشد.', 'error');
                    } finally {
                        this.quickTaskSaving = false;
                    }
                },

                handleTaskEscape(event) {
                    if (!this.showModal) return;
                    if (this.attachmentPreview) {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                        this.closeAttachmentPreview();
                        return;
                    }
                    this.requestCloseModal();
                },

                requestDeleteFromTaskModal() {
                    if (!this.editingTask || this.taskSaving) return;
                    const columnId = this.form.columnId;
                    const taskId = this.editingTask;
                    this.closeModal();
                    this.confirmDelete(columnId, taskId);
                },

                trapModalFocus(event) {
                    if (event.key !== 'Tab') return;
                    const dialog = event.currentTarget;
                    const focusable = Array.from(dialog.querySelectorAll('button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'))
                        .filter(element => element.getClientRects().length > 0);
                    if (!focusable.length) return;
                    const first = focusable[0];
                    const last = focusable[focusable.length - 1];
                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                },

                async saveTask() {
                    if (!this.canEdit || this.taskSaving) return;
                    this.flushMutationSnapshot();
                    if (!this.form.title.trim()) {
                        this.taskError = 'عنوان وظیفه را وارد کنید.';
                        this.$nextTick(() => document.getElementById('task-title')?.focus());
                        return;
                    }
                    this.finishCheckItemEdit();
                    this.addCheckItem({ focus: false });
                    this.taskSaving = true;
                    this.taskError = '';
                    const token = '{{ csrf_token() }}';
                    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' };

                    try {
                        if (this.editingTask) {
                            const sourceCol = this.columns.find(c => c.tasks.some(t => t.dbId === this.editingTask));
                            const targetCol = this.columns.find(c => c.id === this.form.columnId);
                            const task = sourceCol?.tasks.find(t => t.dbId === this.editingTask);
                            if (!task) throw new Error('وظیفه پیدا نشد.');
                            const payload = { title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: this.form.assignees, due_date: this.form.dueDate, due_time: this.form.dueDate ? this.form.dueTime : '', tags: this.form.tags, checklist: this.form.checklist, comments: this.form.comments, column_id: parseInt(this.form.columnId) };
                            const response = await window.neovaFetch('{{ route("board.task.update", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', task.dbId), { method: 'PUT', headers, body: JSON.stringify(payload) });
                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'ذخیره وظیفه انجام نشد.');
                            Object.assign(task, { title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: this.form.assignees.slice(), dueDate: this.form.dueDate, dueTime: this.form.dueTime, tags: this.form.tags.slice(), checklist: JSON.parse(JSON.stringify(this.form.checklist)), comments: JSON.parse(JSON.stringify(this.form.comments)), updatedAt: data.updated_at || data.updatedAt, version: data.edit_version });
                            this.form.updatedAt = task.updatedAt;
                            this.form.version = task.version;
                            if (sourceCol && targetCol && sourceCol.id !== targetCol.id) {
                                sourceCol.tasks = sourceCol.tasks.filter(item => item.dbId !== task.dbId);
                                targetCol.tasks.push(task);
                                this.activeColumnIndex = this.columns.findIndex(column => column.id === targetCol.id);
                                this.$nextTick(() => this.scrollToColumn(this.activeColumnIndex));
                            }
                            if (!await this.uploadQueuedDescription(task.dbId)) { this.taskError = 'برخی فایل‌ها بارگذاری نشدند. دوباره تلاش کنید.'; return; }
                            this.showToast('تغییرات ذخیره شد');
                        } else {
                            const col = this.columns.find(c => c.id === this.form.columnId);
                            if (!col) throw new Error('ستون وظیفه پیدا نشد.');
                            const payload = { column_id: parseInt(this.form.columnId), title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: this.form.assignees, due_date: this.form.dueDate, due_time: this.form.dueDate ? this.form.dueTime : '', tags: this.form.tags, checklist: this.form.checklist, comments: this.form.comments };
                            const res = await window.neovaFetch('{{ route("board.task.store", [$workspace->slug, $project->slug], false) }}', { method: 'POST', headers, body: JSON.stringify(payload) });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'ایجاد وظیفه انجام نشد.');
                            const createdTask = { id: data.display_id, dbId: data.id, title: data.title, description: data.description || '', priority: data.priority, assignees: data.assignees || [], dueDate: data.due_date || '', dueTime: data.due_time?.slice(0, 5) || '', tags: data.tags || [], checklist: data.checklist || [], comments: data.comments || [], attachments: [], updatedAt: data.updated_at || null, version: data.edit_version };
                            col.tasks.push(createdTask);
                            this.editingTask = data.id;
                            this.form.id = data.display_id;
                            this.form.updatedAt = data.updated_at || null;
                            this.form.version = data.edit_version;
                            if (!await this.uploadQueuedDescription(data.id)) { this.taskError = 'وظیفه ایجاد شد، اما برخی فایل‌ها بارگذاری نشدند. برای تلاش دوباره «ذخیره تغییرات» را بزنید.'; this.modalSnapshot = this.formFingerprint(); return; }
                            this.showToast('وظیفه جدید ایجاد شد');
                        }
                        this.modalSnapshot = null;
                        this.closeModal();
                    } catch (error) {
                        this.taskError = error.message || 'ذخیره وظیفه انجام نشد.';
                    } finally {
                        this.taskSaving = false;
                        this.flushMutationSnapshot();
                    }
                },

                async stateTask(action) {
                    if (!this.canEdit || !this.editingTask || this.taskSaving) return;
                    let reason = null;
                    if (action === 'block') {
                        reason = window.prompt('دلیل انسداد چیست؟', this.form.blockedReason || '');
                        if (!reason || !reason.trim()) return;
                    }
                    this.taskSaving = true;
                    try {
                        const response = await window.neovaFetch('{{ route("today.task.state", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', this.editingTask), {
                            method: 'PATCH', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                            body: JSON.stringify({ action, reason }),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'عملیات انجام نشد.');
                        const source = this.columns.find(column => column.tasks.some(task => task.dbId === this.editingTask));
                        const localTask = source?.tasks.find(task => task.dbId === this.editingTask);
                        const target = this.columns.find(column => Number(column.id) === Number(data.task.column.id));
                        if (localTask) {
                            localTask.isBlocked = data.task.isBlocked;
                            localTask.blockedReason = data.task.blockedReason;
                            localTask.completedAt = data.task.completedAt;
                            if (source && target && source.id !== target.id) { source.tasks = source.tasks.filter(task => task.dbId !== localTask.dbId); target.tasks.push(localTask); }
                        }
                        this.closeModal(); this.showToast(action === 'complete' ? 'وظیفه انجام شد' : 'وضعیت وظیفه تغییر کرد');
                    } catch (error) { this.taskError = error.message; }
                    finally { this.taskSaving = false; }
                },

                formatFileSize(bytes) {
                    const value = Number(bytes) || 0;
                    return value < 1024 * 1024 ? Math.max(1, Math.round(value / 1024)) + ' KB' : (value / 1024 / 1024).toFixed(1) + ' MB';
                },

                attachmentCategory(file) {
                    const type = (file.type || file.mimeType || '').toLowerCase();
                    const extension = (file.name || '').split('.').pop().toLowerCase();
                    if (type.startsWith('image/') && extension !== 'svg') return 'image';
                    if (type === 'application/pdf' || extension === 'pdf') return 'pdf';
                    if (type.startsWith('audio/')) return 'audio';
                    if (type.startsWith('video/')) return 'video';
                    if (type.startsWith('text/') || ['txt','csv','md','json'].includes(extension)) return 'text';
                    if (['zip','rar','7z','tar','gz'].includes(extension)) return 'archive';
                    return 'document';
                },

                attachmentLabel(item) {
                    return ({ image: 'IMG', pdf: 'PDF', audio: 'صوت', video: 'ویدئو', text: 'TXT', archive: 'ZIP', document: 'DOC' })[item.category || this.attachmentCategory(item)] || 'FILE';
                },

                attachmentStatusText(item) {
                    if (item.status === 'uploading') return `در حال بارگذاری · ${this.toPersianDigits(item.progress)}٪`;
                    if (item.status === 'error') return item.error || 'بارگذاری ناموفق؛ برای تلاش دوباره ذخیره کنید.';
                    return `${this.formatFileSize(item.size)} · آماده بارگذاری`;
                },

                queueAttachmentFiles(fileList, target) {
                    const files = Array.from(fileList || []);
                    if (!files.length) return;
                    const list = target === 'comment' ? this.pendingCommentFiles : this.pendingDescriptionFiles;
                    const allowed = ['jpg','jpeg','png','gif','webp','bmp','heic','pdf','txt','csv','md','json','doc','docx','xls','xlsx','ppt','pptx','odt','ods','odp','mp3','wav','m4a','ogg','mp4','webm','mov','zip','rar','7z','tar','gz'];
                    if (list.length + files.length > 10) { this.showToast('در هر بار حداکثر ۱۰ فایل انتخاب کنید.'); return; }
                    if (files.some(file => file.size > 25 * 1024 * 1024)) { this.showToast('حجم هر فایل باید حداکثر ۲۵ مگابایت باشد.'); return; }
                    if (list.reduce((sum, item) => sum + item.size, 0) + files.reduce((sum, file) => sum + file.size, 0) > 100 * 1024 * 1024) { this.showToast('حجم مجموع فایل‌ها نباید بیشتر از ۱۰۰ مگابایت باشد.'); return; }
                    if (files.some(file => !allowed.includes((file.name.split('.').pop() || '').toLowerCase()))) { this.showToast('یکی از فایل‌های انتخاب‌شده از نوع مجاز نیست.'); return; }
                    files.forEach(file => list.push({ localId: `${Date.now()}-${Math.random()}`, file, name: file.name, size: file.size, type: file.type, category: this.attachmentCategory(file), previewUrl: URL.createObjectURL(file), status: 'queued', progress: 0, error: '', xhr: null }));
                    if (target === 'description' && this.editingTask) this.uploadQueuedDescription(this.editingTask);
                },

                handleAttachmentPaste(event, target) {
                    const files = Array.from(event.clipboardData?.files || []);
                    if (files.length) this.queueAttachmentFiles(files, target);
                },

                removePendingAttachment(item, target) {
                    item.xhr?.abort();
                    if (item.previewUrl?.startsWith('blob:')) URL.revokeObjectURL(item.previewUrl);
                    const key = target === 'comment' ? 'pendingCommentFiles' : 'pendingDescriptionFiles';
                    this[key] = this[key].filter(candidate => candidate.localId !== item.localId);
                },

                clearPendingFiles(target) {
                    const key = target === 'comment' ? 'pendingCommentFiles' : 'pendingDescriptionFiles';
                    this[key].forEach(item => { item.xhr?.abort(); if (item.previewUrl?.startsWith('blob:')) URL.revokeObjectURL(item.previewUrl); });
                    this[key] = [];
                },

                async uploadQueuedDescription(taskId) {
                    const queue = this.pendingDescriptionFiles.filter(item => ['queued', 'error'].includes(item.status));
                    if (!queue.length) return true;
                    this.attachmentUploading = true;
                    for (const item of queue) {
                        try {
                            const data = await this.uploadDescriptionItem(item, taskId);
                            this.form.attachments.push(...(data.attachments || []));
                            const task = this.columns.flatMap(column => column.tasks).find(candidate => Number(candidate.dbId) === Number(taskId));
                            if (task) task.attachments = JSON.parse(JSON.stringify(this.form.attachments));
                            this.removePendingAttachment(item, 'description');
                        } catch (error) {
                            if (item.status !== 'cancelled') { item.status = 'error'; item.error = error.message; }
                        }
                    }
                    this.attachmentUploading = false;
                    return this.pendingDescriptionFiles.length === 0;
                },

                uploadDescriptionItem(item, taskId) {
                    return new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest(); item.xhr = xhr; item.status = 'uploading'; item.progress = 0; item.error = '';
                        const body = new FormData(); body.append('context', 'description'); body.append('files[]', item.file);
                        xhr.open('POST', '{{ route("task.attachments.store", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', taskId));
                        xhr.setRequestHeader('Accept', 'application/json'); xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                        const socketId = window.Echo?.socketId?.(); if (socketId) xhr.setRequestHeader('X-Socket-ID', socketId);
                        xhr.timeout = 30000; xhr.ontimeout = () => reject(new Error('ارتباط کند است. وضعیت را بررسی و دوباره تلاش کنید.'));
                        xhr.upload.onprogress = event => { if (event.lengthComputable) item.progress = Math.round(event.loaded / event.total * 100); };
                        xhr.onload = () => { try { const data = JSON.parse(xhr.responseText || '{}'); xhr.status >= 200 && xhr.status < 300 ? resolve(window.neovaMutationResponse(data)) : reject(new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'بارگذاری انجام نشد.')); } catch { reject(new Error('پاسخ سرور معتبر نیست.')); } };
                        xhr.onerror = () => reject(new Error('ارتباط هنگام بارگذاری قطع شد.'));
                        xhr.onabort = () => { item.status = 'cancelled'; reject(new Error('بارگذاری لغو شد.')); };
                        xhr.send(body);
                    });
                },

                filteredAttachments() {
                    if (this.attachmentFilter === 'all') return this.form.attachments;
                    if (this.attachmentFilter === 'media') return this.form.attachments.filter(item => ['audio', 'video'].includes(item.category));
                    if (this.attachmentFilter === 'document') return this.form.attachments.filter(item => ['document', 'pdf', 'text'].includes(item.category));
                    return this.form.attachments.filter(item => item.category === this.attachmentFilter);
                },

                descriptionAttachments() {
                    return this.form.attachments.filter(item => item.context !== 'comment');
                },

                openAttachmentPreview(attachment) { this.attachmentPreview = attachment; },
                closeAttachmentPreview() { this.attachmentPreview = null; },

                async deleteAttachment(attachment) {
                    if (!window.confirm('این پیوست حذف شود؟')) return;
                    try {
                        const url = '{{ route("task.attachments.destroy", [$workspace->slug, $project->slug, "__TASK__", "__ATTACHMENT__"], false) }}'.replace('__TASK__', this.editingTask).replace('__ATTACHMENT__', attachment.id);
                        const response = await window.neovaFetch(url, { method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'} });
                        if (!response.ok) throw new Error('حذف پیوست انجام نشد.');
                        this.form.attachments = this.form.attachments.filter(item => item.id !== attachment.id);
                        this.form.comments.forEach(comment => comment.attachments = (comment.attachments || []).filter(item => item.id !== attachment.id));
                        const task = this.columns.flatMap(column => column.tasks).find(item => item.dbId === this.editingTask);
                        if (task) { task.attachments = JSON.parse(JSON.stringify(this.form.attachments)); task.comments = JSON.parse(JSON.stringify(this.form.comments)); }
                    } catch (error) { this.taskError = error.message; }
                },

                openColumnModal() {
                    if (!this.canEdit) return;
                    this.columnEditingId = null;
                    this.columnFormTitle = '';
                    this.columnFormColor = '#8B938E';
                    this.columnFormWipLimit = '';
                    this.columnError = '';
                    this.showColumnModal = true;
                    this.$nextTick(() => this.$refs.columnTitle?.focus());
                },

                openEditColumnModal(column) {
                    if (!this.canEdit) return;
                    this.columnEditingId = column.id;
                    this.columnFormTitle = column.title;
                    this.columnFormColor = column.dotHex || '#8B938E';
                    this.columnFormWipLimit = column.wipLimit || '';
                    this.columnError = '';
                    this.showColumnModal = true;
                    this.$nextTick(() => this.$refs.columnTitle?.focus());
                },

                closeColumnModal() {
                    this.showColumnModal = false;
                    this.columnFormTitle = '';
                    this.columnEditingId = null;
                    this.columnFormColor = '#8B938E';
                    this.columnFormWipLimit = '';
                },

                async addColumn() {
                    if (!this.canEdit || this.columnSaving) return;
                    if (!this.columnFormTitle.trim()) {
                        this.columnError = 'نام ستون را وارد کنید.';
                        return;
                    }
                    this.columnSaving = true;
                    this.columnError = '';
                    const editing = Boolean(this.columnEditingId);
                    const url = editing
                        ? '{{ route("board.column.update", [$workspace->slug, $project->slug, "__COLUMN__"], false) }}'.replace('__COLUMN__', this.columnEditingId)
                        : '{{ route("board.column.store", [$workspace->slug, $project->slug], false) }}';
                    try {
                        const response = await window.neovaFetch(url, {
                            method: editing ? 'PATCH' : 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify(editing ? { title: this.columnFormTitle.trim(), color: this.columnFormColor, wip_limit: this.columnFormWipLimit || null } : { project_id: {{ $project->id }}, title: this.columnFormTitle.trim(), color: this.columnFormColor, wip_limit: this.columnFormWipLimit || null }),
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'ذخیره ستون انجام نشد.');
                        if (editing) {
                            const column = this.columns.find(item => item.id === String(data.id));
                            if (column) { column.title = data.title; column.dotHex = data.color || this.columnFormColor; column.wipLimit = data.wip_limit || null; }
                        } else {
                            this.columns.push({ id: String(data.id), title: data.title, dotColor: 'bg-[#94A3B8]', dotHex: data.color || this.columnFormColor, wipLimit: data.wip_limit || null, badgeClass: 'bg-[#F1F5F9] text-[#64748B]', tasks: [], collapsed: false });
                        }
                        this.closeColumnModal();
                        this.showToast(editing ? 'تغییرات ستون ذخیره شد' : 'ستون جدید اضافه شد');
                        if (!editing) this.$nextTick(() => { this.initSortable(String(data.id), this.boardMediaQuery?.matches ? 'mobile' : 'desktop'); if (this.boardMediaQuery?.matches) this.initMobileBoardObserver(); });
                    } catch (error) {
                        this.columnError = error.message || 'ذخیره ستون انجام نشد.';
                    } finally {
                        this.columnSaving = false;
                        this.flushMutationSnapshot();
                    }
                },

                confirmDeleteColumn(column) {
                    if (!this.canEdit || this.columns.length <= 1) return;
                    this.columnDeleteTarget = { id: column.id, title: column.title, taskCount: column.tasks.length };
                    this.showColumnDeleteModal = true;
                },

                async deleteColumn() {
                    if (!this.canEdit || this.columnDeleting || !this.columnDeleteTarget.id || this.columns.length <= 1) return;
                    const columnId = this.columnDeleteTarget.id;
                    this.columnDeleting = true;
                    this.columnError = '';
                    try {
                        const response = await window.neovaFetch('{{ route("board.column.destroy", [$workspace->slug, $project->slug, "__COLUMN__"], false) }}'.replace('__COLUMN__', columnId), {
                            method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'حذف ستون انجام نشد.');
                        this.columns = this.columns.filter(column => column.id !== columnId);
                        this.activeColumnIndex = Math.max(0, Math.min(this.activeColumnIndex, this.columns.length - 1));
                        this.showColumnDeleteModal = false;
                        this.columnDeleteTarget = { id: null, title: '', taskCount: 0 };
                        this.destroySortables();
                        this.$nextTick(() => this.columns.forEach(column => this.initSortable(column.id, this.boardMediaQuery?.matches ? 'mobile' : 'desktop')));
                        this.showToast('ستون حذف شد');
                    } catch (error) {
                        this.columnError = error.message || 'حذف ستون انجام نشد.';
                    } finally {
                        this.columnDeleting = false;
                    }
                },

                confirmDelete(columnId, taskId) {
                    if (!this.canEdit) return;
                    this.deleteTarget = { columnId, taskId };
                    this.showDeleteModal = true;
                },

                async deleteTask() {
                    if (!this.canEdit || this.taskDeleting) return;
                    this.taskDeleting = true;
                    const col = this.columns.find(c => c.id === this.deleteTarget.columnId);
                    try {
                        if (!col) throw new Error('وظیفه پیدا نشد.');
                        const task = col.tasks.find(t => t.dbId === this.deleteTarget.taskId);
                        if (!task) throw new Error('وظیفه پیدا نشد.');
                        const response = await window.neovaFetch('{{ route("board.task.destroy", [$workspace->slug, $project->slug, "__TASK__"], false) }}'.replace('__TASK__', task.dbId), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(data.message || 'حذف وظیفه انجام نشد.');
                        col.tasks = col.tasks.filter(t => t.dbId !== this.deleteTarget.taskId);
                        this.showToast('وظیفه حذف شد');
                        this.showDeleteModal = false;
                        this.deleteTarget = { columnId: null, taskId: null };
                    } catch (error) {
                        this.showToast(error.message || 'حذف وظیفه انجام نشد.', 'error');
                    } finally {
                        this.taskDeleting = false;
                    }
                },

                showToast(message, type = 'success') {
                    clearTimeout(this.toastTimer);
                    this.toast = { show: true, message, type };
                    if (type === 'success') this.toastTimer = setTimeout(() => { this.toast.show = false; }, 4000);
                }
            };
        }
    </script>
    </x-workspace-shell>
    @stack('scripts')
</body>
</html>
