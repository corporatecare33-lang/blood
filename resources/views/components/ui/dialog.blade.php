{{--
    shadcn Dialog / Sheet bound to a Livewire boolean.
    <x-ui.dialog model="showForm" title="…" description="…" [side="right"] [width="max-w-lg"]> body <x-slot:footer>…</x-slot:footer> </x-ui.dialog>
    side="right" renders it as a Sheet sliding in from the right edge. Put a wire:submit form around
    body + footer by passing form="method" — the footer's submit button then saves.
--}}
@props(['model', 'title', 'description' => null, 'side' => null, 'width' => 'max-w-lg', 'form' => null])
@php
    $id = 'dlg-'.\Illuminate\Support\Str::slug($model);
    $panel = $side === 'right'
        ? "absolute inset-y-0 right-0 flex w-full {$width} flex-col bg-white shadow-pop"
        : "pointer-events-auto flex max-h-[92dvh] w-full {$width} flex-col rounded-t-xl bg-white shadow-pop sm:rounded-xl";
    $tag = $form ? 'form' : 'div';
@endphp

<div x-data="{ open: $wire.entangle('{{ $model }}').live }" x-on:keydown.escape.window="open && (open = false)">
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
            <div x-show="open" x-transition.opacity.duration.150ms class="absolute inset-0 bg-ink/45" x-on:click="open = false"></div>

            <div @class(['pointer-events-none absolute inset-0 flex items-end justify-center sm:items-center sm:p-4' => $side !== 'right'])>
                <{{ $tag }} x-show="open" x-trap.noscroll="open"
                     @if ($form) wire:submit="{{ $form }}" novalidate @endif
                     @if ($side === 'right')
                         x-transition:enter="transition duration-250 ease-out" x-transition:enter-start="translate-x-full"
                         x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="translate-x-full"
                     @else
                         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                         x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 sm:scale-95"
                     @endif
                     {{ $attributes->class($panel) }}>
                    <header class="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                        <div class="min-w-0">
                            <h2 id="{{ $id }}-title" class="font-sans text-lg font-semibold">{{ $title }}</h2>
                            @if ($description)<p class="mt-0.5 text-sm text-muted-foreground">{{ $description }}</p>@endif
                        </div>
                        <button type="button" x-on:click="open = false" class="-mr-1.5 flex size-8 shrink-0 items-center justify-center rounded-md text-ink-soft hover:bg-muted hover:text-ink">
                            <x-lucide-x class="size-4" aria-hidden="true" /><span class="sr-only">বন্ধ করুন</span>
                        </button>
                    </header>
                    <div class="flex-1 overflow-y-auto px-5 py-5">{{ $slot }}</div>
                    @isset($footer)
                        <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-border px-5 py-3">{{ $footer }}</footer>
                    @endisset
                </{{ $tag }}>
            </div>
        </div>
    </template>
</div>
