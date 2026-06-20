<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تخته اسکرام</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-white.png') }}">
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
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen" x-data="board()" x-cloak>

    {{-- Top Navigation Bar --}}
    <header class="bg-[#003B8E] shadow-lg shadow-[#003B8E]/20 sticky top-0 z-40">
        <div class="max-w-[1600px] mx-auto px-5 h-14 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard', ['workspace' => $workspace->slug]) }}" class="text-blue-200 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="h-5 w-px bg-white/20"></div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#0069FF] flex items-center justify-center shadow-md shadow-[#0069FF]/30">
                        <span class="text-white font-bold text-xs">{{ $project->key }}</span>
                    </div>
                    <div>
                        <span class="text-white font-bold text-[15px]">{{ $project->name }}</span>
                        <span class="text-blue-200 text-[10px] mr-2">{{ $workspace->name }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-blue-200 text-xs" x-text="totalTasks() + ' وظیفه'"></span>
                <div class="h-5 w-px bg-white/20"></div>
                <x-notification-menu />
                @if ($canEdit)
                    <button
                        @click="openAddModal(columns[0]?.id)"
                        class="flex items-center gap-1.5 bg-[#0069FF] hover:bg-[#4D99FF] text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition-all duration-150 shadow-md shadow-[#0069FF]/25 hover:shadow-lg hover:shadow-[#0069FF]/30 active:scale-[0.97]"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        وظیفه جدید
                    </button>
                @else
                    <span class="text-[10px] font-bold text-blue-100 bg-white/10 rounded-md px-2.5 py-1.5">فقط مشاهده</span>
                @endif
            </div>
        </div>
    </header>

    {{-- Board --}}
    <main class="max-w-[1600px] mx-auto px-5 py-5">
        <div class="grid grid-cols-4 gap-4 items-start" style="direction: rtl;">
            <template x-for="(column, colIdx) in columns" :key="column.id">
                <div class="flex flex-col">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <div class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-full shadow-sm" :class="column.dotColor"></span>
                            <h2 class="text-[13px] font-bold text-[#1A1D21]" x-text="column.title"></h2>
                            <span class="text-[10px] font-bold min-w-[20px] text-center px-1.5 py-0.5 rounded-full" :class="column.badgeClass" x-text="column.tasks.length"></span>
                        </div>
                        @if ($canEdit)
                            <button @click="openAddModal(column.id)" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-[#0069FF] hover:bg-[#E8F0FE] transition-all" title="افزودن وظیفه">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        @endif
                    </div>

                    <div class="flex flex-col gap-2.5 min-h-[200px] max-h-[calc(100vh-11rem)] overflow-y-auto rounded-xl p-2 bg-[#E2E8F0]/50 border border-[#CBD5E1]/40" :id="'col-' + column.id" x-init="$nextTick(() => initSortable(column.id))">
                        <template x-for="task in column.tasks" :key="task.id">
                            <div class="bg-white rounded-xl border border-[#E2E8F0] p-3.5 cursor-grab active:cursor-grabbing hover:border-[#0069FF]/30 hover:shadow-md hover:shadow-[#0069FF]/8 transition-all duration-150 group relative" :data-id="task.id" :data-column="column.id" @click="openEditModal(task, column.id)">
                                <div class="absolute top-0 right-0 w-1 h-full rounded-r-xl" :class="{ 'bg-[#EF4444]': task.priority === 'بالا', 'bg-[#F59E0B]': task.priority === 'متوسط', 'bg-[#22C55E]': task.priority === 'پایین' }"></div>
                                <div class="pr-2">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-bold text-[#94A3B8] tracking-wider" x-text="task.id"></span>
                                        <div class="flex gap-1">
                                            <template x-for="tag in task.tags" :key="tag">
                                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md" :class="getTagClass(tag)" x-text="tag"></span>
                                            </template>
                                        </div>
                                    </div>
                                    <p class="text-[13px] font-bold text-[#1A1D21] mb-1.5 leading-relaxed" x-text="task.title"></p>
                                    <p x-show="task.description" class="text-[11px] text-[#64748B] leading-relaxed line-clamp-2 mb-2.5" x-text="task.description"></p>
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
                                        <button @click.stop="confirmDelete(column.id, task.id)" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-red-500 hover:bg-red-50 transition-all" title="حذف">
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
        </div>
    </main>

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
                            <div x-show="!editingDescription" @click="if (canEdit) editingDescription = true" class="min-h-[32px]" :class="[(canEdit ? 'cursor-pointer' : 'cursor-default'), form.description ? 'text-sm text-[#475569] leading-relaxed whitespace-pre-wrap' : 'text-sm text-[#CBD5E1]']" x-text="form.description || 'توضیحی ثبت نشده'"></div>
                            <div x-show="editingDescription" x-transition>
                                <textarea
                                    x-model="form.description"
                                    x-init="$nextTick(() => $el.focus())"
                                    rows="4"
                                    class="w-full text-sm text-[#1A1D21] border-2 border-[#0069FF] rounded-lg px-3 py-2 focus:outline-none transition-colors resize-none leading-relaxed"
                                    placeholder="توضیحات وظیفه را بنویسید..."
                                    @keydown.escape="editingDescription = false"
                                ></textarea>
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
                                            <p class="text-[12px] text-[#475569] leading-relaxed" x-text="comment.text"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            @if ($canEdit)
                            <div class="mt-3 flex gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shrink-0 shadow-sm">
                                    <span class="text-[9px] text-white font-bold">ش</span>
                                </div>
                                <div class="flex-1">
                                    <textarea
                                        x-model="newComment"
                                        rows="2"
                                        class="w-full text-sm border-2 border-[#E2E8F0] rounded-xl px-3 py-2 focus:outline-none focus:border-[#0069FF] transition-colors resize-none placeholder:text-[#CBD5E1]"
                                        placeholder="پیام بنویسید..."
                                        @keydown.meta.enter="addComment()"
                                        @keydown.ctrl.enter="addComment()"
                                    ></textarea>
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
                                <template x-for="p in [{name:'بالا', color:'bg-red-500'}, {name:'متوسط', color:'bg-amber-500'}, {name:'پایین', color:'bg-green-500'}]" :key="p.name">
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

            return {
                canEdit: @json($canEdit),
                showModal: false,
                showDeleteModal: false,
                editingTask: null,
                editingDescription: false,
                deleteTarget: { columnId: null, taskId: null },
                toast: { show: false, message: '' },
                newCheckItem: '',
                newComment: '',
                form: { id: '', title: '', description: '', priority: 'متوسط', assignees: [], columnId: '', dueDate: '', tags: [], checklist: [], comments: [] },

                assignees: serverMembers,

                allTags: [
                    { name: 'طراحی', activeClass: 'border-purple-400 bg-purple-50 text-purple-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-purple-200 hover:text-purple-500' },
                    { name: 'توسعه', activeClass: 'border-blue-400 bg-blue-50 text-blue-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-blue-200 hover:text-blue-500' },
                    { name: 'بک‌اند', activeClass: 'border-amber-400 bg-amber-50 text-amber-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-amber-200 hover:text-amber-500' },
                    { name: 'فرانت‌اند', activeClass: 'border-green-400 bg-green-50 text-green-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-green-200 hover:text-green-500' },
                    { name: 'باگ', activeClass: 'border-red-400 bg-red-50 text-red-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-red-200 hover:text-red-500' },
                    { name: 'بهبود', activeClass: 'border-teal-400 bg-teal-50 text-teal-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-teal-200 hover:text-teal-500' },
                ],

                columns: serverColumns,
                sortableInstances: [],

                totalTasks() { return this.columns.reduce((sum, col) => sum + col.tasks.length, 0); },

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

                addComment() {
                    if (!this.newComment.trim()) return;
                    this.form.comments.push({ author: '{{ auth()->user()->full_name }}', text: this.newComment.trim(), time: 'همین الان' });
                    this.newComment = '';
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

                initSortable(columnId) {
                    if (!this.canEdit) return;
                    const el = document.getElementById('col-' + columnId);
                    if (!el) return;
                    const self = this;
                    const instance = new Sortable(el, {
                        group: 'tasks', animation: 200, ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen', dragClass: 'sortable-drag', direction: 'rtl', draggable: '[data-id]', delay: 50, delayOnTouchOnly: true,
                        onEnd(evt) {
                            const taskId = evt.item.getAttribute('data-id');
                            const fromColId = evt.from.id.replace('col-', '');
                            const toColId = evt.to.id.replace('col-', '');
                            self.moveTask(fromColId, toColId, taskId, evt.newIndex);
                        }
                    });
                    this.sortableInstances.push(instance);
                },

                moveTask(fromColId, toColId, taskId, newIndex) {
                    if (!this.canEdit) return;
                    const fromCol = this.columns.find(c => c.id === fromColId);
                    const toCol = this.columns.find(c => c.id === toColId);
                    if (!fromCol || !toCol) return;
                    const idx = fromCol.tasks.findIndex(t => t.id === taskId);
                    if (idx === -1) return;
                    const [task] = fromCol.tasks.splice(idx, 1);
                    toCol.tasks.splice(newIndex, 0, task);

                    fetch('{{ route("board.task.move", [$workspace->slug, $project->slug, "__TASK__"]) }}'.replace('__TASK__', task.dbId), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ column_id: parseInt(toColId), position: newIndex }),
                    });
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
                    this.editingTask = task.id;
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
                        const col = this.columns.find(c => c.id === this.form.columnId);
                        const task = col?.tasks.find(t => t.id === this.editingTask);
                        if (task) {
                            const payload = { title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: this.form.assignees, due_date: this.form.dueDate, tags: this.form.tags, checklist: this.form.checklist, comments: this.form.comments, column_id: parseInt(this.form.columnId) };
                            const res = await fetch('{{ route("board.task.update", [$workspace->slug, $project->slug, "__TASK__"]) }}'.replace('__TASK__', task.dbId), { method: 'PUT', headers, body: JSON.stringify(payload) });
                            const data = await res.json();
                            Object.assign(task, { title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: [...this.form.assignees], dueDate: this.form.dueDate, tags: [...this.form.tags], checklist: JSON.parse(JSON.stringify(this.form.checklist)), comments: JSON.parse(JSON.stringify(this.form.comments)) });
                        }
                        this.showToast('تغییرات ذخیره شد');
                    } else {
                        const col = this.columns.find(c => c.id === this.form.columnId);
                        if (col) {
                            const payload = { column_id: parseInt(this.form.columnId), title: this.form.title, description: this.form.description, priority: this.form.priority, assignees: this.form.assignees, due_date: this.form.dueDate, tags: this.form.tags, checklist: this.form.checklist, comments: this.form.comments };
                            const res = await fetch('{{ route("board.task.store", [$workspace->slug, $project->slug]) }}', { method: 'POST', headers, body: JSON.stringify(payload) });
                            const data = await res.json();
                            col.tasks.push({ id: data.title, dbId: data.id, title: data.title, description: data.description || '', priority: data.priority, assignees: data.assignees || [], dueDate: data.due_date || '', tags: data.tags || [], checklist: data.checklist || [], comments: data.comments || [] });
                            this.showToast('وظیفه جدید ایجاد شد');
                        }
                    }
                    this.showModal = false;
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
                        const task = col.tasks.find(t => t.id === this.deleteTarget.taskId);
                        if (task) {
                            await fetch('{{ route("board.task.destroy", [$workspace->slug, $project->slug, "__TASK__"]) }}'.replace('__TASK__', task.dbId), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
                        }
                        col.tasks = col.tasks.filter(t => t.id !== this.deleteTarget.taskId);
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
