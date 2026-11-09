{{--
    shadcn DropdownMenu. Items: <button class="ui-menu-item">…</button> or <a class="ui-menu-item">…</a>;
    separator: <div class="-mx-1 my-1 h-px bg-border"></div>; label: <p class="px-2 py-1.5 text-xs font-medium text-muted-foreground">…</p>.
    The menu is teleported to <body> so table scrollers never clip it; $wire still resolves to the owning component.
--}}
@props(['align' => 'bottom-end', 'width' => 'w-52', 'label' => 'আরও কাজ'])

<div x-data="{ open: false }" class="relative inline-flex" x-on:keydown.escape="open && (open = false, $refs.trigger.querySelector('button')?.focus())">
    <div x-ref="trigger" x-on:click="open = !open" class="inline-flex" x-bind:aria-expanded="open">
        @isset($trigger)
            {{ $trigger }}
        @else
            <button type="button" aria-haspopup="menu" class="flex size-8 items-center justify-center rounded-md text-ink-soft hover:bg-muted hover:text-ink">
                <x-lucide-ellipsis class="size-4" aria-hidden="true" /><span class="sr-only">{{ $label }}</span>
            </button>
        @endisset
    </div>
    <template x-teleport="body">
        <div x-show="open" x-cloak x-anchor.{{ $align }}.offset.6="$refs.trigger"
             x-on:click.outside="open = false" x-on:click="$event.target.closest('a,button') && (open = false)"
             x-on:keydown.escape="open = false"
             x-transition:enter="transition duration-100 ease-out" x-transition:enter-start="opacity-0 scale-95"
             x-transition:leave="transition duration-75 ease-in" x-transition:leave-end="opacity-0"
             role="menu" {{ $attributes->class("z-50 {$width} rounded-lg border border-border bg-white p-1 shadow-pop") }}>
            {{ $slot }}
        </div>
    </template>
</div>
