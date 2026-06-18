<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تخته اسکرام</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        @font-face { font-family: 'Estedad'; src: url('/assets/fonts/estedad/Estedad-Thin.woff2') format('woff2'); font-weight: 100; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Estedad'; src: url('/assets/fonts/estedad/Estedad-Light.woff2') format('woff2'); font-weight: 300; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Estedad'; src: url('/assets/fonts/estedad/Estedad-Medium.woff2') format('woff2'); font-weight: 500; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Estedad'; src: url('/assets/fonts/estedad/Estedad-Bold.woff2') format('woff2'); font-weight: 700; font-style: normal; font-display: swap; }
        @font-face { font-family: 'Estedad'; src: url('/assets/fonts/estedad/Estedad-Black.woff2') format('woff2'); font-weight: 900; font-style: normal; font-display: swap; }

        @theme { --font-sans: 'Estedad', ui-sans-serif, system-ui, sans-serif; }
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Estedad';
        }
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
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-[#0069FF] flex items-center justify-center shadow-md shadow-[#0069FF]/30">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    </div>
                    <span class="text-white font-bold text-[15px]">تخته اسکرام</span>
                </div>
                <div class="h-5 w-px bg-white/20"></div>
                <span class="text-blue-200 text-xs font-medium">اسپرینت ۱۲</span>
                <span class="bg-[#0069FF]/40 text-blue-100 text-[10px] font-bold px-2 py-0.5 rounded-full">فعال</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-blue-200 text-xs" x-text="totalTasks() + ' وظیفه'"></span>
                <div class="h-5 w-px bg-white/20"></div>
                <button
                    @click="openAddModal('backlog')"
                    class="flex items-center gap-1.5 bg-[#0069FF] hover:bg-[#4D99FF] text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition-all duration-150 shadow-md shadow-[#0069FF]/25 hover:shadow-lg hover:shadow-[#0069FF]/30 active:scale-[0.97]"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    وظیفه جدید
                </button>
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
                        <button @click="openAddModal(column.id)" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-[#0069FF] hover:bg-[#E8F0FE] transition-all" title="افزودن وظیفه">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        </button>
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
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shadow-sm">
                                                <span class="text-[8px] text-white font-bold" x-text="task.assignee ? task.assignee.charAt(0) : ''"></span>
                                            </div>
                                            <span class="text-[10px] text-[#64748B] font-medium" x-text="task.assignee"></span>
                                        </div>
                                        <div class="flex items-center gap-1" x-show="task.dueDate">
                                            <svg class="w-3 h-3 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <span class="text-[10px] font-medium" :class="isOverdue(task.dueDate) ? 'text-red-500' : 'text-[#94A3B8]'" x-text="formatDate(task.dueDate)"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute top-3 left-2 flex gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click.stop="confirmDelete(column.id, task.id)" class="w-6 h-6 rounded-md flex items-center justify-center text-[#94A3B8] hover:text-red-500 hover:bg-red-50 transition-all" title="حذف">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
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
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-start justify-center pt-10 pb-10 px-4 overflow-y-auto"
        @keydown.escape.window="closeModal()"
        x-effect="if (showModal) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open') }"
    >
        <div class="absolute inset-0 bg-[#0A1628]/60 backdrop-blur-sm" @click="closeModal()"></div>

        <div
            class="relative bg-white w-full max-w-[820px] rounded-2xl shadow-2xl shadow-black/25 overflow-hidden"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            @click.stop
        >
            {{-- Header --}}
            <div class="bg-gradient-to-l from-[#003B8E] to-[#0069FF] px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="text-white font-bold text-sm" x-text="editingTask ? 'ویرایش وظیفه' : 'وظیفه جدید'"></h3>
                    <span x-show="editingTask" class="text-blue-200 text-[10px] font-bold bg-white/15 px-2 py-0.5 rounded-md" x-text="form.id"></span>
                </div>
                <button @click="closeModal()" class="text-white/70 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body: Sidebar + Main --}}
            <div class="flex" style="direction: rtl;">
                {{-- Main Content Area --}}
                <div class="flex-1 min-w-0 p-6 space-y-6">
                    {{-- Title --}}
                    <div>
                        <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">عنوان</label>
                        <input
                            x-model="form.title"
                            type="text"
                            class="w-full text-base font-bold text-[#1A1D21] border-b-2 border-[#E2E8F0] pb-2 focus:outline-none focus:border-[#0069FF] transition-colors bg-transparent placeholder:text-[#CBD5E1]"
                            placeholder="عنوان وظیفه..."
                        >
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">توضیحات</label>
                        <div x-show="!editingDescription" @click="editingDescription = true" class="cursor-pointer" x-text="form.description || 'افزودن توضیحات详细...'" :class="form.description ? 'text-sm text-[#475569] leading-relaxed whitespace-pre-wrap' : 'text-sm text-[#CBD5E1]'"></div>
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
                                        <input type="checkbox" x-model="item.done" class="w-4 h-4 rounded border-2 border-[#CBD5E1] text-[#0069FF] focus:ring-[#0069FF]/20 cursor-pointer accent-[#0069FF]">
                                        <span class="text-sm text-[#1A1D21] transition-all" x-text="item.text"></span>
                                    </label>
                                    <button @click="removeCheckItem(idx)" class="opacity-0 group-hover/item:opacity-100 text-[#94A3B8] hover:text-red-500 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <div class="mt-2">
                            <input
                                x-model="newCheckItem"
                                @keydown.enter="addCheckItem()"
                                type="text"
                                class="w-full text-sm border border-[#E2E8F0] rounded-lg px-3 py-2 focus:outline-none focus:border-[#0069FF] transition-colors placeholder:text-[#CBD5E1]"
                                placeholder="افزودن آیتم..."
                            >
                        </div>
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
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="w-[240px] shrink-0 bg-[#F8FAFC] border-r border-[#F1F5F9] p-4 space-y-4" style="border-right: 1px solid #F1F5F9;">
                    {{-- Column --}}
                    <div>
                        <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">ستون</label>
                        <select x-model="form.columnId" class="w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#0069FF] transition-colors bg-white">
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
                                    <input type="radio" :value="p.name" x-model="form.priority" class="hidden">
                                    <span class="w-2 h-2 rounded-full" :class="p.color"></span>
                                    <span class="font-semibold" :class="form.priority === p.name ? 'text-[#003B8E]' : 'text-[#64748B]'" x-text="p.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Due Date --}}
                    <div>
                        <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">سررسید</label>
                        <input x-model="form.dueDate" type="date" class="w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#0069FF] transition-colors bg-white">
                        <p x-show="form.dueDate && isOverdue(form.dueDate)" class="text-[10px] text-red-500 font-bold mt-1">سررسید گذشته</p>
                    </div>

                    {{-- Assignee --}}
                    <div>
                        <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">مسئول</label>
                        <div class="space-y-1">
                            <template x-for="name in assignees" :key="name">
                                <label class="flex items-center gap-2 text-[11px] cursor-pointer px-2.5 py-1.5 rounded-lg transition-all duration-150" :class="form.assignee === name ? 'bg-[#E8F0FE]' : 'hover:bg-white'">
                                    <input type="radio" :value="name" x-model="form.assignee" class="hidden">
                                    <div class="w-5 h-5 rounded-full bg-gradient-to-br from-[#0069FF] to-[#003B8E] flex items-center justify-center shrink-0">
                                        <span class="text-[7px] text-white font-bold" x-text="name.charAt(0)"></span>
                                    </div>
                                    <span class="font-semibold" :class="form.assignee === name ? 'text-[#003B8E]' : 'text-[#64748B]'" x-text="name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Tags --}}
                    <div>
                        <label class="block text-[9px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">برچسب‌ها</label>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="tag in allTags" :key="tag.name">
                                <button type="button" @click="toggleTag(tag.name)" class="text-[9px] font-bold px-2 py-1 rounded-md border transition-all duration-150" :class="form.tags.includes(tag.name) ? tag.activeClass : tag.inactiveClass" x-text="tag.name"></button>
                            </template>
                        </div>
                    </div>

                    {{-- Separator --}}
                    <div class="border-t border-[#E2E8F0]"></div>

                    {{-- Actions --}}
                    <div class="space-y-2">
                        <button type="button" @click="saveTask()" class="w-full text-[11px] font-bold text-white bg-gradient-to-l from-[#003B8E] to-[#0069FF] hover:from-[#004BAA] hover:to-[#4D99FF] px-4 py-2.5 rounded-xl shadow-md shadow-[#0069FF]/25 hover:shadow-lg transition-all active:scale-[0.97]">
                            <span x-text="editingTask ? 'ذخیره تغییرات' : 'ایجاد وظیفه'"></span>
                        </button>
                        <button x-show="editingTask" type="button" @click="confirmDelete(form.columnId, editingTask); showModal = false" class="w-full flex items-center justify-center gap-1.5 text-[11px] font-semibold text-[#94A3B8] hover:text-red-500 px-4 py-2 rounded-xl border border-[#E2E8F0] hover:border-red-200 hover:bg-red-50 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            حذف وظیفه
                        </button>
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

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>

    <script>
        function board() {
            return {
                showModal: false,
                showDeleteModal: false,
                editingTask: null,
                editingDescription: false,
                deleteTarget: { columnId: null, taskId: null },
                toast: { show: false, message: '' },
                newCheckItem: '',
                newComment: '',
                form: { id: '', title: '', description: '', priority: 'متوسط', assignee: '', columnId: 'backlog', dueDate: '', tags: [], checklist: [], comments: [] },

                assignees: ['علی محمدی', 'سارا احمدی', 'رضا کریمی', 'نیلوفر شریفی', 'امیر حسینی', 'مریم رضایی'],

                allTags: [
                    { name: 'طراحی', activeClass: 'border-purple-400 bg-purple-50 text-purple-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-purple-200 hover:text-purple-500' },
                    { name: 'توسعه', activeClass: 'border-blue-400 bg-blue-50 text-blue-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-blue-200 hover:text-blue-500' },
                    { name: 'بک‌اند', activeClass: 'border-amber-400 bg-amber-50 text-amber-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-amber-200 hover:text-amber-500' },
                    { name: 'فرانت‌اند', activeClass: 'border-green-400 bg-green-50 text-green-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-green-200 hover:text-green-500' },
                    { name: 'باگ', activeClass: 'border-red-400 bg-red-50 text-red-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-red-200 hover:text-red-500' },
                    { name: 'بهبود', activeClass: 'border-teal-400 bg-teal-50 text-teal-700', inactiveClass: 'border-[#F1F5F9] text-[#94A3B8] hover:border-teal-200 hover:text-teal-500' },
                ],

                columns: [
                    {
                        id: 'backlog', title: 'پس‌زمینه', dotColor: 'bg-[#94A3B8]', badgeClass: 'bg-[#F1F5F9] text-[#64748B]',
                        tasks: [
                            { id: 'SCR-001', title: 'طراحی صفحه ورود کاربران', description: 'طراحی مجدد فرم ورود با رابط کاربری ساده‌تر و تجربه کاربری بهتر', priority: 'متوسط', assignee: 'علی محمدی', dueDate: '2026-07-01', tags: ['طراحی', 'فرانت‌اند'], checklist: [{text:' wireframe', done:true},{text:'پروتوتایپ', done:false}], comments: [{author:'علی محمدی', text:'نیاز به بررسی مجدد دارد', time:'۲ ساعت پیش'}] },
                            { id: 'SCR-002', title: 'بررسی عملکرد سرور', description: '', priority: 'پایین', assignee: 'رضا کریمی', dueDate: '', tags: ['بک‌اند'], checklist: [], comments: [] },
                            { id: 'SCR-003', title: 'به‌روزرسانی مستندات API', description: 'افزودن مستندات اندپوینت‌های جدید به Swagger', priority: 'پایین', assignee: 'نیلوفر شریفی', dueDate: '2026-07-10', tags: ['بک‌اند'], checklist: [{text:'اندپوینت‌های کاربر', done:true},{text:'اندپوینت‌های پرداخت', done:false}], comments: [] },
                        ]
                    },
                    {
                        id: 'progress', title: 'در حال انجام', dotColor: 'bg-[#0069FF]', badgeClass: 'bg-[#E8F0FE] text-[#0069FF]',
                        tasks: [
                            { id: 'SCR-004', title: 'پیاده‌سازی سیستم اعلان‌ها', description: 'ارسال اعلان از طریق ایمیل و پوش نوتیفیکیشن', priority: 'بالا', assignee: 'سارا احمدی', dueDate: '2026-06-25', tags: ['توسعه', 'بک‌اند'], checklist: [{text:'ایمیل', done:true},{text:'پوش', done:false},{text:'تست', done:false}], comments: [{author:'سارا احمدی', text:'ایمیل تمام شد، روی پوش کار می‌کنم', time:'۱ روز پیش'}] },
                            { id: 'SCR-005', title: 'اتصال به درگاه پرداخت', description: 'پیاده‌سازی درگاه زرین‌پال', priority: 'متوسط', assignee: 'امیر حسینی', dueDate: '2026-07-05', tags: ['توسعه'], checklist: [], comments: [] },
                        ]
                    },
                    {
                        id: 'review', title: 'بررسی', dotColor: 'bg-[#F59E0B]', badgeClass: 'bg-[#FEF3C7] text-[#D97706]',
                        tasks: [
                            { id: 'SCR-006', title: 'اصلاح باگ فرم ثبت‌نام', description: 'مشکل اعتبارسنجی ایمیل', priority: 'بالا', assignee: 'مریم رضایی', dueDate: '2026-06-22', tags: ['باگ', 'فرانت‌اند'], checklist: [{text:'بررسی کد', done:true},{text:'تست', done:true}], comments: [{author:'مریم رضایی', text:'تست شد، آماده بررسی نهایی', time:'۳ ساعت پیش'}] },
                        ]
                    },
                    {
                        id: 'done', title: 'انجام شده', dotColor: 'bg-[#22C55E]', badgeClass: 'bg-[#DCFCE7] text-[#16A34A]',
                        tasks: [
                            { id: 'SCR-007', title: 'راه‌اندازی محیط توسعه', description: 'docker-compose', priority: 'متوسط', assignee: 'رضا کریمی', dueDate: '2026-06-10', tags: ['توسعه'], checklist: [], comments: [] },
                            { id: 'SCR-008', title: 'طراحی دیتابیس', description: 'طراحی اسکیما و ERD', priority: 'بالا', assignee: 'علی محمدی', dueDate: '2026-06-08', tags: ['بک‌اند'], checklist: [{text:'ERD', done:true},{text:'Migration', done:true}], comments: [] },
                        ]
                    }
                ],

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
                    this.form.comments.push({ author: 'شما', text: this.newComment.trim(), time: 'همین الان' });
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
                    const fromCol = this.columns.find(c => c.id === fromColId);
                    const toCol = this.columns.find(c => c.id === toColId);
                    if (!fromCol || !toCol) return;
                    const idx = fromCol.tasks.findIndex(t => t.id === taskId);
                    if (idx === -1) return;
                    const [task] = fromCol.tasks.splice(idx, 1);
                    toCol.tasks.splice(newIndex, 0, task);
                },

                openAddModal(columnId) {
                    this.editingTask = null;
                    this.editingDescription = false;
                    this.form = { id: '', title: '', description: '', priority: 'متوسط', assignee: '', columnId: columnId, dueDate: '', tags: [], checklist: [], comments: [] };
                    this.newCheckItem = '';
                    this.newComment = '';
                    this.showModal = true;
                },

                openEditModal(task, columnId) {
                    this.editingTask = task.id;
                    this.editingDescription = false;
                    this.form = {
                        id: task.id, title: task.title, description: task.description || '', priority: task.priority,
                        assignee: task.assignee, columnId: columnId, dueDate: task.dueDate || '',
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

                saveTask() {
                    if (!this.form.title.trim()) return;
                    if (this.editingTask) {
                        const col = this.columns.find(c => c.id === this.form.columnId);
                        const task = col?.tasks.find(t => t.id === this.editingTask);
                        if (task) {
                            task.title = this.form.title;
                            task.description = this.form.description;
                            task.priority = this.form.priority;
                            task.assignee = this.form.assignee;
                            task.dueDate = this.form.dueDate;
                            task.tags = [...this.form.tags];
                            task.checklist = JSON.parse(JSON.stringify(this.form.checklist));
                            task.comments = JSON.parse(JSON.stringify(this.form.comments));
                        }
                        this.showToast('تغییرات ذخیره شد');
                    } else {
                        const col = this.columns.find(c => c.id === this.form.columnId);
                        if (col) {
                            const maxNum = this.columns.reduce((max, c) => Math.max(max, ...c.tasks.map(t => parseInt(t.id.replace('SCR-', '')) || 0)), 0);
                            col.tasks.push({
                                id: 'SCR-' + String(maxNum + 1).padStart(3, '0'),
                                title: this.form.title, description: this.form.description, priority: this.form.priority,
                                assignee: this.form.assignee, dueDate: this.form.dueDate,
                                tags: [...this.form.tags], checklist: JSON.parse(JSON.stringify(this.form.checklist)),
                                comments: JSON.parse(JSON.stringify(this.form.comments))
                            });
                            this.showToast('وظیفه جدید ایجاد شد');
                        }
                    }
                    this.showModal = false;
                },

                confirmDelete(columnId, taskId) {
                    this.deleteTarget = { columnId, taskId };
                    this.showDeleteModal = true;
                },

                deleteTask() {
                    const col = this.columns.find(c => c.id === this.deleteTarget.columnId);
                    if (col) {
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
