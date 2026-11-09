{{-- shadcn Card. Slots: action (top-right), footer. --}}
@props(['title' => null, 'description' => null, 'padded' => true])

<section {{ $attributes->class('admin-card flex flex-col') }}>
    @if ($title || isset($action))
        <header @class(['flex items-start justify-between gap-4 px-5 pt-5', 'pb-4' => ! $padded])>
            <div class="min-w-0">
                @if ($title)<h2 class="font-sans text-base font-semibold text-ink">{{ $title }}</h2>@endif
                @if ($description)<p class="mt-0.5 text-sm text-muted-foreground">{{ $description }}</p>@endif
            </div>
            @isset($action)<div class="shrink-0">{{ $action }}</div>@endisset
        </header>
    @endif
    <div @class(['flex-1', 'p-5' => $padded, 'pt-4' => $padded && ($title || isset($action))])>{{ $slot }}</div>
    @isset($footer)
        <footer class="flex items-center gap-2 border-t border-border px-5 py-3">{{ $footer }}</footer>
    @endisset
</section>
