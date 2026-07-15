# Task Modal Redesign + Jalali Dates + Tag Management

> **For agentic workers:** REQUIRED SUB-SKILL: Use compose:subagent (recommended) or compose:execute to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the task modal to be single-column with description as the main event, add Jalali date picker, and enable custom tag management per project.

**Architecture:** Single-column modal layout with description-first hierarchy. Jalali dates via flatpickr + jalali plugin (store Gregorian in DB, display Jalali). Custom tags stored as JSON on projects table, merged with hardcoded defaults on frontend.

**Tech Stack:** Laravel 13, Alpine.js, TailwindCSS v4, flatpickr, flatpickr-jalali

---

## File Structure

| File | Action | Purpose |
|------|--------|---------|
| `package.json` | Modify | Add flatpickr + jalali plugin |
| `resources/js/app.js` | Modify | Import flatpickr + jalali styles |
| `resources/css/board.css` | Modify | New modal + tag styles |
| `resources/views/board.blade.php` | Modify | Modal HTML + JS logic |
| `database/migrations/xxxx_add_project_tags_to_projects_table.php` | Create | Add project_tags JSON column |
| `app/Models/Project.php` | Modify | Add project_tags to fillable/casts |
| `app/Http/Controllers/BoardController.php` | Modify | Load/save custom tags |
| `resources/views/emails/` | No change | — |

---

## Task 1: Install flatpickr + jalali

**Files:**
- Modify: `package.json`
- Modify: `resources/js/app.js`

- [ ] **Step 1: Install packages**

Run:
```bash
cd /var/www/html/scrum && npm install flatpickr jalali-moment
```

- [ ] **Step 2: Import in app.js**

Add to `resources/js/app.js`:
```js
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import moment from 'jalali-moment';

window.flatpickr = flatpickr;
window.moment = moment;
```

- [ ] **Step 3: Verify build**

Run: `npm run build`
Expected: Build succeeds with no errors.

- [ ] **Step 4: Commit**

```bash
git add package.json package-lock.json resources/js/app.js
git commit -m "feat: install flatpickr + jalali-moment for Jalali date picker"
```

---

## Task 2: Migration for project_tags column

**Files:**
- Create: `database/migrations/2026_07_15_000001_add_project_tags_to_projects_table.php`
- Modify: `app/Models/Project.php`

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('custom_tags')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('custom_tags');
        });
    }
};
```

- [ ] **Step 2: Add to Project model**

In `app/Models/Project.php`, add `'custom_tags'` to `$fillable` and `'custom_tags' => 'array'` to `$casts`.

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: Migration runs successfully.

- [ ] **Step 4: Commit**

```bash
git add database/migrations/ app/Models/Project.php
git commit -m "feat: add custom_tags JSON column to projects table"
```

---

## Task 3: Backend — Load/save custom tags

**Files:**
- Modify: `app/Http/Controllers/BoardController.php`

- [ ] **Step 1: Pass custom_tags to board view**

In `BoardController::show()`, add `'customTags' => $project->custom_tags ?? []` to the data passed to the view.

- [ ] **Step 2: Save custom_tags on project update**

In `BoardController::updateProject()` (or equivalent), accept `custom_tags` from request and save to project:
```php
if ($request->has('custom_tags')) {
    $project->custom_tags = $request->input('custom_tags');
    $project->save();
}
```

- [ ] **Step 3: Verify build**

Run: `npm run build`
Expected: Build succeeds.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/BoardController.php
git commit -m "feat: load/save custom project tags from backend"
```

---

## Task 4: Modal redesign — New HTML structure

**Files:**
- Modify: `resources/views/board.blade.php` (modal section, lines ~785-1132)
- Modify: `resources/css/board.css`

- [ ] **Step 1: Replace modal HTML**

Replace the entire modal section (from `{{-- Backdrop --}}` to the closing `</div>` of scroll container) with the new single-column layout:

