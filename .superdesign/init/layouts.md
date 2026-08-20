# Shared layouts

## `resources/views/layouts/app.blade.php`

Root RTL document wrapper used by most pages.

```blade
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'تخته اسکرام' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo-black-transparent.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="app-page bg-[#FDFDFC] min-h-screen">
    @yield('body')
    @stack('scripts')
</body>
</html>
```

## `resources/views/components/workspace-shell.blade.php`

Primary authenticated RTL shell. Props: `workspace`, `projects`, `active`, `board`, `activeProject`. It renders a 236px sticky right sidebar (64px collapsed on project boards), brand logo, workspace switcher, navigation for Today/Board/Projects/Team, project shortcuts, account identity, a sticky 57px topbar, global search command palette, notification/profile controls, and a four-item mobile bottom bar. Desktop content occupies `.workspace-stage > .workspace-main`. Mobile removes the sidebar and uses the topbar plus fixed bottom navigation.

Key source structure:

```blade
<div class="workspace-shell {{ $board ? 'workspace-shell--board' : '' }} min-h-screen bg-[#FBFDFF]" x-data="workspaceShell(...)" :class="{ 'workspace-shell--collapsed': sidebarCollapsed }">
  <aside class="workspace-sidebar" :class="{ 'is-collapsed': sidebarCollapsed }">
    <div class="workspace-sidebar__brand"><a href="{{ route('today', $workspace->slug) }}"><img src="{{ asset('assets/logo/horizental-logo-black-transparent.png') }}" alt="نئووا"></a></div>
    <div class="workspace-switcher">…workspace trigger and menu…</div>
    <nav class="workspace-nav">
      <a class="workspace-nav__item {{ $active === 'today' ? 'is-active' : '' }}">☀ <b>امروز</b></a>
      <a class="workspace-nav__item {{ $active === 'board' ? 'is-active' : '' }}">▥ <b>تخته</b></a>
      <a class="workspace-nav__item {{ $active === 'projects' ? 'is-active' : '' }}">▤ <b>پروژه‌ها</b></a>
      <a class="workspace-nav__item {{ $active === 'team' ? 'is-active' : '' }}">♙ <b>تیم</b></a>
    </nav>
    <div class="workspace-sidebar__projects">…project shortcuts…</div>
    <div class="workspace-sidebar__account">…user identity…</div>
  </aside>
  <div class="workspace-stage">
    <header class="workspace-topbar"><div class="workspace-mobile-brand">…</div><div class="workspace-topbar__context">{{ $context ?? '' }}</div><div class="workspace-topbar__actions">{{ $toolbar ?? '' }} …search, notifications, account…</div></header>
    <main class="workspace-main">{{ $slot }}</main>
  </div>
  <nav class="workspace-mobile-nav">…Today, Board, Projects, Team…</nav>
  <div class="workspace-command">…global search dialog…</div>
  <div class="workspace-command">…create workspace dialog…</div>
</div>
```

The full implementation is 146 lines at `resources/views/components/workspace-shell.blade.php`; its visual selectors are in `resources/css/today.css` and its global tokens are in `resources/css/app.css`.

## `resources/views/components/navbar.blade.php`

Secondary sticky navbar used for standalone and embedded board contexts. Supports dark/light, fluid width, board shell, embedded rendering, search/action/mobile slots, exact Neova logo assets, notification bell, and user dropdown.
