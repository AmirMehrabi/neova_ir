@extends('layouts.app')

@section('body')
<x-workspace-shell :workspace="$workspace" :projects="$projects" active="projects">
    <x-slot:context>پروژه‌ها</x-slot:context>
    <div class="today-page projects-page">
        <header class="today-header">
            <div><p class="today-eyebrow">همه کارها در جای خودشان</p><h1>پروژه‌ها</h1><p>{{ $projects->count() }} پروژه در {{ $workspace->name }}</p></div>
        </header>
        <div class="project-list">
            @forelse ($projects as $project)
                <a href="{{ route('board', [$workspace->slug, $project->slug]) }}" class="project-list__row">
                    <span class="project-list__mark">{{ $project->key ?: mb_substr($project->name, 0, 2) }}</span>
                    <span><strong>{{ $project->name }}</strong><small>{{ $project->description ?: 'تخته و وظیفه‌های پروژه' }}</small></span>
                    <span class="project-list__counts">{{ $project->active_tasks }} در حال انجام · {{ $project->open_tasks }} باز</span>
                    <span>←</span>
                </a>
            @empty
                <p class="today-empty">هنوز پروژه‌ای در این فضای کاری وجود ندارد.</p>
            @endforelse
        </div>
        @if ($canManage)
            <form class="project-create-inline" method="POST" action="{{ route('dashboard.project.store', $workspace->slug) }}">
                @csrf
                <input name="name" required maxlength="100" placeholder="نام پروژه جدید">
                <input name="key" maxlength="10" pattern="[A-Z]+" dir="ltr" placeholder="KEY">
                <button type="submit">+ پروژه جدید</button>
            </form>
        @endif
    </div>
</x-workspace-shell>
@endsection
