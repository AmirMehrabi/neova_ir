@extends('layouts.app')
@section('body')
<div
    class="min-h-screen bg-[#F0F4F8] flex"
    x-data="{
        sidebarOpen: false,
        showModal: false,
        modalType: 'workspace',
        activeWs: '{{ $activeWorkspace?->slug ?? '' }}'
    }"
>
    <style>
        body.modal-open { overflow: hidden !important; position: fixed; width: 100%; }
    </style>

    {{-- Sidebar --}}
    <aside
        class="fixed md:sticky top-0 right-0 h-screen w-64 bg-white border-l border-[#E2E8F0] flex flex-col z-40 transition-transform duration-200"
        :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'"
    >
        {{-- Logo --}}
        <div class="px-5 py-4 border-b border-[#F1F5F9] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#003B8E] to-[#0069FF] flex items-center justify-center shadow-md shadow-[#0069FF]/20">
                    <span class="text-white font-black text-sm">N</span>
                </div>
                <div>
                    <span class="text-[#1A1D21] font-black text-sm">Neova</span>
                    <span class="text-[#94A3B8] text-[10px] block -mt-0.5">نئووا</span>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="md:hidden text-[#94A3B8] hover:text-[#1A1D21]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Workspace List --}}
        <div class="flex-1 overflow-y-auto py-3 px-3">
            <p class="text-[9px] font-bold text-[#94A3B8] uppercase tracking-widest px-2 mb-2">فضاهای کاری</p>
            @forelse ($workspaces as $ws)
                <a
                    href="{{ route('dashboard', ['workspace' => $ws->slug]) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl mb-1 transition-all group"
                    :class="activeWs === '{{ $ws->slug }}' ? 'bg-[#E8F0FE] text-[#003B8E]' : 'text-[#475569] hover:bg-[#F8FAFC]'"
                >
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors" :class="activeWs === '{{ $ws->slug }}' ? 'bg-[#0069FF]' : 'bg-[#F1F5F9] group-hover:bg-[#E8F0FE]'">
                        <span class="text-[9px] font-bold" :class="activeWs === '{{ $ws->slug }}' ? 'text-white' : 'text-[#64748B]'">{{ substr($ws->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold truncate" x-text="'{{ $ws->name }}'"></p>
                        <p class="text-[9px] text-[#94A3B8]">{{ $ws->projects_count }} پروژه</p>
                    </div>
                </a>
            @empty
                <p class="text-[11px] text-[#94A3B8] px-2">فضای کاری‌ای وجود ندارد</p>
            @endforelse
        </div>

        {{-- New Workspace --}}
        <div class="p-3 border-t border-[#F1F5F9]">
            <button
                @click="modalType = 'workspace'; showModal = true"
                class="w-full flex items-center justify-center gap-1.5 text-[11px] font-bold text-[#0069FF] hover:bg-[#E8F0FE] px-3 py-2 rounded-xl transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
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
    <div class="flex-1 flex flex-col min-h-screen">
        {{-- Header --}}
        <header class="bg-white border-b border-[#E2E8F0] sticky top-0 z-30">
            <div class="px-5 h-14 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="md:hidden text-[#64748B] hover:text-[#1A1D21]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div x-show="activeWs">
                        <h1 class="text-sm font-black text-[#1A1D21]">{{ $activeWorkspace?->name }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#003B8E] to-[#0069FF] flex items-center justify-center">
                            <span class="text-[9px] text-white font-bold">{{ substr(auth()->user()->first_name ?? auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <span class="text-xs font-medium text-[#475569] hidden sm:block">{{ auth()->user()->full_name }}</span>
                    </div>
                    <form action="{{ route('auth.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-[11px] font-semibold text-[#94A3B8] hover:text-[#1A1D21] transition-colors">خروج</button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-5">
            @if ($activeWorkspace)
                {{-- Stats Row --}}
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="bg-white rounded-xl border border-[#E2E8F0] px-4 py-3">
                        <p class="text-[10px] font-bold text-[#94A3B8] uppercase tracking-widest">پروژه‌ها</p>
                        <p class="text-xl font-black text-[#1A1D21] mt-0.5">{{ $stats['total_projects'] }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-[#E2E8F0] px-4 py-3">
                        <p class="text-[10px] font-bold text-[#94A3B8] uppercase tracking-widest">همه وظایف</p>
                        <p class="text-xl font-black text-[#1A1D21] mt-0.5">{{ $stats['total_tasks'] }}</p>
                    </div>
                    <div class="bg-white rounded-xl border border-[#E2E8F0] px-4 py-3">
                        <p class="text-[10px] font-bold text-[#94A3B8] uppercase tracking-widest">انجام شده</p>
                        <p class="text-xl font-black text-[#22C55E] mt-0.5">{{ $stats['done_tasks'] }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-[#1A1D21]">پروژه‌ها</h2>
                    <button
                        @click="modalType = 'project'; showModal = true"
                        class="flex items-center gap-1.5 bg-[#0069FF] hover:bg-[#4D99FF] text-white text-[11px] font-bold px-3 py-1.5 rounded-lg transition-all shadow-md shadow-[#0069FF]/20 active:scale-[0.97]"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        پروژه جدید
                    </button>
                </div>

                {{-- Projects Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse ($projects as $project)
                        <a
                            href="{{ route('board', [$activeWorkspace->slug, $project->slug]) }}"
                            class="block bg-white rounded-xl border border-[#E2E8F0] p-4 hover:border-[#0069FF]/30 hover:shadow-md hover:shadow-[#0069FF]/5 transition-all group"
                        >
                            <div class="flex items-start justify-between mb-2.5">
                                <div class="w-9 h-9 rounded-lg bg-[#E8F0FE] flex items-center justify-center">
                                    <span class="text-[#0069FF] font-bold text-[11px]">{{ $project->key }}</span>
                                </div>
                                <span class="text-[9px] font-bold text-[#94A3B8] bg-[#F1F5F9] px-1.5 py-0.5 rounded-md">{{ $project->columns_count }} ستون</span>
                            </div>
                            <h3 class="text-[13px] font-bold text-[#1A1D21] group-hover:text-[#0069FF] transition-colors">{{ $project->name }}</h3>
                            @if ($project->description)
                                <p class="text-[11px] text-[#64748B] mt-1 line-clamp-2">{{ $project->description }}</p>
                            @endif
                            <div class="flex items-center gap-2 mt-2.5 pt-2.5 border-t border-[#F1F5F9]">
                                @foreach ($project->columns->take(4) as $col)
                                    <div class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $col->color ?? '#94A3B8' }}"></span>
                                        <span class="text-[9px] text-[#94A3B8]">{{ $col->tasks_count ?? $col->tasks->count() }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-12 h-12 rounded-xl bg-[#E8F0FE] flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-[#0069FF]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <p class="text-xs font-bold text-[#1A1D21] mb-0.5">هنوز پروژه‌ای ندارید</p>
                            <p class="text-[10px] text-[#94A3B8]">اولین پروژه خود را بسازید</p>
                        </div>
                    @endforelse
                </div>
            @else
                {{-- No workspace selected --}}
                <div class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-14 h-14 rounded-xl bg-[#E8F0FE] flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-[#0069FF]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
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
                    class="relative bg-white w-full max-w-sm rounded-2xl shadow-2xl shadow-black/25 p-6"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    @click.stop
                >
                    {{-- Workspace Modal --}}
                    <template x-if="modalType === 'workspace'">
                        <div>
                            <h3 class="text-sm font-bold text-[#1A1D21] mb-4">فضای کاری جدید</h3>
                            <form action="{{ route('dashboard.workspace.store') }}" method="POST">
                                @csrf
                                <input name="name" type="text" required class="w-full text-sm font-bold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors mb-4" placeholder="نام فضای کاری">
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0055CC] px-4 py-2 rounded-xl transition-all">ایجاد</button>
                                </div>
                            </form>
                        </div>
                    </template>

                    {{-- Project Modal --}}
                    <template x-if="modalType === 'project'">
                        <div>
                            <h3 class="text-sm font-bold text-[#1A1D21] mb-4">پروژه جدید</h3>
                            <form action="{{ route('dashboard.project.store', $activeWorkspace?->slug) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">نام پروژه</label>
                                    <input name="name" type="text" required class="w-full text-sm font-bold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors" placeholder="نام پروژه">
                                </div>
                                <div class="mb-4">
                                    <label class="block text-[10px] font-bold text-[#94A3B8] mb-1.5 uppercase tracking-widest">کلید پروژه <span class="text-[#CBD5E1]">(اختیاری)</span></label>
                                    <input name="key" type="text" maxlength="10" class="w-full text-sm font-bold border-2 border-[#E2E8F0] rounded-xl px-4 py-2.5 focus:outline-none focus:border-[#0069FF] transition-colors uppercase" placeholder="مثلاً SCR">
                                </div>
                                <div class="flex gap-2 justify-end">
                                    <button type="button" @click="showModal = false" class="text-xs font-semibold text-[#64748B] px-4 py-2 rounded-xl border border-[#E2E8F0] hover:bg-[#F8FAFC] transition-colors">انصراف</button>
                                    <button type="submit" class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0055CC] px-4 py-2 rounded-xl transition-all">ایجاد</button>
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
