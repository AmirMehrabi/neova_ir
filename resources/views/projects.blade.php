@extends('layouts.app')

@section('body')
<x-workspace-shell :workspace="$workspace" :projects="$projects" active="projects">
    <x-slot:context>پروژه‌ها</x-slot:context>
    <div class="projects-directory" x-data="projectsDirectory" data-create-errors="{{ $errors->has('name') || $errors->has('key') ? 'true' : 'false' }}" @hashchange.window="if (location.hash === '#project-create') openCreate()">
        <header class="projects-directory__header">
            <div><h1>پروژه‌ها</h1><p>{{ $projectCounts['active'] }} پروژه فعال در {{ $workspace->name }}</p></div>
            @if($canManage)<button class="projects-button projects-button--primary" type="button" x-ref="createButton" @click="openCreate()"><span aria-hidden="true">+</span> پروژه جدید</button>@endif
        </header>
        @if(session('status'))<p class="project-status" role="status">{{ session('status') }}</p>@endif
        @if($errors->any() && ! $errors->has('name') && ! $errors->has('key'))<div class="project-error" role="alert">{{ $errors->first() }}</div>@endif
        <nav class="projects-tabs" aria-label="وضعیت پروژه‌ها">
            @foreach(['active' => 'فعال', 'archived' => 'بایگانی‌شده'] as $tab => $label)
                <a class="{{ ($archived === ($tab === 'archived')) ? 'is-active' : '' }}" @if($archived === ($tab === 'archived')) aria-current="page" @endif href="{{ route('projects.index', [$workspace->slug, 'archived' => $tab === 'archived' ? 1 : null, 'q' => $search ?: null, 'sort' => $sort]) }}">{{ $label }} <span>{{ $projectCounts[$tab] }}</span></a>
            @endforeach
        </nav>
        <form class="projects-toolbar" method="GET" action="{{ route('projects.index', $workspace->slug) }}" role="search" aria-label="جست‌وجوی پروژه‌ها">
            @if($archived)<input type="hidden" name="archived" value="1">@endif
            <div class="projects-search"><label class="sr-only" for="projects-search">نام یا کلید پروژه</label><input id="projects-search" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="جست‌وجوی نام یا کلید پروژه…"><button type="submit" aria-label="جست‌وجو"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button></div>
            <label class="projects-sort" for="projects-sort">مرتب‌سازی<select id="projects-sort" name="sort" @change="$el.form.requestSubmit()"><option value="name" @selected($sort === 'name')>نام پروژه</option><option value="recent" @selected($sort === 'recent')>اخیراً بازشده</option></select></label>
            @if($search)<a class="projects-clear" href="{{ route('projects.index', [$workspace->slug, 'archived' => $archived ? 1 : null, 'sort' => $sort]) }}">پاک کردن جست‌وجو</a>@endif
        </form>
        @if($search)<p class="projects-result" role="status">{{ $projects->count() }} نتیجه برای «{{ $search }}»</p>@endif
        @if($projects->isNotEmpty())
            <div class="projects-table">
                <div class="projects-table__heading" aria-hidden="true"><span>پروژه</span><span>وظیفه‌های باز</span><span>در حال انجام</span><span></span></div>
                <ul class="projects-table__list" aria-label="{{ $archived ? 'پروژه‌های بایگانی‌شده' : 'پروژه‌های فعال' }}">
                @foreach($projects as $project)
                    <li class="projects-row">
                        <a class="projects-row__identity" href="{{ route('board', [$workspace->slug, $project->slug]) }}">
                            <span class="projects-row__badge" dir="auto">{{ $project->key ?: mb_substr($project->name, 0, 2) }}</span>
                            <span class="projects-row__copy"><strong>{{ $project->name }}</strong>@if($project->description)<small>{{ $project->description }}</small>@endif @if($archived)<small>بایگانی‌شده · فقط مشاهده</small>@endif</span>
                        </a>
                        <span class="projects-row__count"><b>{{ $project->open_tasks }}</b><small>وظیفه باز</small></span>
                        <span class="projects-row__count"><b>{{ $project->active_tasks }}</b><small>در حال انجام</small></span>
                        <div class="projects-row__actions">
                        @if($canManage && $archived)
                            <form method="POST" action="{{ route('board.project.archive', [$workspace->slug, $project->slug]) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="1"><button class="projects-restore" type="submit">بازگرداندن</button></form>
                        @elseif($canManage)
                            <details class="projects-menu" @click.outside="$el.removeAttribute('open')" @keydown.escape.stop.prevent="$el.removeAttribute('open'); $el.querySelector('summary').focus()" @toggle="if ($el.open) document.querySelectorAll('.projects-menu[open]').forEach(menu => { if (menu !== $el) menu.removeAttribute('open') })">
                                <summary aria-label="مدیریت پروژه {{ $project->name }}" title="مدیریت پروژه"><span aria-hidden="true">⋯</span></summary>
                                <div class="projects-menu__panel">
                                    <a href="{{ route('board', [$workspace->slug, $project->slug, 'settings' => 'general']) }}">تنظیمات پروژه</a>
                                    <a href="{{ route('board', [$workspace->slug, $project->slug, 'settings' => 'members']) }}">اعضای پروژه</a>
                                    <form method="POST" action="{{ route('board.project.archive', [$workspace->slug, $project->slug]) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="0"><button type="submit">بایگانی پروژه</button></form>
                                    <p>وظیفه‌ها و فایل‌ها حفظ می‌شوند.</p>
                                </div>
                            </details>
                        @else<span class="projects-row__arrow" aria-hidden="true">←</span>@endif
                        </div>
                    </li>
                @endforeach
                </ul>
            </div>
        @else
            <div class="projects-empty">
                <span class="projects-empty__icon" aria-hidden="true">{{ $search ? '⌕' : '▤' }}</span>
                <h2>{{ $search ? 'پروژه‌ای پیدا نشد' : ($archived ? 'بایگانی خالی است' : 'پروژه‌های شما از اینجا شروع می‌شوند') }}</h2>
                <p>{{ $search ? 'نام یا کلید دیگری را جست‌وجو کنید، یا جست‌وجو را پاک کنید.' : ($archived ? 'پروژه‌های بایگانی‌شده اینجا نگهداری می‌شوند و هر زمان قابل بازگرداندن‌اند.' : ($canManage ? 'یک پروژه بسازید و وظیفه‌های تیم را در تخته آن سامان دهید.' : 'از مدیر فضای کاری بخواهید شما را به یک پروژه اضافه کند.')) }}</p>
                @if(!$search && !$archived && $canManage)<button class="projects-button projects-button--primary" type="button" @click="openCreate()">ساخت اولین پروژه</button>@endif
            </div>
        @endif
        @if($canManage)
            <dialog class="projects-dialog" id="project-create" x-ref="createDialog" aria-labelledby="project-create-title" @close="restoreFocus()" @click="if ($event.target === $el) closeCreate()">
                <form method="POST" action="{{ route('dashboard.project.store', $workspace->slug) }}">
                    @csrf
                    <header><div><h2 id="project-create-title">پروژه جدید</h2><p>در {{ $workspace->name }}</p></div><button type="button" @click="closeCreate()" aria-label="بستن">×</button></header>
                    <label for="project-name">نام پروژه <input id="project-name" x-ref="projectName" name="name" value="{{ old('name') }}" required maxlength="100" placeholder="مثلاً طراحی محصول" @if($errors->has('name')) aria-invalid="true" aria-describedby="project-name-error" @endif></label>
                    @error('name')<p class="projects-field-error" id="project-name-error" role="alert">{{ $message }}</p>@enderror
                    <label for="project-key">کلید پروژه <span>(اختیاری)</span><input id="project-key" name="key" value="{{ old('key') }}" maxlength="10" pattern="[A-Z]+" dir="ltr" placeholder="TEAM" aria-describedby="project-key-help{{ $errors->has('key') ? ' project-key-error' : '' }}" @if($errors->has('key')) aria-invalid="true" @endif></label>
                    <p id="project-key-help" class="projects-field-help">حداکثر ۱۰ حرف بزرگ انگلیسی؛ برای شناسه وظیفه‌ها استفاده می‌شود.</p>
                    @error('key')<p class="projects-field-error" id="project-key-error" role="alert">{{ $message }}</p>@enderror
                    <footer><button class="projects-button" type="button" @click="closeCreate()">انصراف</button><button class="projects-button projects-button--primary" type="submit">ساخت پروژه</button></footer>
                </form>
            </dialog>
        @endif
    </div>
</x-workspace-shell>
@endsection