```html
{{-- Backdrop --}}
<div
    x-show="showModal"
    x-cloak
    x-transition:enter="transition-opacity ease-out duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-75"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 bg-[#0A1628]/60"
    @click="requestCloseModal()"
    x-effect="if (showModal) { document.body.classList.add('modal-open') } else { document.body.classList.remove('modal-open') }"
></div>

{{-- Scroll container --}}
<div
    x-show="showModal"
    x-cloak
    x-transition:enter="transition-opacity ease-out duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-75"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    @keydown.escape.window="requestCloseModal()"
>
    <div class="min-h-screen flex items-start justify-center p-4 pt-8 pb-8 md:pt-12 md:pb-12">
        <div
            class="task-modal-shell relative my-auto"
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
            {{-- Header --}}
            <div class="task-modal-header shrink-0">
                <div class="flex items-center gap-3">
                    <h3 id="task-modal-title" class="text-white font-bold text-[15px]" x-text="editingTask ? 'ویرایش کارت' : 'کارت جدید'"></h3>
                    <span x-show="editingTask" class="text-white/80 text-[11px] font-bold bg-white/15 px-2 py-0.5 rounded-md" x-text="form.id"></span>
                </div>
                <button @click="requestCloseModal()" class="text-white/70 hover:text-white transition-colors w-9 h-9 flex items-center justify-center rounded-lg hover:bg-white/10" aria-label="بستن پنجره">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body: Single column --}}
            <div class="p-4 md:p-6 space-y-5" style="direction: rtl;">
                {{-- Title --}}
                <div>
                    <label class="board-field-label">عنوان</label>
                    <input
                        x-ref="taskTitle"
                        x-model="form.title"
                        type="text"
                        :disabled="!canEdit"
                        class="w-full text-base font-bold text-[#1A1D21] border-b-2 border-[#E2E8F0] pb-2 focus:outline-none focus:border-[#18212B] transition-colors bg-transparent placeholder:text-[#CBD5E1]"
                        placeholder="عنوان وظیفه..."
                    >
                </div>

                {{-- Description — THE MAIN EVENT --}}
                <div>
                    <label class="board-field-label">توضیحات</label>
                    <div x-show="!editingDescription" @click="if (canEdit) { descriptionBeforeEdit = form.description; editingDescription = true }" class="min-h-[80px] p-3 rounded-xl border border-[#E2E8F0] cursor-pointer hover:border-[#CBD5E1] transition-colors" :class="form.description ? 'text-sm text-[#475569] leading-relaxed whitespace-pre-wrap' : 'text-sm text-[#CBD5E1]'" x-html="form.description ? formatMentionText(form.description) : 'توضیحی ثبت نشده — کلیک کنید...'"></div>
                    <div x-show="editingDescription" x-transition class="relative">
                        <textarea
                            x-model="form.description"
                            x-init="$nextTick(() => $el.focus())"
                            rows="6"
                            class="w-full text-sm text-[#1A1D21] border-2 border-[#18212B] rounded-xl px-3 py-2 focus:outline-none transition-colors resize-none leading-relaxed"
                            placeholder="توضیحات وظیفه را بنویسید..."
                            @input="handleMentionInput('description', $event)"
                            @keydown.down.prevent="moveMentionSelection(1)"
                            @keydown.up.prevent="moveMentionSelection(-1)"
                            @keydown.enter="if (mentionOpen) { $event.preventDefault(); selectActiveMention() }"
                            @keydown.escape="mentionOpen ? closeMentionMenu() : editingDescription = false"
                        ></textarea>
                        <div x-show="mentionOpen && mentionField === 'description'" class="absolute top-full right-0 left-0 mt-1 bg-white border border-[#D8E0EB] rounded-xl shadow-xl z-30 overflow-hidden">
                            <template x-for="(person, index) in mentionResults" :key="person.id">
                                <button @click="selectMention(person)" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-right" :class="mentionIndex === index ? 'bg-[#F1F3F2]' : 'hover:bg-[#F8FAFC]'">
                                    <span class="w-7 h-7 rounded-full bg-[#071B33] text-white flex items-center justify-center text-[9px] font-bold" x-text="person.name.charAt(0)"></span>
                                    <span class="text-[11px] font-bold text-[#172B4D]" x-text="person.name"></span>
                                </button>
                            </template>
                        </div>
                        <p class="text-[10px] text-[#94A3B8] mt-1.5">@ برای اشاره به هم‌تیمی‌ها</p>
                        <div class="flex gap-2 mt-2">
                            <button @click="editingDescription = false" class="text-[11px] font-bold text-white bg-[#18212B] hover:bg-[#000000] px-3 py-1.5 rounded-lg transition-all">ذخیره</button>
                            <button @click="form.description = descriptionBeforeEdit; editingDescription = false" class="text-[11px] font-bold text-[#64748B] hover:text-[#1A1D21] px-3 py-1.5 rounded-lg transition-all">لغو</button>
                        </div>
                    </div>
                </div>

                {{-- Grid: Priority, Due Date, Column --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Priority --}}
                    <div>
                        <label class="board-field-label">اولویت</label>
                        <div class="flex flex-col gap-1">
                            <template x-for="p in [{name:'بالا', color:'bg-red-500'}, {name:'متوسط', color:'bg-violet-500'}, {name:'پایین', color:'bg-slate-400'}]" :key="p.name">
                                <label class="flex items-center gap-2 text-[11px] cursor-pointer px-2.5 py-1.5 rounded-lg border transition-all duration-150" :class="form.priority === p.name ? 'border-[#18212B] bg-[#F1F3F2]' : 'border-transparent hover:bg-white'">
                                    <input type="radio" :value="p.name" x-model="form.priority" :disabled="!canEdit" class="hidden">
                                    <span class="w-2 h-2 rounded-full" :class="p.color"></span>
                                    <span class="font-semibold" :class="form.priority === p.name ? 'text-[#000000]' : 'text-[#64748B]'" x-text="p.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>

                    {{-- Due Date (Jalali) --}}
                    <div>
                        <label class="board-field-label">سررسید</label>
                        <div class="relative">
                            <input
                                x-ref="dueDateInput"
                                x-model="form.dueDate"
                                type="text"
                                :disabled="!canEdit"
                                class="jalali-date-input w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#18212B] transition-colors bg-white disabled:bg-[#F1F5F9]"
                                placeholder="انتخاب تاریخ..."
                                readonly
                            >
                            <button x-show="form.dueDate && canEdit" @click="form.dueDate = ''" type="button" class="absolute left-2 top-1/2 -translate-y-1/2 text-[#94A3B8] hover:text-red-500 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <p x-show="form.dueDate && isOverdue(form.dueDate)" class="text-[10px] text-red-500 font-bold mt-1">سررسید گذشته</p>
                    </div>

                    {{-- Column --}}
                    <div>
                        <label class="board-field-label">ستون</label>
                        <select x-model="form.columnId" :disabled="!canEdit" class="w-full text-xs font-semibold border-2 border-[#E2E8F0] rounded-lg px-2.5 py-2 focus:outline-none focus:border-[#18212B] transition-colors bg-white disabled:bg-[#F1F5F9]">
                            <template x-for="col in columns" :key="col.id">
                                <option :value="col.id" x-text="col.title"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Grid: Assignees, Tags --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Assignees --}}
                    <div x-data="{ assigneeOpen: false, assigneeSearch: '' }" @click.away="assigneeOpen = false" class="relative">
                        <label class="board-field-label">مسئولین</label>
                        <div
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
                            <span x-show="form.assignees.length === 0" class="text-xs text-[#CBD5E1]">انتخاب کنید...</span>
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
                            class="absolute top-full left-0 right-0 mt-1 bg-white border-2 border-[#E2E8F0] rounded-xl shadow-lg shadow-black/10 z-10 overflow-hidden"
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
                                        x-init="$nextTick(() => $el.focus())"
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
                                        <div class="w-5 h-5 rounded-full bg-gradient-to-br from-[#18212B] to-[#000000] flex items-center justify-center shrink-0">
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

                    {{-- Tags --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="board-field-label mb-0">برچسب‌ها</label>
                            <button x-show="canEdit" @click="showTagManager = !showTagManager" type="button" class="text-[10px] font-bold text-[#64748B] hover:text-[#18212B] transition-colors" x-text="showTagManager ? 'بستن' : 'مدیریت'"></button>
                        </div>
                        {{-- Tag manager (expandable) --}}
                        <div x-show="showTagManager" x-transition class="mb-3 p-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC]">
                            <div class="flex items-center gap-2 mb-2">
                                <input
                                    x-model="newTagName"
                                    @keydown.enter="addCustomTag()"
                                    type="text"
                                    class="flex-1 text-[11px] border border-[#E2E8F0] rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-[#18212B] transition-colors placeholder:text-[#CBD5E1]"
                                    placeholder="نام برچسب جدید..."
                                    maxlength="20"
                                >
                                <div class="flex gap-1">
                                    <template x-for="color in tagColors" :key="color">
                                        <button type="button" @click="newTagColor = color" class="w-5 h-5 rounded-full border-2 transition-all" :class="newTagColor === color ? 'border-[#18212B] scale-110' : 'border-transparent'" :style="'background-color:' + color"></button>
                                    </template>
                                </div>
                                <button @click="addCustomTag()" :disabled="!newTagName.trim()" class="text-[10px] font-bold text-white bg-[#18212B] hover:bg-[#000000] disabled:opacity-40 px-2 py-1 rounded-lg transition-colors">افزودن</button>
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <template x-for="tag in editableTags()" :key="tag.name">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-md border transition-all" :class="tag.activeClass">
                                        <span x-text="tag.name"></span>
                                        <template x-if="tag.isCustom">
                                            <button @click="removeCustomTag(tag.name)" class="hover:text-red-500 ml-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </template>
                                    </span>
                                </template>
                            </div>
                        </div>
                        {{-- Tag toggles --}}
                        <div class="flex flex-wrap gap-1">
                            <template x-for="tag in editableTags()" :key="tag.name">
                                <button type="button" @click="if (canEdit) toggleTag(tag.name)" :disabled="!canEdit" class="text-[9px] font-bold px-2 py-1 rounded-md border transition-all duration-150" :class="form.tags.includes(tag.name) ? tag.activeClass : tag.inactiveClass" x-text="tag.name"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Separator --}}
                <div class="border-t border-[#E2E8F0]"></div>

                {{-- Checklist --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="board-field-label mb-0">چک‌لیست</label>
                        <span class="text-[12px] font-bold text-[#64748B]" x-text="checklistProgress()"></span>
                    </div>
                    <div class="checklist-bar mb-3">
                        <div class="checklist-bar-fill" :style="'width:' + checklistPercent() + '%'"></div>
                    </div>
                    <div class="space-y-1.5">
                        <template x-for="(item, idx) in form.checklist" :key="idx">
                            <div class="check-item flex items-center gap-2.5 py-1.5 px-2 rounded-lg hover:bg-[#F8FAFC] group/item transition-colors">
                                <label class="flex items-center gap-2.5 cursor-pointer flex-1">
                                    <input type="checkbox" x-model="item.done" :disabled="!canEdit" class="w-4 h-4 rounded border-2 border-[#CBD5E1] text-[#18212B] focus:ring-[#18212B]/20 cursor-pointer accent-[#18212B] disabled:cursor-default">
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
                            class="w-full text-sm border border-[#E2E8F0] rounded-lg px-3 py-2 focus:outline-none focus:border-[#18212B] transition-colors placeholder:text-[#CBD5E1]"
                            placeholder="افزودن آیتم..."
                        >
                    </div>
                    @endif
                </div>

                {{-- Separator --}}
                <div class="border-t border-[#E2E8F0]"></div>

                {{-- Comments --}}
                <div>
                    <label class="board-field-label mb-3">گفتگو</label>
                    <div class="space-y-3">
                        <template x-for="(comment, idx) in form.comments" :key="idx">
                            <div class="flex gap-3">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#18212B] to-[#000000] flex items-center justify-center shrink-0 shadow-sm">
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
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#18212B] to-[#000000] flex items-center justify-center shrink-0 shadow-sm">
                            <span class="text-[9px] text-white font-bold">ش</span>
                        </div>
                        <div class="flex-1 relative">
                            <textarea
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
                            <div x-show="mentionOpen && mentionField === 'comment'" class="absolute bottom-full right-0 left-0 mb-1 bg-white border border-[#D8E0EB] rounded-xl shadow-xl z-30 overflow-hidden">
                                <template x-for="(person, index) in mentionResults" :key="person.id">
                                    <button @click="selectMention(person)" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-right" :class="mentionIndex === index ? 'bg-[#F1F3F2]' : 'hover:bg-[#F8FAFC]'">
                                        <span class="w-7 h-7 rounded-full bg-[#071B33] text-white flex items-center justify-center text-[9px] font-bold" x-text="person.name.charAt(0)"></span>
                                        <span class="text-[11px] font-bold text-[#172B4D]" x-text="person.name"></span>
                                    </button>
                                </template>
                            </div>
                            <p class="text-[10px] text-[#94A3B8] mt-1">@ برای اشاره به هم‌تیمی‌ها</p>
                            <div class="flex justify-end mt-1.5" x-show="newComment.trim()">
                                <button @click="addComment()" class="text-[10px] font-bold text-white bg-[#18212B] hover:bg-[#000000] px-3 py-1 rounded-lg transition-all">ارسال (Ctrl+Enter)</button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            @if ($canEdit)
            <div class="border-t border-[#E2E8F0] p-4 bg-[#F8FAFC] flex items-center justify-between">
                <button x-show="editingTask" type="button" @click="requestDeleteFromTaskModal()" :disabled="taskSaving" class="flex items-center gap-1.5 text-[11px] font-semibold text-[#94A3B8] hover:text-red-500 disabled:opacity-50 px-3 py-2 rounded-xl border border-[#E2E8F0] hover:border-red-200 hover:bg-red-50 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span x-text="'حذف کارت'"></span>
                </button>
                <div x-show="!editingTask"></div>
                <button type="button" @click="saveTask()" :disabled="taskSaving" :aria-busy="taskSaving" class="text-[11px] font-bold text-white bg-gradient-to-l from-[#000000] to-[#18212B] hover:from-[#000000] hover:to-[#253342] disabled:opacity-60 disabled:cursor-wait px-5 py-2.5 rounded-xl shadow-md shadow-black/20 hover:shadow-lg transition-all active:scale-[0.97]">
                    <span x-text="taskSaving ? 'در حال ذخیره…' : (editingTask ? 'ذخیره تغییرات' : 'ایجاد کارت')"></span>
                </button>
            </div>
            @else
            <div class="border-t border-[#E2E8F0] p-4 bg-[#F8FAFC]">
                <p class="text-[11px] leading-5 text-[#64748B] bg-white border border-[#E2E8F0] rounded-lg px-3 py-2.5">شما دسترسی مشاهده دارید و نمی‌توانید این وظیفه را تغییر دهید.</p>
            </div>
            @endif
        </div>
    </div>
</div>
```

