{{-- Donor card: blood-group drop · name · area · status, then the number with SMS / call. --}}
@props(['donor'])
@php
    $wait = $donor->daysUntilEligible();
    $canGive = $donor->is_available && $wait === 0;
    $area = collect([$donor->thana?->bn_name ?? $donor->thana?->name, $donor->district?->bn_name])->filter()->implode(', ');
@endphp

<article {{ $attributes->class('card flex h-full flex-col') }}>
    <div class="flex gap-3.5 p-4 pb-3.5">
        <span @class(['blood-drop', 'is-muted' => ! $canGive]) aria-hidden="true">
            <span class="group-glyph text-base" lang="en">{{ $donor->blood_group->value }}</span>
        </span>

        <div class="min-w-0 flex-1">
            <h3 class="truncate font-sans text-[1.0625rem] font-bold">{{ $donor->name }}</h3>
            <p class="sr-only">রক্তের গ্রুপ <span lang="en">{{ $donor->blood_group->value }}</span></p>
            @if ($area)
                <p class="mt-0.5 flex min-w-0 items-center gap-1 text-[0.9375rem] text-ink-soft">
                    <x-lucide-map-pin class="size-4 shrink-0" aria-hidden="true" /><span class="truncate">{{ $area }}</span>
                </p>
            @endif
            <p class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-sm">
                @if (! $donor->is_available)
                    <span class="inline-flex items-center gap-1.5 font-semibold text-ink-soft"><span class="size-2 rounded-full bg-ink-soft/50" aria-hidden="true"></span>এখন বিরতিতে</span>
                @elseif ($wait > 0)
                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#7a5600]"><span class="size-2 rounded-full bg-marigold" aria-hidden="true"></span>{{ \App\Support\Bn::num($wait) }} দিন পর দিতে পারবেন</span>
                @else
                    <span class="inline-flex items-center gap-1.5 font-semibold text-leaf"><span class="size-2 rounded-full bg-leaf" aria-hidden="true"></span>এখন দিতে পারবেন</span>
                @endif
                @if ($donor->last_donated_at)
                    <span class="text-ink-soft">শেষ রক্তদান {{ \App\Support\Bn::ago($donor->last_donated_at) }}</span>
                @endif
            </p>
        </div>
    </div>

    <div class="mt-auto flex items-center gap-2 border-t border-border px-4 py-3">
        <span class="mr-auto flex min-w-0 items-center gap-1.5 text-[0.9375rem] font-semibold tabular-nums" lang="en">
            <x-lucide-phone class="size-4 shrink-0 text-ink-soft" aria-hidden="true" /><span class="truncate select-all">{{ $donor->mobile }}</span>
        </span>
        <x-ui.button size="icon" variant="soft" :href="'sms:+88'.$donor->mobile" :navigate="false" aria-label="{{ $donor->name }}-কে এসএমএস করুন">
            <x-lucide-message-square aria-hidden="true" />
        </x-ui.button>
        <x-ui.button size="sm" :href="'tel:+88'.$donor->mobile" :navigate="false" aria-label="{{ $donor->name }}-কে কল করুন ({{ $donor->mobile }})">
            <x-lucide-phone-call aria-hidden="true" />কল করুন
        </x-ui.button>
    </div>
</article>
