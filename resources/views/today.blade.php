@extends('layouts.app')

@section('body')
<x-workspace-shell :workspace="$workspace" :projects="$projects" active="today">
    <x-slot:context>امروز</x-slot:context>
    <div class="today-page today-dashboard" x-data="todayPage()" @keydown.window="handleShortcut($event)">
        <header class="today-header">
            <div>
                <h1>امروز</h1>
                <p x-text="formattedDate"></p>
                <small><span x-text="activeCount"></span> کار باقی مانده · <span x-text="doneTasks.length"></span> کار انجام شد</small>
            </div>
            @if ($canEdit && $projects->isNotEmpty())
                <button type="button" class="today-create" @click="quickOpen = true; $nextTick(() => $refs.quickTitle.focus())">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="1.8" stroke-linecap="round"/></svg>
                    وظیفه جدید <kbd>C</kbd>
                </button>
            @elseif ($canEdit)
                <a href="{{ route('projects.index', $workspace->slug) }}" class="today-create">ساخت اولین پروژه</a>
            @endif
        </header>

        <div class="today-dashboard__grid">
            <div>
                <div class="today-section__heading"><h2>کارهای امروز</h2><span x-text="activeCount + ' وظیفه' || ''"></span></div>
                @if ($canEdit && $projects->isNotEmpty())
                    <form class="today-capture" @submit.prevent="createTodayTask()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input x-ref="captureTitle" x-model="quick.title" maxlength="500" placeholder="یک کار را سریع به امروز اضافه کنید…" aria-label="عنوان وظیفه جدید">
                        <select x-model="quick.projectId" aria-label="انتخاب پروژه">
                            <template x-for="project in projects" :key="project.id"><option :value="project.id" x-text="'پروژه: ' + project.name"></option></template>
                        </select>
                        <button :disabled="saving || !quick.title.trim() || !quick.projectId" x-text="saving ? 'در حال افزودن…' : 'افزودن'"></button>
                    </form>
                @elseif ($canEdit)
                    <a class="today-project-empty" href="{{ route('projects.index', $workspace->slug) }}">برای افزودن کار، ابتدا یک پروژه بسازید.</a>
                @endif
                <section class="today-section today-task-card">
                    <div class="today-group" x-ref="taskList" :class="reordering ? 'is-reordering' : ''">
                        <template x-for="task in activeTasks" :key="task.dbId">
                            <article class="today-row" :data-today-task="task.dbId" :class="busyTasks.includes(task.dbId) ? 'is-busy' : ''">
                                @if ($canEdit)<button type="button" class="today-drag" aria-label="تغییر اولویت" title="برای تغییر اولویت بکشید"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="5" cy="4" r="1"/><circle cx="11" cy="4" r="1"/><circle cx="5" cy="8" r="1"/><circle cx="11" cy="8" r="1"/><circle cx="5" cy="12" r="1"/><circle cx="11" cy="12" r="1"/></svg></button>@endif
                                @if ($canEdit)<button type="button" class="today-check" @click="completeTask(task)" aria-label="انجام شد"></button>@endif
                                <div class="today-row__copy"><strong x-text="task.title"></strong><small><span x-text="task.project.name"></span><template x-if="task.dueDate"><span x-text="' · ' + task.dueDate"></span></template></small></div>
                                <template x-if="task.assignees?.[0]"><span class="today-row__avatar" x-text="task.assignees[0].initials"></span></template>
                                @if ($canEdit)
                                    <div class="today-row__actions">
                                        <button type="button" @click="moveTomorrow(task)">فردا</button>
                                        <button type="button" @click="removeTask(task)" aria-label="برداشتن از امروز" title="برداشتن از امروز">×</button>
                                    </div>
                                @endif
                            </article>
                        </template>
                        <p class="today-empty" x-show="activeCount === 0">هنوز کاری برای امروز انتخاب نکرده‌اید.</p>
                    </div>
                </section>
                @if ($canEdit)<button class="today-add-existing" @click="existingOpen = true">+ افزودن از کارهای موجود</button>@endif
            </div>

            <aside class="today-team-card">
                <div class="today-section__heading"><h2>تیم</h2><a href="{{ route('team.index', $workspace->slug) }}">مشاهده همه</a></div>
                <div class="today-team-list">
                    @foreach ($teamPulse as $member)
                        <div class="today-team-person {{ $member['blocked'] ? 'is-blocked' : '' }}">
                            @if ($member['avatar'])
                                <img src="{{ asset('storage/avatars/'.$member['avatar']) }}" alt="">
                            @else
                                <span>{{ $member['initials'] }}</span>
                            @endif
                            <div><strong>{{ $member['name'] }}</strong><small>{{ $member['focus'] ?: 'امروز کاری ثبت نکرده است' }}@if($member['project']) · {{ $member['project'] }}@endif</small></div>
                            @if ($member['blocked'])<i title="مسدود">!</i>@endif
                        </div>
                    @endforeach
                </div>
            </aside>
        </div>

        <div class="today-toast" x-show="notice.message" x-transition :class="notice.type === 'error' ? 'is-error' : ''" x-text="notice.message" role="status"></div>

        <section class="today-section today-secondary-section" x-show="blockedTasks.length">
            <div class="today-section__heading"><h2>مسدود</h2></div>
            <template x-for="task in blockedTasks" :key="task.dbId">
                <div class="today-row is-blocked" :class="busyTasks.includes(task.dbId) ? 'is-busy' : ''"><span class="today-row__warning">!</span><div><strong x-text="task.title"></strong><small x-text="task.blockedReason || 'منتظر رفع مانع'"></small></div><span class="today-row__project" x-text="task.project.name"></span>@if($canEdit)<div class="today-row__actions"><button type="button" @click="moveTomorrow(task)">فردا</button><button type="button" @click="removeTask(task)" aria-label="برداشتن از امروز">×</button></div>@endif</div>
            </template>
        </section>

        <section class="today-section today-secondary-section" x-show="overdueTasks.length">
            <div class="today-section__heading"><h2>عقب‌افتاده</h2><span x-text="overdueTasks.length"></span></div>
            <template x-for="task in overdueTasks" :key="task.dbId">
                <div class="today-row"><span class="today-row__due">!</span><div><strong x-text="task.title"></strong><small x-text="task.project.name + ' · سررسید ' + task.dueDate"></small></div><button @click="addExisting(task, false)">افزودن به امروز</button></div>
            </template>
        </section>

        <section class="today-section today-secondary-section" x-show="doneTasks.length">
            <div class="today-section__heading"><h2>انجام‌شده امروز</h2><span x-text="doneTasks.length"></span></div>
            <template x-for="task in doneTasks" :key="task.dbId">
                <div class="today-row is-done" :class="busyTasks.includes(task.dbId) ? 'is-busy' : ''">
                    <span>✓</span><strong x-text="task.title"></strong><span class="today-row__project" x-text="task.project.name"></span>
                    @if ($canEdit)<button type="button" class="today-undo" @click="reopenTask(task)">برگرداندن</button>@endif
                </div>
            </template>
        </section>

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
                        <button type="button" @click="addExisting(task, true)" :disabled="busyTasks.includes(task.dbId)"><span><strong x-text="task.title"></strong><small x-text="task.project.name"></small></span><em x-text="busyTasks.includes(task.dbId) ? 'در حال افزودن…' : 'افزودن'"></em></button>
                    </template>
                    <p class="today-empty" x-show="filteredAvailable.length === 0">کار دیگری برای افزودن پیدا نشد.</p>
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
                today: '{{ $todayDate->toDateString() }}', quickOpen: false, existingOpen: false, existingSearch: '', saving: false, reordering: false, busyTasks: [], error: '', notice: { message: '', type: 'success' }, noticeTimer: null, sortable: null,
                quick: { title: '', projectId: @js($projects->first()?->id), when: 'today' },
                get formattedDate() { return new Intl.DateTimeFormat('fa-IR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(this.today + 'T12:00:00')); },
                get activeCount() { return this.mustTasks.length + this.optionalTasks.length; },
                get activeTasks() { return this.mustTasks.concat(this.optionalTasks); },
                get filteredAvailable() { const q = this.existingSearch.trim().toLowerCase(); const planned = new Set(this.activeTasks.concat(this.blockedTasks).map(t => t.dbId)); return this.availableTasks.filter(t => !planned.has(t.dbId) && (!q || t.title.toLowerCase().includes(q) || t.project.name.toLowerCase().includes(q))); },
                init() { this.$nextTick(() => this.setupSortable()); },
                setupSortable() {
                    if (!@js($canEdit) || !this.$refs.taskList || !window.Sortable) return;
                    this.sortable?.destroy();
                    this.sortable = new Sortable(this.$refs.taskList, {
                        animation: 160, handle: '.today-drag', draggable: '[data-today-task]', ghostClass: 'today-sort-ghost', chosenClass: 'today-sort-chosen',
                        onEnd: event => this.persistOrder(event),
                    });
                },
                snapshot() { return { must: [...this.mustTasks], optional: [...this.optionalTasks], blocked: [...this.blockedTasks], done: [...this.doneTasks], available: [...this.availableTasks] }; },
                restore(snapshot) { this.mustTasks = snapshot.must; this.optionalTasks = snapshot.optional; this.blockedTasks = snapshot.blocked; this.doneTasks = snapshot.done; this.availableTasks = snapshot.available; },
                flash(message, type = 'success') { clearTimeout(this.noticeTimer); this.notice = { message, type }; this.noticeTimer = setTimeout(() => this.notice = { message: '', type: 'success' }, 3200); },
                busy(id, active) { this.busyTasks = active ? [...new Set([...this.busyTasks, Number(id)])] : this.busyTasks.filter(value => value !== Number(id)); },
                find(id) { return this.activeTasks.concat(this.blockedTasks, this.doneTasks).find(t => t.dbId === Number(id)); },
                endpoint(task, kind) { const base = @js(route('today.task.state', [$workspace->slug, '__PROJECT__', '__TASK__'], false)); return base.replace('__PROJECT__', task.project.slug).replace('__TASK__', task.dbId).replace('/state', kind); },
                async request(url, method, body) { const r = await fetch(url, { method, headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@js(csrf_token())}, body: body ? JSON.stringify(body) : null }); const data = await r.json().catch(() => ({})); if (!r.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'عملیات انجام نشد.'); return data; },
                async completeTask(task) { if (this.busyTasks.includes(task.dbId)) return; const snapshot = this.snapshot(); this.busy(task.dbId, true); this.removeLocal(task.dbId); this.doneTasks.unshift({...task, completedAt: new Date().toISOString()}); try { const data = await this.request(this.endpoint(task, '/state'), 'PATCH', {action:'complete'}); this.doneTasks = this.doneTasks.map(item => item.dbId === task.dbId ? data.task : item); this.flash('وظیفه انجام شد.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async reopenTask(task) { if (this.busyTasks.includes(task.dbId)) return; const snapshot = this.snapshot(); this.busy(task.dbId, true); this.doneTasks = this.doneTasks.filter(item => item.dbId !== task.dbId); this.mustTasks.push({...task, completedAt:null}); try { const data = await this.request(this.endpoint(task, '/state'), 'PATCH', {action:'reopen'}); this.mustTasks = this.mustTasks.map(item => item.dbId === task.dbId ? data.task : item); this.flash('وظیفه به فهرست امروز برگشت.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async removeTask(task) { if (this.busyTasks.includes(task.dbId)) return; const snapshot = this.snapshot(); this.busy(task.dbId, true); this.removeLocal(task.dbId); this.availableTasks.unshift({...task, plan:null}); try { await this.request(this.endpoint(task, '/plan'), 'DELETE', {planned_for:this.today}); this.flash('از فهرست امروز برداشته شد.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async moveTomorrow(task) { if (this.busyTasks.includes(task.dbId)) return; const snapshot = this.snapshot(); this.busy(task.dbId, true); this.removeLocal(task.dbId); try { await this.request(this.endpoint(task, '/plan/tomorrow'), 'PATCH'); this.flash('به فردا منتقل شد.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                removeLocal(id) { for (const key of ['mustTasks','optionalTasks','blockedTasks']) this[key] = this[key].filter(t => t.dbId !== Number(id)); },
                async addExisting(task, close) { if (this.busyTasks.includes(task.dbId)) return; this.busy(task.dbId, true); try { const assigned = task.assignees.some(a => a.id === @js(auth()->id())); const data = await this.request(this.endpoint(task, '/plan'), 'PUT', {planned_for:this.today,bucket:'must',assign_to_me:!assigned}); if (data.task.isBlocked) this.blockedTasks.push(data.task); else this.mustTasks.push(data.task); this.availableTasks = this.availableTasks.filter(t => t.dbId !== task.dbId); this.overdueTasks = this.overdueTasks.filter(t => t.dbId !== task.dbId); if(close) this.existingOpen=false; this.flash('به امروز اضافه شد.'); } catch(e) { this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async createTodayTask() { if (!this.quick.title.trim() || !this.quick.projectId) return; this.quick.when = 'today'; await this.createTask(false); },
                async createTask(closeModal = true) { if (this.saving) return; this.saving=true; this.error=''; const title = this.quick.title.trim(); try { const payload={title, project_id:this.quick.projectId, when:this.quick.when, bucket:'must'}; const data=await this.request(@js(route('today.tasks.store', $workspace->slug, false)), 'POST', payload); if(this.quick.when==='today') this.mustTasks.push(data.task); const destination = this.quick.when === 'today' ? 'به امروز اضافه شد.' : (this.quick.when === 'tomorrow' ? 'برای فردا ساخته شد.' : 'بدون برنامه ساخته شد.'); this.quick={title:'',projectId:this.quick.projectId,when:'today'}; if(closeModal) this.quickOpen=false; this.flash(destination); this.$nextTick(() => { if (!closeModal) this.$refs.captureTitle?.focus(); }); } catch(e) { this.error=e.message; this.flash(e.message, 'error'); } finally { this.saving=false; } },
                async persistOrder(event) { const snapshot = this.snapshot(); const ids = [...this.$refs.taskList.querySelectorAll('[data-today-task]')].map(row => Number(row.dataset.todayTask)); const byId = new Map(this.activeTasks.map(task => [task.dbId, task])); const ordered = ids.map(id => byId.get(id)).filter(Boolean); if (ordered.length !== this.activeCount) { this.sortable.sort(this.activeTasks.map(task => String(task.dbId))); return; } this.mustTasks = ordered.map((task,index) => ({...task, plan:{...(task.plan || {}), bucket:'must', position:index+1}})); this.optionalTasks=[]; this.reordering=true; try { await this.request(@js(route('today.tasks.reorder', $workspace->slug, false)), 'PATCH', {task_ids:ids}); this.flash('اولویت‌ها ذخیره شد.'); } catch(e) { this.restore(snapshot); this.$nextTick(() => this.sortable.sort(this.activeTasks.map(task => String(task.dbId)))); this.flash(e.message, 'error'); } finally { this.reordering=false; } },
                handleShortcut(e) { if (!@js($canEdit) || this.projects.length === 0 || e.key.toLowerCase() !== 'c' || e.metaKey || e.ctrlKey || e.altKey || ['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName) || e.target.isContentEditable) return; e.preventDefault(); this.quickOpen=true; this.$nextTick(() => this.$refs.quickTitle.focus()); }
            }
        }
    </script>
    @endpush
</x-workspace-shell>
@endsection