- [ ] **Step 2: Add CSS for jalali-date-input**

In `resources/css/board.css`, add:
```css
.jalali-date-input {
    direction: ltr;
    text-align: right;
    cursor: pointer;
}
.jalali-date-input:focus {
    outline: none;
    border-color: #18212B;
}
```

- [ ] **Step 3: Verify build**

Run: `npm run build`
Expected: Build succeeds.

- [ ] **Step 4: Commit**

```bash
git add resources/views/board.blade.php resources/css/board.css
git commit -m "feat: redesign task modal to single-column, description-first layout"
```

---

## Task 5: JS — Jalali date picker initialization + tag management

**Files:**
- Modify: `resources/views/board.blade.php` (JS section, `board()` function)

- [ ] **Step 1: Add jalaliDatepicker initialization**

In the `init()` method of `board()`, add after existing code:
```js
this.$nextTick(() => {
    this.initJalaliDatePicker();
});
```

Add new method:
```js
initJalaliDatePicker() {
    if (!this.$refs.dueDateInput) return;
    const self = this;
    flatpickr(this.$refs.dueDateInput, {
        locale: 'fa',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'j F j',
        disableMobile: true,
        defaultDate: self.form.dueDate || null,
        onChange(selectedDates, dateStr) {
            self.form.dueDate = dateStr;
        },
    });
},
```

