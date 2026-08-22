@props(['workspace', 'projects' => null, 'active' => 'today', 'board' => false, 'activeProject' => null])

@php
    $workspaceContext = app(\App\Services\WorkspaceContext::class);
    $shellWorkspaces = $workspaceContext->all(auth()->user());
    $shellProjects = $workspaceContext->visibleProjects($workspace, auth()->user());
    $canManageWorkspace = $workspace->canManageMembers(auth()->user());
@endphp

<div class="workspace-shell workspace-shell--{{ $active }} {{ $board ? 'workspace-shell--board' : '' }} min-h-screen bg-[#FBFDFF]"
     x-data="workspaceShell({ board: {{ $board ? 'true' : 'false' }}, searchUrl: @js(route('workspace.search', $workspace->slug, false)) })"
     :class="{ 'workspace-shell--collapsed': sidebarCollapsed }"
     @keydown.slash.window="openSearch($event)">
    <aside class="workspace-sidebar" :class="{ 'is-collapsed': sidebarCollapsed }">
        <div class="workspace-sidebar__brand">
            <a href="{{ route('today', $workspace->slug) }}" aria-label="خانه نئووا">
                <img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا">
            </a>
            @if ($board)
                <button type="button" @click="toggleSidebar()" aria-label="باز و بسته کردن نوار کناری">☰</button>
            @endif
        </div>

        <div class="workspace-switcher" @click.away="workspaceOpen = false">
            <button type="button" class="workspace-switcher__trigger" @click="workspaceOpen = !workspaceOpen" :aria-expanded="workspaceOpen">
                <span class="workspace-switcher__mark">{{ mb_substr($workspace->name, 0, 1) }}</span>
                <span class="workspace-switcher__copy"><strong>{{ $workspace->name }}</strong><small>تغییر فضای کاری</small></span>
                <span class="workspace-switcher__chevron">⌄</span>
            </button>
            <div x-show="workspaceOpen" x-cloak x-transition class="workspace-switcher__menu">
                <p>فضاهای کاری</p>
                @foreach ($shellWorkspaces as $shellWorkspace)
                    <a href="{{ route('today', $shellWorkspace->slug) }}" class="{{ $shellWorkspace->id === $workspace->id ? 'is-current' : '' }}">
                        <span>{{ mb_substr($shellWorkspace->name, 0, 1) }}</span><strong>{{ $shellWorkspace->name }}</strong>
                        @if ($shellWorkspace->id === $workspace->id)<i>✓</i>@endif
                    </a>
                @endforeach
                <button type="button" @click="workspaceCreating = true; workspaceOpen = false">+ فضای کاری جدید</button>
                @if ($canManageWorkspace)
                    <a href="{{ route('workspaces.settings', $workspace->slug) }}" class="workspace-switcher__manage">تنظیمات فضای کاری</a>
                @endif
            </div>
        </div>

        <nav class="workspace-nav" aria-label="ناوبری اصلی">
            <a href="{{ route('today', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'today' ? 'is-active' : '' }}"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg></span><b>امروز</b></a>
            <a href="{{ route('workspace.board', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'board' ? 'is-active' : '' }}"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16m6-16v16"/></svg></span><b>تخته</b></a>
            <a href="{{ route('projects.index', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'projects' ? 'is-active' : '' }}"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 6a2 2 0 0 1 2-2h4l2 2h6a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6Z"/></svg></span><b>پروژه‌ها</b></a>
            <a href="{{ route('team.index', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'team' ? 'is-active' : '' }}"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20m6-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-1a3 3 0 0 1 3 3v1"/></svg></span><b>تیم</b></a>
        </nav>

        <div class="workspace-sidebar__projects">
            <div><p>پروژه‌ها</p>@if ($canManageWorkspace)<a href="{{ route('projects.index', $workspace->slug) }}" aria-label="پروژه جدید">+</a>@endif</div>
            @foreach ($shellProjects as $shellProject)
                <a href="{{ route('board', [$workspace->slug, $shellProject->slug]) }}" class="{{ (string) $activeProject === (string) $shellProject->slug ? 'is-active' : '' }}">
                    <span>{{ mb_substr($shellProject->name, 0, 1) }}</span><b>{{ $shellProject->name }}</b>
                </a>
            @endforeach
            @if ($shellProjects->isEmpty())<small>هنوز پروژه‌ای ساخته نشده است.</small>@endif
        </div>

        <div class="workspace-sidebar__account">
            <a href="{{ route('profile') }}"><span>{{ auth()->user()->initials }}</span><b>{{ auth()->user()->full_name }}</b></a>
        </div>
    </aside>

    <div class="workspace-stage">
        <header class="workspace-topbar">
            <div class="workspace-mobile-brand">
                <img src="{{ asset('assets/logo/logo-black-transparent.png') }}" alt="نئووا">
                <button type="button" @click="mobileWorkspaceOpen = !mobileWorkspaceOpen"><strong>{{ $workspace->name }}</strong><span>⌄</span></button>
                <div x-show="mobileWorkspaceOpen" x-cloak @click.away="mobileWorkspaceOpen=false" class="workspace-mobile-switcher">
                    @foreach ($shellWorkspaces as $shellWorkspace)<a href="{{ route('today', $shellWorkspace->slug) }}">{{ $shellWorkspace->name }}</a>@endforeach
                    <button type="button" @click="workspaceCreating=true; mobileWorkspaceOpen=false">+ فضای کاری جدید</button>
                </div>
            </div>
            <button type="button" class="workspace-search-trigger" @click="searchOpen=true; $nextTick(() => $refs.searchInput.focus())" aria-label="جستجوی وظیفه یا پروژه"><span>⌕</span><b>جستجوی وظیفه یا پروژه…</b><kbd>/</kbd></button>
            <div class="workspace-topbar__actions">
                {{ $toolbar ?? '' }}
                <x-notification-menu />
                <div class="workspace-account-menu" @click.away="accountOpen=false">
                    <button type="button" class="workspace-profile-link" @click="accountOpen=!accountOpen" aria-label="حساب کاربری">
                        @if (auth()->user()->avatar)<img src="{{ asset('storage/avatars/'.auth()->user()->avatar) }}" alt="">@else<span>{{ auth()->user()->initials }}</span>@endif
                    </button>
                    <div x-show="accountOpen" x-cloak x-transition>
                        <p><strong>{{ auth()->user()->full_name }}</strong><small>{{ auth()->user()->phone }}</small></p>
                        <a href="{{ route('profile') }}">پروفایل و تنظیمات</a>
                        <a href="{{ route('notifications.index') }}">اعلان‌ها</a>
                        <form method="POST" action="{{ route('auth.logout') }}">@csrf<button>خروج</button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="workspace-main">{{ $slot }}</main>
    </div>

    <nav class="workspace-mobile-nav" aria-label="ناوبری موبایل">
        <a href="{{ route('today', $workspace->slug) }}" class="{{ $active === 'today' ? 'is-active' : '' }}"><span>○</span><b>امروز</b></a>
        <a href="{{ route('workspace.board', $workspace->slug) }}" class="{{ $active === 'board' ? 'is-active' : '' }}"><span>□</span><b>تخته</b></a>
        <a href="{{ route('projects.index', $workspace->slug) }}" class="{{ $active === 'projects' ? 'is-active' : '' }}"><span>◇</span><b>پروژه‌ها</b></a>
        <a href="{{ route('team.index', $workspace->slug) }}" class="{{ $active === 'team' ? 'is-active' : '' }}"><span>◌</span><b>تیم</b></a>
    </nav>

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
        sidebarCollapsed: config.board ? localStorage.getItem('neova_board_sidebar') !== 'expanded' : false,
        workspaceOpen: false, mobileWorkspaceOpen: false, workspaceCreating: false, accountOpen: false,
        searchOpen: false, searchQuery: '', searchResults: [], searchLoading: false,
        toggleSidebar() { this.sidebarCollapsed = !this.sidebarCollapsed; localStorage.setItem('neova_board_sidebar', this.sidebarCollapsed ? 'collapsed' : 'expanded'); },
        openSearch(event) { if (event.ctrlKey || event.metaKey || event.altKey || ['INPUT','TEXTAREA','SELECT'].includes(event.target.tagName) || event.target.isContentEditable) return; event.preventDefault(); this.searchOpen=true; this.$nextTick(() => this.$refs.searchInput.focus()); },
        async search() { if (!this.searchQuery.trim()) { this.searchResults=[]; return; } this.searchLoading=true; try { const response=await fetch(config.searchUrl+'?q='+encodeURIComponent(this.searchQuery), {headers:{Accept:'application/json'}}); this.searchResults=response.ok ? await response.json() : []; } finally { this.searchLoading=false; } }
    };
}
</script>
@endpush
@endonce
