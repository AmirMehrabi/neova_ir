@extends('layouts.app')

@section('body')
<x-workspace-shell :workspace="$workspace" :projects="$projects" active="projects">
    <x-slot:context>پروژه‌ها</x-slot:context>
    <div class="today-page projects-page">
        <header class="today-header">
            <div><p class="today-eyebrow">همه کارها در جای خودشان</p><h1>پروژه‌ها</h1><p>{{ $projects->count() }} پروژه در {{ $workspace->name }}</p></div>
            <a class="project-settings-link" href="{{ route('projects.index', [$workspace->slug, 'archived' => ! $archived]) }}">{{ $archived ? 'پروژه‌های فعال' : 'بایگانی پروژه‌ها' }}</a>
        </header>
        @if(session('status'))<p class="project-status" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<div class="project-error" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="project-list">
            @forelse ($projects as $project)
                <div class="project-list__row">
                    <span class="project-list__mark">{{ $project->key ?: mb_substr($project->name, 0, 2) }}</span>
                    <a href="{{ route('board', [$workspace->slug, $project->slug]) }}"><strong>{{ $project->name }}</strong><small>{{ $project->description ?: 'تخته و وظیفه‌های پروژه' }}</small>@if($archived)<small>بایگانی شده · فقط مشاهده</small>@endif</a>
                    <span class="project-list__counts">{{ $project->active_tasks }} در حال انجام · {{ $project->open_tasks }} باز</span>
                    @if($canManage)
                        <details class="project-actions">
                            <summary aria-label="مدیریت پروژه {{ $project->name }}">مدیریت پروژه</summary>
                            <div class="project-actions__menu">
                                <a href="{{ route('board', [$workspace->slug, $project->slug, 'settings' => 'general']) }}">تنظیمات پروژه</a>
                                <a href="{{ route('board', [$workspace->slug, $project->slug, 'settings' => 'members']) }}">اعضای پروژه</a>
                                <form method="POST" action="{{ route('board.project.archive', [$workspace->slug, $project->slug]) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $archived ? 1 : 0 }}"><button type="submit">{{ $archived ? 'بازگرداندن پروژه' : 'بایگانی پروژه (قابل بازگشت)' }}</button></form>
                                <a class="project-actions__danger" href="{{ route('board', [$workspace->slug, $project->slug, 'settings' => 'delete']) }}">حذف دائمی…</a>
                            </div>
                        </details>
                    @else
                        <a class="project-settings-link" href="{{ route('board', [$workspace->slug, $project->slug]) }}">باز کردن تخته ←</a>
                    @endif
                </div>
            @empty
                <p class="today-empty">{{ $archived ? 'پروژه بایگانی‌شده‌ای وجود ندارد.' : ($canManage ? 'اولین پروژه را با فرم زیر بسازید.' : 'هنوز پروژه‌ای در دسترس شما نیست. از مدیر فضای کاری بخواهید شما را به پروژه اضافه کند.') }}</p>
            @endforelse
        </div>
        @if ($canManage && ! $archived)
            <form class="project-create-inline" method="POST" action="{{ route('dashboard.project.store', $workspace->slug) }}">
                @csrf
                <label for="project-name">نام پروژه<input id="project-name" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="نام پروژه جدید"></label>
                <label for="project-key">کلید (اختیاری)<input id="project-key" name="key" value="{{ old('key') }}" maxlength="10" pattern="[A-Z]+" dir="ltr" placeholder="TEAM"><small>حروف بزرگ انگلیسی</small></label>
                <button type="submit">+ پروژه جدید</button>
            </form>
        @endif
    </div>
</x-workspace-shell>
@endsection
