@props(['items' => []])

@if ($items->isNotEmpty())
    <nav class="flex items-center gap-1.5 min-w-0 overflow-x-auto text-nowrap pb-1 -mb-1" aria-label="breadcrumb">
        @foreach ($items as $i => $item)
            @if ($i > 0)
                <svg class="w-3 h-3 text-[#CBD5E1] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->isLocale('fa') ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}"/></svg>
            @endif
            @if ($item['url'] ?? null)
                <a href="{{ $item['url'] }}" class="text-[11px] font-medium text-[#64748B] hover:text-[#0069FF] transition-colors truncate">{{ $item['label'] }}</a>
            @else
                <span class="text-[11px] font-bold text-[#172B4D] truncate">{{ $item['label'] }}</span>
            @endif
        @endforeach
    </nav>
@endif
