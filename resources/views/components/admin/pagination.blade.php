{{-- Data-table footer for a Livewire paginator: "x–y / total" and previous/next. --}}
@props(['paginator', 'unit' => 'টি'])
@php($bn = fn ($n) => \App\Support\Bn::num($n))

<div {{ $attributes->class('flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-sm') }}>
    <p class="text-muted-foreground">
        @if ($paginator->total())
            মোট {{ $bn($paginator->total()) }} {{ $unit }} — দেখানো হচ্ছে {{ $bn($paginator->firstItem()) }}–{{ $bn($paginator->lastItem()) }}
        @else
            কিছু পাওয়া যায়নি
        @endif
    </p>
    @if ($paginator->hasPages())
        <nav class="flex items-center gap-2" aria-label="পাতা বদলান">
            <span class="text-muted-foreground">পাতা {{ $bn($paginator->currentPage()) }} / {{ $bn($paginator->lastPage()) }}</span>
            <x-ui.button type="button" variant="outline" size="icon-sm" wire:click="previousPage('{{ $paginator->getPageName() }}')" :disabled="$paginator->onFirstPage()">
                <x-lucide-chevron-left aria-hidden="true" /><span class="sr-only">আগের পাতা</span>
            </x-ui.button>
            <x-ui.button type="button" variant="outline" size="icon-sm" wire:click="nextPage('{{ $paginator->getPageName() }}')" :disabled="! $paginator->hasMorePages()">
                <x-lucide-chevron-right aria-hidden="true" /><span class="sr-only">পরের পাতা</span>
            </x-ui.button>
        </nav>
    @endif
</div>
