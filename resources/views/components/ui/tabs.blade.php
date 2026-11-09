{{--
    shadcn Tabs used as a filter bound to a Livewire property.
    <x-ui.tabs model="status" :value="$status" label="…" :options="['open' => ['খোলা', 12], 'all' => ['সব', null]]" />
--}}
@props(['model', 'value', 'options', 'label'])

<div role="tablist" aria-label="{{ $label }}" {{ $attributes->class('inline-flex h-9 max-w-full items-center gap-0.5 overflow-x-auto rounded-lg bg-muted p-[3px]') }}>
    @foreach ($options as $key => [$text, $count])
        @php($active = (string) $value === (string) $key)
        <button type="button" role="tab" aria-selected="{{ $active ? 'true' : 'false' }}" wire:click="$set('{{ $model }}', '{{ $key }}')"
            @class([
                'inline-flex h-full items-center gap-1.5 rounded-md px-3 text-sm font-medium whitespace-nowrap transition-colors',
                'bg-white text-ink shadow-xs' => $active,
                'text-ink-soft hover:text-ink' => ! $active,
            ])>
            {{ $text }}
            @if ($count !== null)
                <span @class(['rounded-full px-1.5 text-xs tabular-nums', 'bg-crimson-soft text-crimson-deep' => $active, 'bg-ink/8' => ! $active])>{{ \App\Support\Bn::num($count) }}</span>
            @endif
        </button>
    @endforeach
</div>
