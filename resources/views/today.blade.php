@extends('layouts.app')

@section('body')
<x-workspace-shell :workspace="$workspace" :projects="$projects" active="today">
    <div class="today-page" x-data="todayPage()" @keydown.window="handleShortcut($event)">
        <header class="today-header">
            <div>
                <p class="today-eyebrow">برنامه روزانه</p>
                <h1>صبح بخیر، {{ auth()->user()->first_name ?: auth()->user()->name }}</h1>
                <p x-text="formattedDate"></p>
            </div>
            <button type="button" class="today-create" @click="quickOpen = true; $nextTick(() => $refs.quickTitle.focus())">وظیفه جدید <kbd>C</kbd></button>
        </header>

        <section class="today-section">
            <div class="today-section__heading"><h2>امروز من</h2><span x-text="activeCount + ' وظیفه فعال'"></span></div>
            <div class="today-group">
                <h3>حتماً انجام شود</h3>
                <template x-for="task in mustTasks" :key="task.dbId"><div x-html="taskRow(task)"></div></template>
                <p class="today-empty" x-show="mustTasks.length === 0">هنوز کاری برای امروز انتخاب نکرده‌اید.</p>
            </div>
            <div class="today-group" x-show="optionalTasks.length">
                <h3>اگر وقت شد</h3>
                <template x-for="task in optionalTasks" :key="task.dbId"><div x-html="taskRow(task)"></div></template>
            </div>
        </section>

        <section class="today-section" x-show="blockedTasks.length">
            <div class="today-section__heading"><h2>مسدود</h2></div>
            <template x-for="task in blockedTasks" :key="task.dbId">
                <div class="today-row is-blocked"><span class="today-row__warning">!</span><div><strong x-text="task.title"></strong><small x-text="task.blockedReason || 'منتظر رفع مانع'"></small></div><span class="today-row__project" x-text="task.project.name"></span></div>
            </template>
        </section>

        <section class="today-section" x-show="overdueTasks.length">
            <div class="today-section__heading"><h2>عقب‌افتاده</h2><span x-text="overdueTasks.length"></span></div>
            <template x-for="task in overdueTasks" :key="task.dbId">
                <div class="today-row"><span class="today-row__due">!</span><div><strong x-text="task.title"></strong><small x-text="task.project.name + ' · سررسید ' + task.dueDate"></small></div><button @click="addExisting(task, false)">افزودن به امروز</button></div>
            </template>
        </section>

        <section class="today-section" x-show="doneTasks.length">
            <div class="today-section__heading"><h2>انجام‌شده امروز</h2><span x-text="doneTasks.length"></span></div>
            <template x-for="task in doneTasks" :key="task.dbId"><div class="today-row is-done"><span>✓</span><strong x-text="task.title"></strong><span class="today-row__project" x-text="task.project.name"></span></div></template>
        </section>

        <button class="today-add-existing" @click="existingOpen = true">+ افزودن وظیفه موجود به امروز</button>

        <div class="today-dialog" x-show="quickOpen" x-cloak @keydown.escape.window="quickOpen = false">
            <div class="today-dialog__backdrop" @click="quickOpen = false"></div>
            <form class="today-dialog__panel" @submit.prevent="createTask()">
                <h2>چه کاری باید انجام شود؟</h2>
                <input x-ref="quickTitle" x-model="quick.title" required maxlength="500" placeholder="عنوان وظیفه">
                <label>پروژه<select x-model="quick.projectId" required><template x-for="project in projects" :key="project.id"><option :value="project.id" x-text="project.name"></option></template></select></label>
                <label>زمان<select x-model="quick.when"><option value="today">امروز</option><option value="tomorrow">فردا</option><option value="unscheduled">بدون برنامه</option></select></label>
                <p class="today-error" x-show="error" x-text="error"></p>
                <div><button type="button" @click="quickOpen = false">انصراف</button><button type="submit" :disabled="saving" x-text="saving ? 'در حال ساخت…' : 'ساخت وظیفه'"></button></div>
            </form>
        </div>

        <div class="today-dialog" x-show="existingOpen" x-cloak @keydown.escape.window="existingOpen = false">
            <div class="today-dialog__backdrop" @click="existingOpen = false"></div>
            <div class="today-dialog__panel">
                <h2>افزودن وظیفه موجود</h2>
                <input x-model="existingSearch" placeholder="جستجوی وظیفه یا پروژه">
                <div class="today-existing-list">
                    <template x-for="task in filteredAvailable" :key="task.dbId">
                        <button type="button" @click="addExisting(task, true)"><span><strong x-text="task.title"></strong><small x-text="task.project.name"></small></span><em>افزودن</em></button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function todayPage() {
            return {
                mustTasks: @js($mustTasks), optionalTasks: @js($optionalTasks), blockedTasks: @js($blockedTasks), doneTasks: @js($doneTasks), overdueTasks: @js($overdueTasks),
                availableTasks: @js($availableTasks), projects: @js($projects->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->values()),
                today: '{{ $todayDate->toDateString() }}', quickOpen: false, existingOpen: false, existingSearch: '', saving: false, error: '',
                quick: { title: '', projectId: @js($projects->first()?->id), when: 'today' },
                get formattedDate() { return new Intl.DateTimeFormat('fa-IR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(this.today + 'T12:00:00')); },
                get activeCount() { return this.mustTasks.length + this.optionalTasks.length; },
                get filteredAvailable() { const q = this.existingSearch.trim().toLowerCase(); return this.availableTasks.filter(t => !q || t.title.toLowerCase().includes(q) || t.project.name.toLowerCase().includes(q)); },
                taskRow(task) { return `<div class="today-row" data-task="${task.dbId}"><button class="today-check" onclick="window.dispatchEvent(new CustomEvent('today-complete',{detail:${task.dbId}}))" aria-label="انجام شد"></button><div><strong>${this.escape(task.title)}</strong><small>${this.escape(task.project.name)}${task.dueDate ? ' · سررسید ' + task.dueDate : ''}</small></div><button onclick="window.dispatchEvent(new CustomEvent('today-tomorrow',{detail:${task.dbId}}))">فردا</button><button onclick="window.dispatchEvent(new CustomEvent('today-remove',{detail:${task.dbId}}))">برداشتن</button></div>`; },
                escape(value) { const el = document.createElement('div'); el.textContent = value || ''; return el.innerHTML; },
                init() { window.addEventListener('today-complete', e => this.state(e.detail, 'complete')); window.addEventListener('today-remove', e => this.remove(e.detail)); window.addEventListener('today-tomorrow', e => this.moveTomorrow(e.detail)); },
                find(id) { return this.mustTasks.concat(this.optionalTasks, this.blockedTasks).find(t => t.dbId === Number(id)); },
                endpoint(task, kind) { const base = @js(route('today.task.state', [$workspace->slug, '__PROJECT__', '__TASK__'], false)); return base.replace('__PROJECT__', task.project.slug).replace('__TASK__', task.dbId).replace('/state', kind); },
                async request(url, method, body) { const r = await fetch(url, { method, headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@js(csrf_token())}, body: body ? JSON.stringify(body) : null }); const data = await r.json().catch(() => ({})); if (!r.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'عملیات انجام نشد.'); return data; },
                async state(id, action) { const task = this.find(id); if (!task) return; try { const data = await this.request(this.endpoint(task, '/state'), 'PATCH', {action}); this.removeLocal(id); this.doneTasks.unshift(data.task); } catch(e) { alert(e.message); } },
                async remove(id) { const task = this.find(id); if (!task) return; try { await this.request(this.endpoint(task, '/plan'), 'DELETE', {planned_for:this.today}); this.removeLocal(id); } catch(e) { alert(e.message); } },
                async moveTomorrow(id) { const task = this.find(id); if (!task) return; const tomorrow = new Date(this.today+'T12:00:00'); tomorrow.setDate(tomorrow.getDate()+1); try { await this.request(this.endpoint(task, '/plan'), 'PUT', {planned_for:tomorrow.toISOString().slice(0,10), bucket:task.plan.bucket}); await this.remove(id); } catch(e) { alert(e.message); } },
                removeLocal(id) { for (const key of ['mustTasks','optionalTasks','blockedTasks']) this[key] = this[key].filter(t => t.dbId !== Number(id)); },
                async addExisting(task, close) { try { const assigned = task.assignees.some(a => a.id === @js(auth()->id())); const data = await this.request(this.endpoint(task, '/plan'), 'PUT', {planned_for:this.today,bucket:'must',assign_to_me:!assigned}); this.mustTasks.push(data.task); this.availableTasks = this.availableTasks.filter(t => t.dbId !== task.dbId); if(close) this.existingOpen=false; } catch(e) { alert(e.message); } },
                async createTask() { this.saving=true; this.error=''; try { const payload=Object.assign({},this.quick,{bucket:'must',project_id:this.quick.projectId}); const data=await this.request(@js(route('today.tasks.store', $workspace->slug, false)), 'POST', payload); if(this.quick.when==='today') this.mustTasks.push(data.task); this.quick={title:'',projectId:this.quick.projectId,when:'today'}; this.quickOpen=false; } catch(e) { this.error=e.message; } finally { this.saving=false; } },
                handleShortcut(e) { if (e.key.toLowerCase() !== 'c' || e.metaKey || e.ctrlKey || e.altKey || ['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName) || e.target.isContentEditable) return; e.preventDefault(); this.quickOpen=true; this.$nextTick(() => this.$refs.quickTitle.focus()); }
            }
        }
    </script>
    @endpush
</x-workspace-shell>
@endsection
