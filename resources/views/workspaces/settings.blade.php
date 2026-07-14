@extends('layouts.app')

@section('body')
<div class="neova-product min-h-screen bg-[#FAF9F6]" x-data="{ tab: 'general' }">
    <x-navbar>
        <x-breadcrumb :items="collect([
            ['label' => 'داشبورد', 'url' => route('dashboard')],
            ['label' => $workspace->name, 'url' => route('dashboard', ['workspace' => $workspace->slug])],
            ['label' => 'تنظیمات'],
        ])" />
    </x-navbar>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-7">
        @if (session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold rounded-xl px-4 py-3">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl px-4 py-3">{{ $errors->first() }}</div>
        @endif

        <div class="mb-6">
            <h2 class="text-xl font-black text-[#172B4D]">مدیریت فضای کاری</h2>
            <p class="text-xs text-[#64748B] mt-1">اعضا، دعوت‌نامه‌ها و تیم پروژه‌ها را مدیریت کنید.</p>
        </div>

        <div class="flex gap-1 border-b border-[#DCE3ED] mb-6 overflow-x-auto">
            <button @click="tab = 'general'" :class="tab === 'general' ? 'text-[#0069FF] border-[#0069FF]' : 'text-[#64748B] border-transparent'" class="px-4 py-3 text-xs font-bold border-b-2 whitespace-nowrap">تنظیمات</button>
            <button @click="tab = 'members'" :class="tab === 'members' ? 'text-[#0069FF] border-[#0069FF]' : 'text-[#64748B] border-transparent'" class="px-4 py-3 text-xs font-bold border-b-2 whitespace-nowrap">اعضا</button>
            <button @click="tab = 'invitations'" :class="tab === 'invitations' ? 'text-[#0069FF] border-[#0069FF]' : 'text-[#64748B] border-transparent'" class="px-4 py-3 text-xs font-bold border-b-2 whitespace-nowrap">دعوت‌نامه‌ها</button>
            <button @click="tab = 'projects'" :class="tab === 'projects' ? 'text-[#0069FF] border-[#0069FF]' : 'text-[#64748B] border-transparent'" class="px-4 py-3 text-xs font-bold border-b-2 whitespace-nowrap">تیم پروژه‌ها</button>
        </div>

        <section x-show="tab === 'general'" x-cloak>
            @if ($actorRole === 'owner')
                <div class="bg-white border border-[#DFE5EF] rounded-xl p-5 max-w-lg">
                    <h3 class="text-sm font-bold text-[#172B4D] mb-1">نام فضای کاری</h3>
                    <p class="text-[10px] text-[#94A3B8] mb-4">نامی که برای این فضای کاری نمایش داده می‌شود.</p>
                    <form method="POST" action="{{ route('workspaces.settings.update', $workspace->slug) }}" class="flex items-center gap-3">
                        @csrf
                        @method('PATCH')
                        <input name="name" value="{{ old('name', $workspace->name) }}" class="flex-1 text-sm border border-[#DCE3ED] rounded-lg px-3 py-2.5 focus:outline-none focus:border-[#0069FF]" required>
                        <button class="text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0057D9] rounded-lg px-4 py-2.5 shrink-0">ذخیره</button>
                    </form>
                </div>
            @else
                <div class="bg-white border border-[#DFE5EF] rounded-xl p-5 max-w-lg">
                    <h3 class="text-sm font-bold text-[#172B4D] mb-1">نام فضای کاری</h3>
                    <p class="text-[13px] text-[#475569] mt-2">{{ $workspace->name }}</p>
                    <p class="text-[10px] text-[#94A3B8] mt-2">فقط مالک می‌تواند نام فضای کاری را تغییر دهد.</p>
                </div>
            @endif
        </section>

        <section x-show="tab === 'members'" x-cloak>
            <div class="bg-white border border-[#DFE5EF] rounded-xl overflow-hidden">
                <div class="flex items-center gap-3 px-4 sm:px-5 py-4 border-b border-[#F1F5F9]">
                    <div class="w-9 h-9 rounded-full bg-[#031B4E] text-white flex items-center justify-center text-xs font-bold">{{ mb_substr($workspace->owner->full_name, 0, 1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-[#172B4D] truncate">{{ $workspace->owner->full_name }}</p>
                        <p class="text-[10px] text-[#94A3B8]">{{ $workspace->owner->phone }}</p>
                    </div>
                    <span class="text-[10px] font-bold text-[#0069FF] bg-[#E8F0FE] px-2.5 py-1 rounded-md">مالک</span>
                </div>

                @foreach ($members as $member)
                    @php
                        $roleLabels = ['admin' => 'مدیر', 'user' => 'کاربر', 'viewer' => 'مشاهده‌گر'];
                        $canManageTarget = $workspace->canManageRole(auth()->user(), $member->pivot->role);
                        $allowedRoleOptions = $actorRole === 'owner' ? ['admin', 'user', 'viewer'] : ['user', 'viewer'];
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 sm:px-5 py-4 border-b border-[#F1F5F9] last:border-0">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-[#E8F0FE] text-[#0069FF] flex items-center justify-center text-xs font-bold">{{ mb_substr($member->full_name, 0, 1) }}</div>
                            <div class="min-w-0">
                                <p class="text-[13px] font-bold text-[#172B4D] truncate">{{ $member->full_name }}</p>
                                <p class="text-[10px] text-[#94A3B8]">{{ $member->phone }}</p>
                            </div>
                        </div>
                        @if ($canManageTarget)
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('workspaces.members.role', [$workspace->slug, $member]) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="text-[11px] font-semibold border border-[#DCE3ED] rounded-lg px-2.5 py-2 bg-white">
                                        @foreach ($allowedRoleOptions as $role)
                                            <option value="{{ $role }}" @selected($member->pivot->role === $role)>{{ $roleLabels[$role] }}</option>
                                        @endforeach
                                    </select>
                                    <button class="text-[11px] font-bold text-[#0069FF] px-2 py-2">ذخیره</button>
                                </form>
                                <form method="POST" action="{{ route('workspaces.members.destroy', [$workspace->slug, $member]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-[11px] font-semibold text-red-500 px-2 py-2">حذف</button>
                                </form>
                            </div>
                        @else
                            <span class="text-[10px] font-bold text-[#64748B] bg-[#F1F5F9] px-2.5 py-1 rounded-md">{{ $roleLabels[$member->pivot->role] ?? $member->pivot->role }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($actorRole !== 'owner')
                <form method="POST" action="{{ route('workspaces.leave', $workspace->slug) }}" class="mt-5">
                    @csrf
                    <button class="text-xs font-bold text-red-500 border border-red-200 bg-white rounded-lg px-4 py-2.5">ترک فضای کاری</button>
                </form>
            @endif
        </section>

        <section x-show="tab === 'invitations'" x-cloak>
            <div class="grid lg:grid-cols-[340px_1fr] gap-5">
                <form method="POST" action="{{ route('workspaces.invitations.store', $workspace->slug) }}" class="bg-white border border-[#DFE5EF] rounded-xl p-5 h-fit">
                    @csrf
                    <h3 class="text-sm font-bold text-[#172B4D] mb-4">دعوت عضو جدید</h3>
                    <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">شماره تلفن</label>
                    <input name="phone" value="{{ old('phone') }}" dir="ltr" placeholder="09123456789" class="w-full text-sm border border-[#DCE3ED] rounded-lg px-3 py-2.5 focus:outline-none focus:border-[#0069FF] mb-4">
                    <label class="block text-[10px] font-bold text-[#64748B] mb-1.5">نقش</label>
                    <select name="role" class="w-full text-sm border border-[#DCE3ED] rounded-lg px-3 py-2.5 bg-white mb-4">
                        @if ($actorRole === 'owner')<option value="admin">مدیر</option>@endif
                        <option value="user">کاربر</option>
                        <option value="viewer">مشاهده‌گر</option>
                    </select>
                    <button class="w-full text-xs font-bold text-white bg-[#0069FF] hover:bg-[#0057D9] rounded-lg px-4 py-2.5">ارسال دعوت‌نامه</button>
                    <p class="text-[10px] leading-5 text-[#94A3B8] mt-3">لینک دعوت از طریق پیامک ارسال می‌شود و ۷ روز اعتبار دارد.</p>
                </form>

                <div class="bg-white border border-[#DFE5EF] rounded-xl overflow-hidden">
                    @forelse ($invitations as $invitation)
                        @php
                            $statusLabels = ['pending' => 'در انتظار', 'accepted' => 'پذیرفته', 'declined' => 'رد شده', 'revoked' => 'لغو شده', 'expired' => 'منقضی'];
                        @endphp
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 py-4 border-b border-[#F1F5F9] last:border-0">
                            <div class="flex-1">
                                <p class="text-[13px] font-bold text-[#172B4D]" dir="ltr">{{ $invitation->phone }}</p>
                                <p class="text-[10px] text-[#94A3B8] mt-1">{{ $statusLabels[$invitation->status] ?? $invitation->status }} · {{ $invitation->expires_at->format('Y/m/d') }}</p>
                            </div>
                            @if ($invitation->status === 'pending' && $workspace->canManageRole(auth()->user(), $invitation->role))
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('workspaces.invitations.resend', [$workspace->slug, $invitation]) }}">
                                        @csrf
                                        <button class="text-[11px] font-bold text-[#0069FF] px-3 py-2">ارسال مجدد</button>
                                    </form>
                                    <form method="POST" action="{{ route('workspaces.invitations.revoke', [$workspace->slug, $invitation]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-[11px] font-bold text-red-500 px-3 py-2">لغو</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-16 text-center text-xs text-[#94A3B8]">دعوت‌نامه‌ای وجود ندارد</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section x-show="tab === 'projects'" x-cloak class="space-y-4">
            @php $workspacePeople = collect([$workspace->owner])->merge($members)->unique('id'); @endphp
            @forelse ($projects as $project)
                @php $availablePeople = $workspacePeople->whereNotIn('id', $project->members->pluck('id')); @endphp
                <div class="bg-white border border-[#DFE5EF] rounded-xl p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-[#172B4D]">{{ $project->name }}</h3>
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md {{ $project->visibility === 'public' ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEF3C7] text-[#D97706]' }}">
                                    {{ $project->visibility === 'public' ? 'عمومی' : 'خصوصی' }}
                                </span>
                            </div>
                            <p class="text-[10px] text-[#94A3B8] mt-1">{{ $project->members->count() }} عضو در تیم پروژه</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($actorRole === 'owner' || $actorRole === 'admin')
                                <form method="POST" action="{{ route('workspaces.projects.visibility', [$workspace->slug, $project]) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="visibility" value="{{ $project->visibility === 'public' ? 'private' : 'public' }}">
                                    <button class="text-[10px] font-bold text-[#0069FF] hover:underline">
                                        {{ $project->visibility === 'public' ? 'خصوصی کردن' : 'عمومی کردن' }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('workspaces.projects.members.store', [$workspace->slug, $project]) }}" class="flex gap-2">
                                @csrf
                                <select name="user_id" class="min-w-44 text-[11px] border border-[#DCE3ED] rounded-lg px-2.5 py-2 bg-white">
                                    @forelse ($availablePeople as $person)
                                        <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                                    @empty
                                        <option value="">همه اعضا اضافه شده‌اند</option>
                                    @endforelse
                                </select>
                                <button @disabled($availablePeople->isEmpty()) class="text-[11px] font-bold text-white bg-[#0069FF] disabled:bg-[#CBD5E1] rounded-lg px-3 py-2">افزودن</button>
                            </form>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-4">
                        @forelse ($project->members as $member)
                            <div class="inline-flex items-center gap-2 bg-[#F1F5F9] rounded-lg px-2.5 py-1.5">
                                <span class="text-[11px] font-semibold text-[#475569]">{{ $member->full_name }}</span>
                                <form method="POST" action="{{ route('workspaces.projects.members.destroy', [$workspace->slug, $project, $member]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-[#94A3B8] hover:text-red-500">×</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-[11px] text-[#94A3B8]">هنوز عضوی برای این تیم انتخاب نشده است.</p>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-xs text-[#94A3B8]">پروژه‌ای وجود ندارد</div>
            @endforelse
        </section>
    </main>
</div>
@endsection
