@extends('layouts.app')
@section('body')
<div
    class="min-h-screen bg-[#F5F7FA]"
    x-data="{ showModal: false, modalType: 'workspace', targetWorkspace: null, userDropdown: false }"
>
    <style>
        [x-cloak] { display: none !important; }
        body.modal-open { overflow: hidden !important; position: fixed; width: 100%; }
        .project-card { transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease; }
        .project-card:hover { border-color: rgba(0, 105, 255, 0.28); box-shadow: 0 8px 24px rgba(3, 27, 78, 0.06); transform: translateY(-1px); }
        @media (prefers-reduced-motion: reduce) {
            .project-card { transition: none; }
        }
    </style>

    <x-navbar />

    {{-- Content --}}
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        @if (session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl px-4 py-3">{{ session('success') }}</div>
        @endif

        <div class="flex items-center justify-between gap-4 mb-7">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-[#172B4D]">داشبورد</h1>
                <p class="text-xs text-[#64748B] mt-1">{{ $workspaces->sum('projects_count') }} پروژه در {{ $workspaces->count() }} فضای کاری</p>
            </div>
            <button
                @click="modalType = 'workspace'; showModal = true"
                class="flex items-center gap-2 bg-[#0069FF] hover:bg-[#0057D9] text-white text-[12px] font-bold px-4 py-2.5 rounded-lg transition-all active:scale-[0.98] shrink-0"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">فضای کاری جدید</span>
            </button>
        </div>

        @forelse ($workspaces as $workspace)
            @php
                $role = $workspace->getAttribute('user_role');
                $canManage = in_array($role, ['owner', 'admin'], true);
            @endphp
            <section class="mb-8 last:mb-0">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-[#031B4E] text-white flex items-center justify-center text-xs font-bold shrink-0">{{ mb_substr($workspace->name, 0, 1) }}</div>
                        <div class="min-w-0">
                            <h2 class="text-[15px] font-bold text-[#172B4D] truncate">{{ $workspace->name }}</h2>
                            <p class="text-[10px] text-[#94A3B8]">{{ $workspace->projects_count }} پروژه · {{ $workspace->members_count ?? 0 }} عضو</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($canManage)
                            <a href="{{ route('workspaces.settings', $workspace->slug) }}" class="text-[11px] font-semibold text-[#64748B] border border-[#DCE3ED] bg-white rounded-lg px-3 py-2 hover:bg-[#F8FAFC] transition-colors">مدیریت</a>
                            <button
                                @click="targetWorkspace = '{{ $workspace->slug }}'; modalType = 'project'; showModal = true"
                                class="flex items-center gap-1.5 bg-[#0069FF] hover:bg-[#0057D9] text-white text-[11px] font-bold px-3 py-2 rounded-lg transition-all active:scale-[0.98]"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                پروژه
                            </button>
                        @endif
                    </div>
                </div>

                @if ($workspace->projects->count())
                    <div class="space-y-2.5">
                        @foreach ($workspace->projects as $project)
                            <a
                                href="{{ route('board', [$workspace->slug, $project->slug]) }}"
                                class="project-card flex items-center gap-3 sm:gap-5 bg-white rounded-xl border border-[#DFE5EF] px-4 py-4 sm:px-5"
                            >
                                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-lg bg-[#E8F0FE] flex items-center justify-center shrink-0">
                                    <span class="text-[#0069FF] font-black text-[11px] sm:text-xs">{{ $project->key ?: mb_substr($project->name, 0, 2) }}</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[14px] sm:text-[15px] font-bold text-[#172B4D] truncate">{{ $project->name }}</h3>
                                    @if ($project->description)
                                        <p class="hidden sm:block text-[12px] text-[#64748B] truncate mt-1">{{ $project->description }}</p>
                                    @else
                                        <p class="hidden sm:block text-[12px] text-[#94A3B8] mt-1">تخته وظایف و روند اجرای پروژه</p>
                                    @endif
                                </div>

                                <div class="hidden md:block w-52 shrink-0">
                                    <div class="flex items-center justify-between gap-3 mb-2">
                                        @if ($project->total_tasks > 0)
                                            <span class="text-[11px] text-[#64748B]">
                                                {{ $project->done_tasks }} از {{ $project->total_tasks }} وظیفه انجام شده
                                            </span>
                                            <span class="text-[10px] font-bold text-[#0069FF]">{{ $project->progress_percentage }}٪</span>
                                        @else
                                            <span class="text-[11px] text-[#94A3B8]">هنوز وظیفه‌ای ثبت نشده</span>
                                        @endif
                                    </div>
                                    <div class="h-1.5 bg-[#E9EEF5] rounded-full overflow-hidden">
                                        <div class="h-full bg-[#0069FF] rounded-full" style="width: {{ $project->progress_percentage }}%"></div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0 text-[#0069FF]">
                                    <span class="hidden lg:inline text-[12px] font-bold">ورود به تخته</span>
                                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7"/></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white border border-dashed border-[#DFE5EF] rounded-xl flex flex-col items-center justify-center py-10 px-5 text-center">
                        <p class="text-[13px] text-[#94A3B8] mb-3">هنوز پروژه‌ای ساخته نشده</p>
                        @if ($canManage)
                            <button
                                @click="targetWorkspace = '{{ $workspace->slug }}'; modalType = 'project'; showModal = true"
                                class="text-[11px] font-bold text-[#0069FF] bg-[#E8F0FE] hover:bg-[#D6E4FD] px-3 py-1.5 rounded-lg transition-colors"
                            >ایجاد پروژه</button>
                        @endif
                    </div>
                @endif
            </section>
        @empty
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-14 h-14 rounded-2xl bg-[#F1F5F9] flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <p class="text-[15px] font-bold text-[#172B4D] mb-1">فضای کاری ندارید</p>
                <p class="text-xs text-[#64748B] mb-5">اولین فضای کاری خود را بسازید.</p>
                <button @click="modalType = 'workspace'; showModal = true" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0057D9] px-4 py-2.5 rounded-lg transition-colors">ایجاد فضای کاری</button>
            </div>
        @endforelse
    </main>

    {{-- Modals --}}
    <div x-show="showModal" x-cloak x-effect="showModal ? document.body.classList.add('modal-open') : document.body.classList.remove('modal-open')">
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 bg-[#0A1628]/60 backdrop-blur-sm" @click="showModal = false"></div>
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showModal = false">
            <div class="min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white w-full max-w-sm rounded-2xl border border-[#E2E8F0] p-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" @click.stop>
                    <template x-if="modalType === 'workspace'">
                        <div>
                            <h3 class="text-sm font-bold text-[#1A1D21] mb-4">فضای کاری جدید</h3>
                            <form action="{{ route('dashboard.workspace.store') }}" method="POST">
                                @csrf
                                <input name="name" type="text" required class="w-full text-sm font-semibold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors mb-4 placeholder:text-[#CBD5E1]" placeholder="نام فضای کاری">
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#4D99FF] px-4 py-2 rounded-xl transition-all">ایجاد</button>
                                </div>
                            </form>
                        </div>
                    </template>
                    <template x-if="modalType === 'project'">
                        <div>
                            <h3 class="text-sm font-bold text-[#1A1D21] mb-4">پروژه جدید</h3>
                            <form :action="'{{ route('dashboard.project.store', ':slug') }}'.replace(':slug', targetWorkspace)" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">نام پروژه</label>
                                    <input name="name" type="text" required class="w-full text-sm font-semibold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors placeholder:text-[#CBD5E1]" placeholder="نام پروژه">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">کلید پروژه <span class="text-[#CBD5E1]">(اختیاری)</span></label>
                                    <input name="key" type="text" maxlength="10" class="w-full text-sm font-semibold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors uppercase placeholder:text-[#CBD5E1]" placeholder="مثلاً SCR">
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#4D99FF] px-4 py-2 rounded-xl transition-all">ایجاد</button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
