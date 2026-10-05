@props(['workspace', 'projects' => null, 'active' => 'today', 'board' => false, 'activeProject' => null])

@php
    $workspaceContext = app(\App\Services\WorkspaceContext::class);
    $shellWorkspaces = $workspaceContext->all(auth()->user());
    $shellProjects = $workspaceContext->visibleProjects($workspace, auth()->user());
    $recentIds = collect(session("recent_projects.{$workspace->id}", [session("last_project.{$workspace->id}")]))->filter()->values();
    $recentProjects = $shellProjects->sortBy(fn ($project) => ($index = $recentIds->search($project->id)) !== false ? $index : $recentIds->count())->take(5);
    $canManageWorkspace = $workspace->canManageMembers(auth()->user());
@endphp

<div class="workspace-shell workspace-shell--unified workspace-shell--{{ $active }} {{ $board ? 'workspace-shell--board' : '' }} min-h-screen"
     x-data="workspaceShell({ board: {{ $board ? 'true' : 'false' }}, searchUrl: @js(route('workspace.search', $workspace->slug, false)) })"
     style="--shell-sidebar: 236px"
     :style="{ '--shell-sidebar': sidebarCollapsed ? '64px' : '236px' }"
     :class="{ 'workspace-shell--collapsed': sidebarCollapsed }"
     @keydown.slash.window="openSearch($event)"
     @keydown.window="openSearchShortcut($event)">
    <aside class="workspace-sidebar" :class="{ 'is-collapsed': sidebarCollapsed }">
        <div class="workspace-sidebar__brand">
            <a href="{{ route('today', $workspace->slug) }}" aria-label="خانه نئووا">
                <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا">
            </a>
            <button type="button" @click="toggleSidebar()" :aria-expanded="!sidebarCollapsed" aria-label="باز و بسته کردن نوار کناری" title="باز و بسته کردن نوار کناری"><x-workspace-icon name="panel-right-close" x-show="!sidebarCollapsed" /><x-workspace-icon name="panel-right-open" x-show="sidebarCollapsed" x-cloak /></button>
        </div>

        <nav class="workspace-nav" aria-label="ناوبری اصلی">
            <a href="{{ route('today', $workspace->slug) }}" title="امروز" aria-label="امروز" class="workspace-nav__item {{ $active === 'today' ? 'is-active' : '' }}"><span><x-workspace-icon name="calendar-days" /></span><b>امروز</b></a>
            <a href="{{ route('projects.index', $workspace->slug) }}" title="پروژه‌ها" aria-label="پروژه‌ها" class="workspace-nav__item {{ $active === 'projects' ? 'is-active' : ($active === 'board' ? 'is-parent-active' : '') }}"><span><x-workspace-icon name="folders" /></span><b>پروژه‌ها</b></a>
            <a href="{{ route('team.index', $workspace->slug) }}" title="تیم" aria-label="تیم" class="workspace-nav__item {{ $active === 'team' ? 'is-active' : '' }}"><span><x-workspace-icon name="users-round" /></span><b>تیم</b></a>
        </nav>

        <div class="workspace-sidebar__projects">
            <div><p>پروژه‌های اخیر</p>@if ($canManageWorkspace)<a href="{{ route('projects.index', $workspace->slug) }}#project-create" aria-label="پروژه جدید" title="پروژه جدید">+</a>@endif</div>
            @foreach ($recentProjects as $shellProject)
                <a href="{{ route('board', [$workspace->slug, $shellProject->slug]) }}" title="{{ $shellProject->name }}" @if ((string) $activeProject === (string) $shellProject->slug) aria-current="page" @endif class="{{ (string) $activeProject === (string) $shellProject->slug ? 'is-active' : '' }}">
                    <span>{{ mb_substr($shellProject->name, 0, 1) }}</span><b>{{ $shellProject->name }}</b>
                </a>
            @endforeach
            @if ($shellProjects->isEmpty())<small>هنوز پروژه‌ای ساخته نشده است.</small>@endif
        </div>

        <div class="workspace-sidebar__footer">
            <button type="button" class="workspace-context-trigger" data-workspace-context @click="toggleContextMenu($el)" @keydown.arrow-down.prevent="openContextMenu($el)" :aria-expanded="contextOpen" aria-controls="workspace-context-menu" aria-label="فضای کاری و حساب کاربری" title="{{ $workspace->name }} · {{ auth()->user()->full_name }}">
                @if(auth()->user()->avatar)<img class="workspace-context-avatar" src="{{ asset('storage/avatars/'.auth()->user()->avatar) }}" alt="">@else<span class="workspace-context-avatar">{{ auth()->user()->initials }}</span>@endif
                <span class="workspace-context-copy"><strong>{{ $workspace->name }}</strong><small>{{ auth()->user()->full_name }}</small></span>
                <span class="workspace-context-chevron" aria-hidden="true">⌃</span>
            </button>
        </div>
    </aside>

    <div class="workspace-stage">
        <header class="workspace-topbar">
            <div class="workspace-mobile-brand">
                <img src="{{ asset('assets/logo/png/symbol-primary-color.png') }}" alt="نئووا">
                <button type="button" data-workspace-context @click="toggleContextMenu($el)" @keydown.arrow-down.prevent="openContextMenu($el)" :aria-expanded="contextOpen" aria-controls="workspace-context-menu" aria-label="فضای کاری و حساب کاربری" title="{{ $workspace->name }} · {{ auth()->user()->full_name }}"><strong>{{ $workspace->name }}</strong><span aria-hidden="true">⌄</span></button>
            </div>
            @if($board)
                {{ $context ?? '' }}
            @else
                <div class="workspace-header-context">
                    {{ $context ?? '' }}
                    <small class="workspace-header-workspace" title="{{ $workspace->name }}">{{ $workspace->name }}</small>
                </div>
            @endif
            <button type="button" class="workspace-search-trigger" @click="showSearch()" aria-label="جستجوی وظیفه یا پروژه"><span>⌕</span><b>جستجوی وظیفه یا پروژه…</b><kbd>/</kbd></button>
            <div class="workspace-topbar__actions">
                {{ $toolbar ?? '' }}
                <x-notification-menu />
            </div>
        </header>

        <main class="workspace-main">{{ $slot }}</main>
    </div>

    <nav class="workspace-mobile-nav" aria-label="ناوبری موبایل">
        <a href="{{ route('today', $workspace->slug) }}" class="{{ $active === 'today' ? 'is-active' : '' }}" aria-label="امروز" title="امروز"><span><x-workspace-icon name="calendar-days" /></span><b>امروز</b></a>
        <a href="{{ route('projects.index', $workspace->slug) }}" class="{{ in_array($active, ['projects', 'board']) ? 'is-active' : '' }}" aria-label="پروژه‌ها" title="پروژه‌ها"><span><x-workspace-icon name="folders" /></span><b>پروژه‌ها</b></a>
        <a href="{{ route('team.index', $workspace->slug) }}" class="{{ $active === 'team' ? 'is-active' : '' }}" aria-label="تیم" title="تیم"><span><x-workspace-icon name="users-round" /></span><b>تیم</b></a>
    </nav>

    <template x-teleport="body">
        <section id="workspace-context-menu" x-ref="contextMenu" x-show="contextOpen" x-cloak x-transition.opacity class="workspace-context-menu" data-workspace-context :style="contextMenuStyle()" aria-label="فضای کاری و حساب کاربری" @keydown="handleContextMenuKey($event)" @keydown.escape.window="if (contextOpen) { $event.preventDefault(); $event.stopPropagation(); closeContextMenu(true) }" @pointerdown.window="if (contextOpen && !$event.target.closest('[data-workspace-context]')) closeContextMenu()" @focusout="if ($event.relatedTarget && !$el.contains($event.relatedTarget) && !$event.relatedTarget.closest('[data-workspace-context]')) closeContextMenu()" @resize.window="closeContextMenu()">
            <header class="workspace-context-menu__identity">
                @if(auth()->user()->avatar)<img class="workspace-context-avatar" src="{{ asset('storage/avatars/'.auth()->user()->avatar) }}" alt="">@else<span class="workspace-context-avatar">{{ auth()->user()->initials }}</span>@endif
                <div><strong>{{ auth()->user()->full_name }}</strong><small>{{ auth()->user()->phone }}</small></div>
            </header>
            <p class="workspace-context-menu__label" id="workspace-context-workspaces">فضاهای کاری</p>
            <nav class="workspace-context-menu__workspaces" aria-labelledby="workspace-context-workspaces">
                @foreach ($shellWorkspaces as $shellWorkspace)
                    <a href="{{ route('today', $shellWorkspace->slug) }}" class="workspace-context-menu__workspace {{ $shellWorkspace->id === $workspace->id ? 'is-current' : '' }}" @if ($shellWorkspace->id === $workspace->id) aria-current="true" @endif>
                        <span class="workspace-context-menu__mark">{{ mb_substr($shellWorkspace->name, 0, 1) }}</span><strong>{{ $shellWorkspace->name }}</strong>
                        @if ($shellWorkspace->id === $workspace->id)<span aria-label="فضای کاری فعلی">✓</span>@endif
                    </a>
                @endforeach
            </nav>
            <div class="workspace-context-menu__group">
                @if ($canManageWorkspace)<a href="{{ route('workspaces.settings', $workspace->slug) }}">تنظیمات فضای کاری</a>@endif
                <button type="button" @click="closeContextMenu(); workspaceCreating = true">+ فضای کاری جدید</button>
            </div>
            <div class="workspace-context-menu__group">
                <a href="{{ route('profile') }}">پروفایل و تنظیمات</a>
                <a href="{{ route('profile') }}#notification-preferences">تنظیمات اعلان‌ها</a>
            </div>
            <form class="workspace-context-menu__group" method="POST" action="{{ route('auth.logout') }}">@csrf<button type="submit" class="workspace-context-menu__logout">خروج</button></form>
        </section>
    </template>

    <div x-show="searchOpen" x-cloak class="workspace-command" @keydown.escape.window="searchOpen=false">
        <button class="workspace-command__backdrop" @click="searchOpen=false" aria-label="بستن"></button>
        <section class="workspace-command__panel">
            <div class="workspace-command__input"><span>⌕</span><input x-ref="searchInput" x-model="searchQuery" @input.debounce.250ms="search()" placeholder="جستجوی پروژه یا وظیفه…"><kbd>Esc</kbd></div>
            <div class="workspace-command__results">
                <p x-show="searchLoading">در حال جستجو…</p>
                <p x-show="!searchLoading && searchQuery && searchResults.length === 0">نتیجه‌ای پیدا نشد.</p>
                <template x-for="result in searchResults" :key="result.type + result.url">
                    <a :href="result.url"><span x-text="result.type === 'project' ? 'پروژه' : 'وظیفه'"></span><div><strong x-text="result.name"></strong><small x-text="result.subtitle"></small></div></a>
                </template>
            </div>
        </section>
    </div>

    <div x-show="workspaceCreating" x-cloak class="workspace-command" @keydown.escape.window="workspaceCreating=false">
        <button class="workspace-command__backdrop" @click="workspaceCreating=false" aria-label="بستن"></button>
        <form class="workspace-create-dialog" method="POST" action="{{ route('dashboard.workspace.store') }}">
            @csrf
            <h2>فضای کاری جدید</h2><p>یک خانه ساده برای پروژه‌ها و کارهای تیم.</p>
            <input name="name" required maxlength="100" placeholder="نام فضای کاری">
            <div><button type="button" @click="workspaceCreating=false">انصراف</button><button type="submit">ایجاد</button></div>
        </form>
    </div>
