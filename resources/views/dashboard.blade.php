@extends('layouts.app')
@section('body')
<div
    class="min-h-screen bg-[#F5F7FA] flex"
    x-data="{
        sidebarOpen: false,
        showModal: false,
        modalType: 'workspace',
        activeWs: '{{ $activeWorkspace?->slug ?? '' }}'
    }"
>
    <style>
        body.modal-open { overflow: hidden !important; position: fixed; width: 100%; }
        .project-card { transition: all 0.15s ease; }
        .project-card:hover { transform: translateY(-1px); border-color: #0069FF30; }
        .ws-item { transition: all 0.15s ease; }
        .ws-item.active { background: rgba(255,255,255,0.08); }
        .ws-item:hover { background: rgba(255,255,255,0.05); }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
    </style>

    {{-- Sidebar --}}
    <aside
        class="fixed md:sticky top-0 right-0 h-screen w-[260px] bg-[#003078] flex flex-col z-40 transition-transform duration-200 shrink-0"
        :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'"
    >
        <div class="px-5 py-5 flex items-center gap-3 border-b border-white/10">
            <div class="w-9 h-9 rounded-xl bg-[#0069FF] flex items-center justify-center">
                <span class="text-white font-black text-base">N</span>
            </div>
            <div>
                <span class="text-white font-bold text-sm block leading-tight">Neova</span>
                <span class="text-blue-300/60 text-[10px]">نئووا</span>
            </div>
            <button @click="sidebarOpen = false" class="mr-auto md:hidden text-white/50 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto py-4 px-3">
            <p class="text-[9px] font-bold text-blue-300/40 uppercase tracking-[0.15em] px-3 mb-2">فضاهای کاری</p>
            @forelse ($workspaces as $ws)
                <a
                    href="{{ route('dashboard', ['workspace' => $ws->slug]) }}"
                    class="ws-item flex items-center gap-3 px-3 py-2.5 rounded-xl mb-0.5 {{ $activeWorkspace?->slug === $ws->slug ? 'active' : '' }}"
                >
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $activeWorkspace?->slug === $ws->slug ? 'bg-[#0069FF]' : 'bg-white/10' }}">
                        <span class="text-[10px] font-bold {{ $activeWorkspace?->slug === $ws->slug ? 'text-white' : 'text-blue-200/70' }}">{{ substr($ws->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-white/90 truncate">{{ $ws->name }}</p>
                        <p class="text-[10px] text-blue-300/40">{{ $ws->projects_count }} پروژه</p>
                    </div>
                </a>
            @empty
                <p class="text-[11px] text-blue-300/30 px-3">فضای کاری‌ای وجود ندارد</p>
            @endforelse
        </div>

        <div class="p-3 border-t border-white/10">
            <button
                @click="modalType = 'workspace'; showModal = true"
                class="w-full flex items-center justify-center gap-2 text-[11px] font-semibold text-blue-300/60 hover:text-white hover:bg-white/5 px-3 py-2.5 rounded-xl transition-all"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                فضای کاری جدید
            </button>
        </div>
    </aside>

    {{-- Sidebar backdrop (mobile) --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/30 z-30 md:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen min-w-0">
        {{-- Header --}}
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-[#E2E8F0]">
            <div class="px-6 h-14 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-[#64748B] hover:text-[#1A1D21]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    @if ($activeWorkspace)
                        <div>
                            <h1 class="text-[15px] font-bold text-[#1A1D21]">{{ $activeWorkspace->name }}</h1>
                            <p class="text-[10px] text-[#94A3B8] -mt-0.5">{{ $projects->count() }} پروژه</p>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    @if ($activeWorkspace)
                        <button
                            @click="modalType = 'project'; showModal = true"
                            class="flex items-center gap-1.5 bg-[#0069FF] hover:bg-[#4D99FF] text-white text-[11px] font-bold px-3.5 py-1.5 rounded-lg transition-all active:scale-[0.97]"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            پروژه جدید
                        </button>
                    @endif
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#003B8E] to-[#0069FF] flex items-center justify-center">
                            <span class="text-[10px] text-white font-bold">{{ substr(auth()->user()->first_name ?? auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <span class="text-[11px] font-semibold text-[#475569] hidden sm:block">{{ auth()->user()->full_name }}</span>
                        <form action="{{ route('auth.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-[10px] font-medium text-[#94A3B8] hover:text-[#1A1D21] transition-colors mr-1">خروج</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-6">
            @if ($activeWorkspace)
                @if ($projects->count())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($projects as $project)
                            <a
                                href="{{ route('board', [$activeWorkspace->slug, $project->slug]) }}"
                                class="project-card block bg-white rounded-xl border border-[#E2E8F0] p-5 relative group"
                            >
                                <div class="flex items-center justify-between mb-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#0069FF]/10 flex items-center justify-center group-hover:bg-[#0069FF] transition-colors">
                                        <span class="text-[#0069FF] font-black text-xs group-hover:text-white transition-colors">{{ $project->key }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        @foreach ($project->columns->take(4) as $col)
                                            <span class="w-2 h-2 rounded-full opacity-60" style="background: {{ $col->color ?? '#94A3B8' }}" title="{{ $col->title }}: {{ $col->tasks_count ?? $col->tasks->count() }}"></span>
                                        @endforeach
                                    </div>
                                </div>

                                <h3 class="text-[14px] font-bold text-[#1A1D21] mb-1 leading-relaxed">{{ $project->name }}</h3>

                                @if ($project->description)
                                    <p class="text-[11px] text-[#94A3B8] line-clamp-2 leading-relaxed">{{ $project->description }}</p>
                                @endif

                                <div class="flex items-center justify-between mt-4 pt-3 border-t border-[#F1F5F9]">
                                    <div class="flex items-center gap-3">
                                        @foreach ($project->columns->take(4) as $col)
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-[#94A3B8]">{{ $col->tasks_count ?? $col->tasks->count() }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <svg class="w-4 h-4 text-[#CBD5E1] group-hover:text-[#0069FF] transition-colors -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-[#E8F0FE] flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-[#0069FF]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-sm font-bold text-[#1A1D21] mb-1">پروژه‌ای وجود ندارد</p>
                        <p class="text-[11px] text-[#94A3B8] mb-4">اولین پروژه خود را بسازید</p>
                        <button
                            @click="modalType = 'project'; showModal = true"
                            class="text-[11px] font-semibold text-[#0069FF] hover:text-[#4D99FF] transition-colors"
                        >+ پروژه جدید</button>
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-[#F1F5F9] flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-[#94A3B8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <p class="text-sm font-bold text-[#1A1D21] mb-1">فضای کاری انتخاب کنید</p>
                    <p class="text-[11px] text-[#94A3B8]">از منوی کناری یک فضای کاری انتخاب کنید</p>
                </div>
            @endif
        </main>
    </div>

    {{-- Modals --}}
    <div x-show="showModal" x-cloak x-effect="showModal ? document.body.classList.add('modal-open') : document.body.classList.remove('modal-open')">
        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-[#0A1628]/60 backdrop-blur-sm"
            @click="showModal = false"
        ></div>

        <div
            x-show="showModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="showModal = false"
        >
            <div class="min-h-screen flex items-center justify-center p-4">
                <div
                    class="relative bg-white w-full max-w-sm rounded-2xl border border-[#E2E8F0] p-6"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    @click.stop
                >
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
                            <form action="{{ route('dashboard.project.store', $activeWorkspace?->slug) }}" method="POST">
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
