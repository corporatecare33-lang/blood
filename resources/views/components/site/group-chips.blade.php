{{--
    Blood-group chip row (mockup: "সব" + red-outlined chips, scrolls sideways on phones).
    Mode "links": each chip links to donor search. Mode "radio": pass name + wire:model via $attributes on inputs.
--}}
@props(['mode' => 'links', 'name' => 'group', 'all' => true, 'model' => null, 'value' => 'slug'])

<div {{ $attributes->class('-mx-4 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:mx-0 sm:px-0') }}>
    <ul class="flex w-max gap-2">
        @if ($all)
            <li>
                @if ($mode === 'links')
                    <a href="{{ route('donors.search') }}" wire:navigate class="flex h-11 items-center gap-1.5 rounded-lg bg-crimson px-4 font-semibold text-white">
                        <x-lucide-droplet class="size-4 fill-white" aria-hidden="true" />সব
                    </a>
                @else
                    <label class="relative cursor-pointer">
                        <input type="radio" name="{{ $name }}" value="" @if ($model) wire:model.live="{{ $model }}" @endif class="peer sr-only">
                        <span class="flex h-11 items-center gap-1.5 rounded-lg border border-crimson/40 bg-white px-4 font-semibold text-crimson transition-colors peer-checked:border-crimson peer-checked:bg-crimson peer-checked:text-white peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-marigold">
                            <x-lucide-droplet class="size-4" aria-hidden="true" />সব
                        </span>
                    </label>
                @endif
            </li>
        @endif
        @foreach (\App\Enums\BloodGroup::cases() as $group)
            @php($v = $value === 'slug' ? $group->slug() : $group->value)
            <li data-glyph>
                @if ($mode === 'links')
                    <a href="{{ route('donors.search', ['group' => $group->slug()]) }}" wire:navigate lang="en"
                       class="group-glyph flex h-11 min-w-14 items-center justify-center rounded-lg border border-crimson/40 bg-white px-3 text-lg text-crimson transition-colors hover:border-crimson hover:bg-crimson-soft">{{ $group->value }}</a>
                @else
                    <label class="relative cursor-pointer">
                        <input type="radio" name="{{ $name }}" value="{{ $v }}" @if ($model) wire:model.live="{{ $model }}" @endif class="peer sr-only">
                        <span lang="en" class="group-glyph flex h-11 min-w-14 items-center justify-center rounded-lg border border-crimson/40 bg-white px-3 text-lg text-crimson transition-colors hover:border-crimson
                                     peer-checked:border-crimson peer-checked:bg-crimson peer-checked:text-white peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-marigold">{{ $group->value }}</span>
                    </label>
                @endif
            </li>
        @endforeach
    </ul>
</div>