- [ ] **Step 2: Update formatDate to use Jalali**

Replace `formatDate()` method:
```js
formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        const jalali = moment(dateStr, 'YYYY-MM-DD').locale('fa').format('j D jMMMM');
        return this.toPersianDigits(jalali);
    } catch {
        return dateStr;
    }
},
```

- [ ] **Step 3: Add tag management state and methods**

Add to `board()` return object:
```js
showTagManager: false,
newTagName: '',
newTagColor: '#8B5CF6',
customTags: @json($customTags ?? []),
tagColors: ['#8B5CF6', '#EF4444', '#F59E0B', '#22C55E', '#14B8A6', '#64748B'],
```

Add methods:
```js
editableTags() {
    const hardcoded = this.allTags.map(t => ({ ...t, isCustom: false }));
    const custom = (this.customTags || []).map(t => ({
        name: t.name,
        activeClass: `border-[${t.color}] bg-[${t.color}]/10 text-[${t.color}]`,
        inactiveClass: `border-[#F1F5F9] text-[#94A3B8] hover:border-[${t.color}]/30`,
        isCustom: true,
    }));
    return [...hardcoded, ...custom];
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
        await fetch('{{ route("board.project.update", [$workspace->slug, $project->slug], false) }}', {
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
```

- [ ] **Step 4: Update openEditModal to refresh jalali picker**

In `openEditModal()`, after setting `form.dueDate`, add:
```js
this.$nextTick(() => {
    if (this.$refs.dueDateInput && this.$refs.dueDateInput._flatpickr) {
        this.$refs.dueDateInput._flatpickr.setDate(this.form.dueDate || null);
    }
});
```

- [ ] **Step 5: Update openAddModal to clear jalali picker**

In `openAddModal()`, after clearing form, add:
```js
this.$nextTick(() => {
    if (this.$refs.dueDateInput && this.$refs.dueDateInput._flatpickr) {
        this.$refs.dueDateInput._flatpickr.clear();
    }
});
```

- [ ] **Step 6: Verify build**

Run: `npm run build`
Expected: Build succeeds.

- [ ] **Step 7: Commit**

```bash
git add resources/views/board.blade.php
git commit -m "feat: add Jalali date picker and custom tag management to task modal"
```

---

## Task 6: Update project drawer to manage custom tags

**Files:**
- Modify: `resources/views/board.blade.php` (project drawer settings tab)

- [ ] **Step 1: Add tag management to project drawer settings**

In the project drawer settings tab (after description textarea), add:
```html
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
```

- [ ] **Step 2: Verify build**

Run: `npm run build`
Expected: Build succeeds.

- [ ] **Step 3: Commit**

```bash
git add resources/views/board.blade.php
git commit -m "feat: add custom tag management to project drawer settings"
```

---

## Task 7: Final verification

- [ ] **Step 1: Full build**

Run: `npm run build`
Expected: Build succeeds with no errors.

- [ ] **Step 2: Verify all changes**

Check:
1. Jalali date picker opens on due date field
2. Dates display in Jalali format on task cards
3. Custom tags can be created via modal tag manager
4. Custom tags can be created via project drawer
5. Custom tags persist after page reload
6. Modal is single-column with description as main event
7. All existing functionality (checklist, comments, mentions) still works

- [ ] **Step 3: Final commit**

```bash
git add -A
git commit -m "feat: complete task modal redesign with Jalali dates and tag management"
```
