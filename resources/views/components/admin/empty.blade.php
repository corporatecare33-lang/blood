@props(['icon' => 'inbox', 'title'])
<div {{ $attributes->class('flex flex-col items-center justify-center px-6 py-14 text-center') }}>
    <span class="flex size-11 items-center justify-center rounded-full bg-muted text-ink-soft" aria-hidden="true">
        <x-dynamic-component :component="'lucide-'.$icon" class="size-5" />
    </span>
    <p class="mt-3 font-semibold">{{ $title }}</p>
    @if ($slot->isNotEmpty())<p class="mt-1 max-w-sm text-sm text-muted-foreground">{{ $slot }}</p>@endif
</div>
