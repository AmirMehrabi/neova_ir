@props(['workspace', 'projects' => collect(), 'active' => 'today'])

<div class="workspace-shell min-h-screen bg-[#FBFDFF]">
    <x-navbar light fluid>
        <div class="hidden sm:flex items-center gap-2 text-[11px] text-[#64788A]">
            <span class="text-[#DCE8F2]">/</span>
            <span class="font-bold text-[#102A43]">{{ $workspace->name }}</span>
        </div>
    </x-navbar>

    <div class="workspace-shell__body">
        <aside class="workspace-sidebar">
            <nav class="workspace-nav" aria-label="ناوبری اصلی">
                <a href="{{ route('today', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'today' ? 'is-active' : '' }}">امروز</a>
                <a href="{{ route('workspace.board', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'board' ? 'is-active' : '' }}">تخته</a>
                <a href="{{ route('projects.index', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'projects' ? 'is-active' : '' }}">پروژه‌ها</a>
                <a href="{{ route('team.index', $workspace->slug) }}" class="workspace-nav__item {{ $active === 'team' ? 'is-active' : '' }}">تیم</a>
            </nav>

            <div class="workspace-sidebar__projects">
                <p>پروژه‌ها</p>
                @foreach ($projects as $project)
                    <a href="{{ route('board', [$workspace->slug, $project->slug]) }}">
                        <span>{{ mb_substr($project->name, 0, 1) }}</span>
                        {{ $project->name }}
                    </a>
                @endforeach
            </div>
        </aside>

        <main class="workspace-main">
            {{ $slot }}
        </main>
    </div>
</div>
