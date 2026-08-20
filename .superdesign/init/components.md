# Shared UI components

Neova is a Laravel 12 application whose reusable UI is implemented as Blade components and styled with Tailwind 4 plus custom CSS.

## `resources/views/components/breadcrumb.blade.php`

Reusable RTL breadcrumb. Props: `items`.

```blade
@props(['items' => []])

@if ($items->isNotEmpty())
    <nav class="flex items-center gap-1.5 min-w-0 overflow-x-auto text-nowrap pb-1 -mb-1" aria-label="breadcrumb">
        @foreach ($items as $i => $item)
            @if ($i > 0)
                <svg class="w-3 h-3 text-[#A49B90] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ app()->isLocale('fa') ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}"/></svg>
            @endif
            @if ($item['url'] ?? null)
                <a href="{{ $item['url'] }}" class="text-[11px] font-medium text-[#8A8175] hover:text-[#18212B] transition-colors truncate">{{ $item['label'] }}</a>
            @else
                <span class="text-[11px] font-bold text-[#18212B] truncate">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
```

## `resources/views/components/app-page-header.blade.php`

Standalone dark page header. Props: `title`, `backUrl`.

```blade
@props(['title', 'backUrl' => null])
<header class="sticky top-0 z-30 bg-[#071B33]/96 backdrop-blur-xl border-b border-white/10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center gap-3">
        @if ($backUrl)<a href="{{ $backUrl }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-white/70 hover:text-white hover:bg-white/10">←</a>@endif
        <a href="{{ route('dashboard') }}"><img src="{{ asset('assets/logo/horizental-logo-white-transparent.png') }}" alt="نئووا" class="h-7 w-auto object-contain"></a>
        <span class="h-5 w-px bg-white/15"></span><h1 class="text-sm font-bold text-white truncate">{{ $title }}</h1>
        <div class="mr-auto flex items-center gap-2"><x-notification-menu /><div class="w-8 h-8 rounded-full bg-[#031B4E] text-white flex items-center justify-center text-[10px] font-bold">{{ mb_substr(auth()->user()->full_name, 0, 1) }}</div></div>
    </div>
</header>
```

## Other reusable components

- `resources/views/components/navbar.blade.php` — 167-line sticky global navbar with logo, search slot, notification menu, profile menu, and responsive mobile slot.
- `resources/views/components/notification-menu.blade.php` — reusable notification bell/dropdown with unread state.
- `resources/views/components/workspace-shell.blade.php` — primary authenticated shell; full source is recorded in `layouts.md`.