</div>

@once
@push('scripts')
<script>
function workspaceShell(config) {
    return {
        sidebarCollapsed: localStorage.getItem('neova_board_sidebar') === 'collapsed',
        contextOpen: false, contextAnchor: null, workspaceCreating: false,
        searchOpen: false, searchQuery: '', searchResults: [], searchLoading: false,
        toggleSidebar() { this.sidebarCollapsed = !this.sidebarCollapsed; localStorage.setItem('neova_board_sidebar', this.sidebarCollapsed ? 'collapsed' : 'expanded'); },
        toggleContextMenu(anchor) {
            if (this.contextOpen && this.contextAnchor === anchor) { this.closeContextMenu(); return; }
            this.contextAnchor = anchor;
            this.contextOpen = true;
        },
        openContextMenu(anchor) {
            this.contextAnchor = anchor;
            this.contextOpen = true;
            this.$nextTick(() => this.$refs.contextMenu.querySelector('a, button')?.focus());
        },
        closeContextMenu(restoreFocus = false) {
            this.contextOpen = false;
            if (restoreFocus) this.contextAnchor?.focus();
        },
        contextMenuStyle() {
            if (!this.contextAnchor) return {};
            const rect = this.contextAnchor.getBoundingClientRect();
            const width = Math.min(280, window.innerWidth - 24);
            const right = Math.max(12, Math.min(window.innerWidth - rect.right, window.innerWidth - width - 12));
            const mobile = window.innerWidth < 768;
            return {
                width: width + 'px', right: right + 'px',
                top: mobile ? (rect.bottom + 8) + 'px' : 'auto',
                bottom: mobile ? 'auto' : (window.innerHeight - rect.top + 8) + 'px',
                maxHeight: Math.max(120, mobile ? window.innerHeight - rect.bottom - 90 : rect.top - 24) + 'px',
            };
        },
        handleContextMenuKey(event) {
            if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const items = Array.from(this.$refs.contextMenu.querySelectorAll('a, button'));
            const index = items.indexOf(document.activeElement);
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? items.length - 1 : (index + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
            items[next]?.focus();
        },
        showSearch() { this.searchOpen=true; this.$nextTick(() => this.$refs.searchInput.focus()); },
        openSearch(event) { if (event.ctrlKey || event.metaKey || event.altKey || ['INPUT','TEXTAREA','SELECT'].includes(event.target.tagName) || event.target.isContentEditable) return; event.preventDefault(); this.showSearch(); },
        openSearchShortcut(event) { if (!(event.ctrlKey || event.metaKey) || event.key.toLowerCase() !== 'k' || ['INPUT','TEXTAREA','SELECT'].includes(event.target.tagName) || event.target.isContentEditable) return; event.preventDefault(); this.showSearch(); },
        async search() { if (!this.searchQuery.trim()) { this.searchResults=[]; return; } this.searchLoading=true; try { const response=await fetch(config.searchUrl+'?q='+encodeURIComponent(this.searchQuery), {headers:{Accept:'application/json'}}); this.searchResults=response.ok ? await response.json() : []; } finally { this.searchLoading=false; } }
    };
}
</script>
@endpush
@endonce
