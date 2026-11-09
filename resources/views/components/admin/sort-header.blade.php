{{-- Sortable <th>: <x-admin.sort-header column="name" :sort="$sort" :dir="$dir">নাম</x-admin.sort-header> --}}
@props(['column', 'sort', 'dir'])
@php($active = $sort === $column)

<th scope="col" aria-sort="{{ $active ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' }}" {{ $attributes }}>
    <button type="button" wire:click="sortBy('{{ $column }}')" class="-ml-2 inline-flex h-8 items-center gap-1 rounded-md px-2 hover:bg-muted hover:text-ink {{ $active ? 'text-ink' : '' }}">
        {{ $slot }}
        @if (! $active)
            <x-lucide-chevrons-up-down class="size-3.5 opacity-60" aria-hidden="true" />
        @elseif ($dir === 'asc')
            <x-lucide-arrow-up class="size-3.5" aria-hidden="true" />
        @else
            <x-lucide-arrow-down class="size-3.5" aria-hidden="true" />
        @endif
    </button>
</th>
