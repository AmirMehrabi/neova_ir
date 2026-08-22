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

        @if ($canManageTeam)
            <nav class="today-view-tabs" aria-label="نمای امروز">
                <a href="{{ route('today', $workspace->slug) }}?view=mine" class="{{ $viewMode === 'mine' ? 'is-active' : '' }}">امروز من</a>
                <a href="{{ route('today', $workspace->slug) }}?view=team" class="{{ $viewMode === 'team' ? 'is-active' : '' }}">امروز تیم</a>
            </nav>
        @endif

        @if ($viewMode === 'mine')
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
        @else
        <section class="today-team-workspace">
            @if ($canEdit && $projects->isNotEmpty())
                <form class="today-capture today-team-capture" @submit.prevent="createTodayTask()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <input x-ref="captureTitle" x-model="quick.title" maxlength="500" placeholder="یک کار را سریع به امروز تیم اضافه کنید…">
                    <select x-model="quick.userId" aria-label="انتخاب مسئول"><template x-for="person in people" :key="person.id"><option :value="person.id" x-text="person.name"></option></template></select>
                    <select x-model="quick.projectId" aria-label="انتخاب پروژه"><template x-for="project in eligibleProjects" :key="project.id"><option :value="project.id" x-text="'پروژه: ' + project.name"></option></template></select>
                    <button :disabled="saving || !quick.title.trim() || !quick.projectId || !quick.userId" x-text="saving ? 'در حال افزودن…' : 'افزودن'"></button>
                </form>
            @endif
            <div class="today-team-groups">
                <template x-for="member in teamDays" :key="member.id">
                    <section class="today-team-group">
                        <header class="today-team-group__header">
                            <div class="today-team-group__person"><span x-text="member.initials"></span><div><h2 x-text="member.name"></h2><small x-text="teamRemaining(member) + ' باقی‌مانده · ' + member.doneTasks.length + ' انجام‌شده'"></small></div></div>
                            <button type="button" class="today-add-existing" @click="openExistingFor(member.id)">+ افزودن وظیفه</button>
                        </header>
                        <div class="today-section today-task-card today-team-task-list" :data-team-list="member.id">
                            <template x-for="task in teamActive(member)" :key="task.dbId">
                                <article class="today-row" :data-today-task="task.dbId" :class="busyTasks.includes(task.dbId) ? 'is-busy' : ''">
                                    <button type="button" class="today-drag" aria-label="تغییر اولویت"><svg viewBox="0 0 16 16" fill="currentColor"><circle cx="5" cy="4" r="1"/><circle cx="11" cy="4" r="1"/><circle cx="5" cy="8" r="1"/><circle cx="11" cy="8" r="1"/><circle cx="5" cy="12" r="1"/><circle cx="11" cy="12" r="1"/></svg></button>
                                    <button type="button" class="today-check" @click="completeTask(task, member.id)" aria-label="انجام شد"></button>
                                    <div class="today-row__copy"><strong x-text="task.title"></strong><small><span x-text="task.project.name"></span><template x-if="task.isBlocked"><span x-text="' · مسدود: ' + (task.blockedReason || '')"></span></template></small></div>
                                    <span class="today-row__avatar" x-text="member.initials"></span>
                                    <div class="today-row__actions"><button type="button" @click="moveTomorrow(task, member.id)">فردا</button><button type="button" @click="removeTask(task, member.id)" aria-label="برداشتن از امروز">×</button></div>
                                </article>
                            </template>
                            <p class="today-empty" x-show="teamRemaining(member) === 0 && member.doneTasks.length === 0">برای امروز کاری ثبت نشده است.</p>
                            <template x-for="task in member.blockedTasks" :key="'blocked-' + task.dbId">
                                <article class="today-row is-blocked" :class="busyTasks.includes(task.dbId) ? 'is-busy' : ''"><span class="today-row__warning">!</span><div class="today-row__copy"><strong x-text="task.title"></strong><small x-text="task.blockedReason || 'منتظر رفع مانع'"></small></div><span class="today-row__project" x-text="task.project.name"></span><div class="today-row__actions"><button type="button" @click="completeTask(task, member.id)">انجام شد</button><button type="button" @click="moveTomorrow(task, member.id)">فردا</button><button type="button" @click="removeTask(task, member.id)">×</button></div></article>
                            </template>
                            <template x-for="task in member.doneTasks" :key="'done-' + task.dbId">
                                <div class="today-row is-done"><span>✓</span><strong x-text="task.title"></strong><span class="today-row__project" x-text="task.project.name"></span><button type="button" class="today-undo" @click="reopenTask(task, member.id)">برگرداندن</button></div>
                            </template>
                        </div>
                    </section>
                </template>
            </div>
        </section>
        @endif

        <div class="today-dialog" x-show="quickOpen" x-cloak @keydown.escape.window="quickOpen = false">
            <div class="today-dialog__backdrop" @click="quickOpen = false"></div>
            <form class="today-dialog__panel" @submit.prevent="createTask()">
                <h2>چه کاری باید انجام شود؟</h2>
                <input x-ref="quickTitle" x-model="quick.title" required maxlength="500" placeholder="عنوان وظیفه">
                <label>پروژه<select x-model="quick.projectId" required><template x-for="project in eligibleProjects" :key="project.id"><option :value="project.id" x-text="project.name"></option></template></select></label>
                @if ($canManageTeam)<label>مسئول<select x-model="quick.userId" required><template x-for="person in people" :key="person.id"><option :value="person.id" x-text="person.name"></option></template></select></label>@endif
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
                availableTasks: @js($availableTasks), projects: @js($projects->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'eligibleUserIds' => $projectEligibility->get($p->id, [])])->values()),
                teamDays: @js($teamDays), people: @js($workspacePeople->map(fn($p) => ['id' => $p->id, 'name' => $p->full_name])->values()), viewMode: @js($viewMode),
                today: '{{ $todayDate->toDateString() }}', quickOpen: false, existingOpen: false, existingTargetId: @js(auth()->id()), existingSearch: '', saving: false, reordering: false, busyTasks: [], error: '', notice: { message: '', type: 'success' }, noticeTimer: null, sortable: null, teamSortables: [],
                quick: { title: '', projectId: @js($projects->first()?->id), userId: @js(auth()->id()), when: 'today' },
                get formattedDate() { return new Intl.DateTimeFormat('fa-IR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(this.today + 'T12:00:00')); },
                get activeCount() { return this.mustTasks.length + this.optionalTasks.length; },
                get activeTasks() { return this.mustTasks.concat(this.optionalTasks); },
                get eligibleProjects() { const id=Number(this.quick.userId); return this.projects.filter(p => p.eligibleUserIds.includes(id)); },
                get filteredAvailable() { const q = this.existingSearch.trim().toLowerCase(); const member=this.teamDays.find(m=>m.id===Number(this.existingTargetId)); const planned = new Set(member ? this.teamActive(member).concat(member.doneTasks).map(t=>t.dbId) : this.activeTasks.concat(this.blockedTasks).map(t => t.dbId)); return this.availableTasks.filter(t => t.eligibleUserIds.includes(Number(this.existingTargetId)) && !planned.has(t.dbId) && (!q || t.title.toLowerCase().includes(q) || t.project.name.toLowerCase().includes(q))); },
                realtimeRefresher: null,
                realtimeDragActive: false,
                pendingRealtimeSnapshot: null,
                init() {
                    this.$watch('quick.userId', () => { if(!this.eligibleProjects.some(p=>p.id===Number(this.quick.projectId))) this.quick.projectId=this.eligibleProjects[0]?.id || ''; });
                    this.realtimeRefresher = window.createRealtimeRefresher({
                        url: @js(route('today.realtime.snapshot', $workspace->slug, false)),
                        apply: payload => this.applyRealtimeSnapshot(payload),
                    });
                    window.subscribeTodayRealtime(@js($workspace->id), () => this.realtimeRefresher.schedule());
                    window.addEventListener('neova:realtime-reconnected', () => this.realtimeRefresher.refreshNow());
                    this.$nextTick(() => { this.setupSortable(); this.setupTeamSortables(); });
                },
                applyRealtimeSnapshot(payload) {
                    if (this.realtimeDragActive || this.reordering) { this.pendingRealtimeSnapshot = payload; return; }
                    for (const key of ['mustTasks','optionalTasks','blockedTasks','doneTasks','overdueTasks','availableTasks','teamDays','projects','people']) {
                        if (payload[key] !== undefined) this[key] = payload[key];
                    }
                    this.$nextTick(() => { this.setupSortable(); this.setupTeamSortables(); });
                },
                finishRealtimeDrag() { window.setTimeout(() => { this.realtimeDragActive=false; if(this.reordering){window.setTimeout(()=>this.finishRealtimeDrag(),50);return;} if(!this.pendingRealtimeSnapshot)return; const payload=this.pendingRealtimeSnapshot; this.pendingRealtimeSnapshot=null; this.applyRealtimeSnapshot(payload); }, 0); },
                teamActive(member) { return member.mustTasks.concat(member.optionalTasks); },
                teamRemaining(member) { return this.teamActive(member).length + member.blockedTasks.length; },
                openExistingFor(userId) { this.existingTargetId=Number(userId); this.existingSearch=''; this.existingOpen=true; },
                setupSortable() {
                    if (this.viewMode !== 'mine' || !@js($canEdit) || !this.$refs.taskList || !window.Sortable) return;
                    this.sortable?.destroy();
                    this.sortable = new Sortable(this.$refs.taskList, {
                        animation: 160, handle: '.today-drag', draggable: '[data-today-task]', ghostClass: 'today-sort-ghost', chosenClass: 'today-sort-chosen',
                        onStart: () => { this.realtimeDragActive=true; },
                        onEnd: event => { this.persistOrder(event); this.finishRealtimeDrag(); },
                    });
                },
                setupTeamSortables() {
                    if (this.viewMode !== 'team' || !@js($canEdit) || !window.Sortable) return;
                    this.teamSortables.forEach(item => item.destroy()); this.teamSortables=[];
                    document.querySelectorAll('[data-team-list]').forEach(list => {
                        this.teamSortables.push(new Sortable(list, { animation:160, handle:'.today-drag', draggable:'[data-today-task]', ghostClass:'today-sort-ghost', chosenClass:'today-sort-chosen', onStart:()=>{this.realtimeDragActive=true;}, onEnd:()=>{this.persistTeamOrder(Number(list.dataset.teamList), list);this.finishRealtimeDrag();} }));
                    });
                },
                snapshot() { return { must: [...this.mustTasks], optional: [...this.optionalTasks], blocked: [...this.blockedTasks], done: [...this.doneTasks], available: [...this.availableTasks] }; },
                restore(snapshot) { this.mustTasks = snapshot.must; this.optionalTasks = snapshot.optional; this.blockedTasks = snapshot.blocked; this.doneTasks = snapshot.done; this.availableTasks = snapshot.available; },
                flash(message, type = 'success') { clearTimeout(this.noticeTimer); this.notice = { message, type }; this.noticeTimer = setTimeout(() => this.notice = { message: '', type: 'success' }, 3200); },
                busy(id, active) { this.busyTasks = active ? [...new Set([...this.busyTasks, Number(id)])] : this.busyTasks.filter(value => value !== Number(id)); },
                find(id) { return this.activeTasks.concat(this.blockedTasks, this.doneTasks).find(t => t.dbId === Number(id)); },
                endpoint(task, kind) { const base = @js(route('today.task.state', [$workspace->slug, '__PROJECT__', '__TASK__'], false)); return base.replace('__PROJECT__', task.project.slug).replace('__TASK__', task.dbId).replace('/state', kind); },
                async request(url, method, body) { const r = await window.neovaFetch(url, { method, headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@js(csrf_token())}, body: body ? JSON.stringify(body) : null }); const data = await r.json().catch(() => ({})); if (!r.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'عملیات انجام نشد.'); return data; },
                async completeTask(task, targetId=null) { if (this.busyTasks.includes(task.dbId)) return; if(targetId){this.busy(task.dbId,true);try{await this.request(this.endpoint(task,'/state'),'PATCH',{action:'complete'});location.reload();}catch(e){this.flash(e.message,'error');this.busy(task.dbId,false);}return;} const snapshot = this.snapshot(); this.busy(task.dbId, true); this.removeLocal(task.dbId); this.doneTasks.unshift({...task, completedAt: new Date().toISOString()}); try { const data = await this.request(this.endpoint(task, '/state'), 'PATCH', {action:'complete'}); this.doneTasks = this.doneTasks.map(item => item.dbId === task.dbId ? data.task : item); this.flash('وظیفه انجام شد.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async reopenTask(task, targetId=null) { if (this.busyTasks.includes(task.dbId)) return; if(targetId){this.busy(task.dbId,true);try{await this.request(this.endpoint(task,'/state'),'PATCH',{action:'reopen'});location.reload();}catch(e){this.flash(e.message,'error');this.busy(task.dbId,false);}return;} const snapshot = this.snapshot(); this.busy(task.dbId, true); this.doneTasks = this.doneTasks.filter(item => item.dbId !== task.dbId); this.mustTasks.push({...task, completedAt:null}); try { const data = await this.request(this.endpoint(task, '/state'), 'PATCH', {action:'reopen'}); this.mustTasks = this.mustTasks.map(item => item.dbId === task.dbId ? data.task : item); this.flash('وظیفه به فهرست امروز برگشت.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async removeTask(task, targetId=null) { if (this.busyTasks.includes(task.dbId)) return; if(targetId){this.busy(task.dbId,true);try{await this.request(this.endpoint(task,'/plan'),'DELETE',{planned_for:this.today,user_id:targetId});location.reload();}catch(e){this.flash(e.message,'error');this.busy(task.dbId,false);}return;} const snapshot = this.snapshot(); this.busy(task.dbId, true); this.removeLocal(task.dbId); this.availableTasks.unshift({...task, plan:null}); try { await this.request(this.endpoint(task, '/plan'), 'DELETE', {planned_for:this.today}); this.flash('از فهرست امروز برداشته شد.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async moveTomorrow(task, targetId=null) { if (this.busyTasks.includes(task.dbId)) return; if(targetId){this.busy(task.dbId,true);try{await this.request(this.endpoint(task,'/plan/tomorrow'),'PATCH',{user_id:targetId});location.reload();}catch(e){this.flash(e.message,'error');this.busy(task.dbId,false);}return;} const snapshot = this.snapshot(); this.busy(task.dbId, true); this.removeLocal(task.dbId); try { await this.request(this.endpoint(task, '/plan/tomorrow'), 'PATCH'); this.flash('به فردا منتقل شد.'); } catch(e) { this.restore(snapshot); this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                removeLocal(id) { for (const key of ['mustTasks','optionalTasks','blockedTasks']) this[key] = this[key].filter(t => t.dbId !== Number(id)); },
                async addExisting(task, close) { if (this.busyTasks.includes(task.dbId)) return; this.busy(task.dbId, true); try { const targetId=Number(this.existingTargetId); const assigned = task.assignees.some(a => a.id === targetId); const data = await this.request(this.endpoint(task, '/plan'), 'PUT', {planned_for:this.today,bucket:'must',user_id:targetId,assign_to_me:!assigned}); if(this.viewMode==='team'){location.reload();return;} if (data.task.isBlocked) this.blockedTasks.push(data.task); else this.mustTasks.push(data.task); this.availableTasks = this.availableTasks.filter(t => t.dbId !== task.dbId); this.overdueTasks = this.overdueTasks.filter(t => t.dbId !== task.dbId); if(close) this.existingOpen=false; this.flash('به امروز اضافه شد.'); } catch(e) { this.flash(e.message, 'error'); } finally { this.busy(task.dbId, false); } },
                async createTodayTask() { if (!this.quick.title.trim() || !this.quick.projectId) return; this.quick.when = 'today'; await this.createTask(false); },
                async createTask(closeModal = true) { if (this.saving) return; this.saving=true; this.error=''; const title = this.quick.title.trim(); try { const payload={title, project_id:this.quick.projectId, user_id:this.quick.userId, when:this.quick.when, bucket:'must'}; const data=await this.request(@js(route('today.tasks.store', $workspace->slug, false)), 'POST', payload); if(this.viewMode==='team'){location.reload();return;} if(this.quick.when==='today') this.mustTasks.push(data.task); const destination = this.quick.when === 'today' ? 'به امروز اضافه شد.' : (this.quick.when === 'tomorrow' ? 'برای فردا ساخته شد.' : 'بدون برنامه ساخته شد.'); this.quick={title:'',projectId:this.quick.projectId,userId:this.quick.userId,when:'today'}; if(closeModal) this.quickOpen=false; this.flash(destination); this.$nextTick(() => { if (!closeModal) this.$refs.captureTitle?.focus(); }); } catch(e) { this.error=e.message; this.flash(e.message, 'error'); } finally { this.saving=false; } },
                async persistOrder(event) { const snapshot = this.snapshot(); const ids = [...this.$refs.taskList.querySelectorAll('[data-today-task]')].map(row => Number(row.dataset.todayTask)); const byId = new Map(this.activeTasks.map(task => [task.dbId, task])); const ordered = ids.map(id => byId.get(id)).filter(Boolean); if (ordered.length !== this.activeCount) { this.sortable.sort(this.activeTasks.map(task => String(task.dbId))); return; } this.mustTasks = ordered.map((task,index) => ({...task, plan:{...(task.plan || {}), bucket:'must', position:index+1}})); this.optionalTasks=[]; this.reordering=true; try { await this.request(@js(route('today.tasks.reorder', $workspace->slug, false)), 'PATCH', {task_ids:ids}); this.flash('اولویت‌ها ذخیره شد.'); } catch(e) { this.restore(snapshot); this.$nextTick(() => this.sortable.sort(this.activeTasks.map(task => String(task.dbId)))); this.flash(e.message, 'error'); } finally { this.reordering=false; } },
                async persistTeamOrder(userId, list) { const ids=[...list.querySelectorAll('[data-today-task]')].map(row=>Number(row.dataset.todayTask)); if(!ids.length)return; try{await this.request(@js(route('today.tasks.reorder', $workspace->slug, false)),'PATCH',{task_ids:ids,user_id:userId});this.flash('اولویت‌ها ذخیره شد.');}catch(e){this.flash(e.message,'error');location.reload();} },
                handleShortcut(e) { if (!@js($canEdit) || this.projects.length === 0 || e.key.toLowerCase() !== 'c' || e.metaKey || e.ctrlKey || e.altKey || ['INPUT','TEXTAREA','SELECT'].includes(e.target.tagName) || e.target.isContentEditable) return; e.preventDefault(); this.quickOpen=true; this.$nextTick(() => this.$refs.quickTitle.focus()); }
            }
        }
    </script>
    @endpush
</x-workspace-shell>
@endsection
