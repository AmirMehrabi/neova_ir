@extends('layouts.app')

@section('body')
<x-workspace-shell :workspace="$workspace" :projects="$projects" active="team">
    <x-slot:context>امروز تیم</x-slot:context>
    <div class="today-page team-page">
        <header class="today-header"><div><p class="today-eyebrow">وضعیت واقعی کارها</p><h1>امروز تیم</h1><p>{{ $workspace->name }}</p></div></header>
        <div class="team-list">
            @foreach ($team as $member)
                <section class="team-member">
                    <header><span>{{ $member['initials'] }}</span><div><h2>{{ $member['name'] }}</h2><p>{{ $member['active']->count() }} فعال · {{ $member['done']->count() }} انجام‌شده</p></div></header>
                    <div class="team-member__tasks">
                        @foreach ($member['done'] as $task)<p class="is-done"><i>✓</i><span>{{ $task['title'] }}<small>{{ $task['project'] }}</small></span></p>@endforeach
                        @foreach ($member['active'] as $task)<p><i>●</i><span>{{ $task['title'] }}<small>{{ $task['project'] }}</small></span></p>@endforeach
                        @foreach ($member['blocked'] as $task)<p class="is-blocked"><i>!</i><span>{{ $task['title'] }}<small>{{ $task['reason'] ?: 'مسدود' }}</small></span></p>@endforeach
                        @if ($member['done']->isEmpty() && $member['active']->isEmpty() && $member['blocked']->isEmpty())<p class="team-member__empty">کاری برای امروز ثبت نشده است.</p>@endif
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-workspace-shell>
@endsection
