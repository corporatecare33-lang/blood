{{-- Blood request card (mockup: drop badge · "জরুরি: O+ রক্ত প্রয়োজন" · hospital · time · action). --}}
@props(['request', 'compact' => false, 'level' => 'h3'])
@php($urgent = $request->isUrgent())

<article {{ $attributes->class('card flex gap-3.5 p-4') }}>
    <span class="flex size-12 shrink-0 flex-col items-center justify-center rounded-full bg-crimson text-white" aria-hidden="true">
        <x-lucide-droplet class="size-5 fill-white" />
    </span>

    <div class="min-w-0 flex-1">
        <{{ $level }} class="font-sans text-[1.0625rem] leading-snug font-bold">
            @if ($urgent)<span class="text-crimson">জরুরি:</span>@endif
            <span class="group-glyph text-crimson" lang="en">{{ $request->blood_group->value }}</span> রক্ত প্রয়োজন
            <span class="font-medium text-ink-soft">· {{ \App\Support\Bn::num($request->bags) }} ব্যাগ</span>
        </{{ $level }}>
        <p class="mt-0.5 text-[0.9375rem] text-ink-soft">{{ $request->hospital }}, {{ $request->district->bn_name }}</p>
        <p class="text-sm text-ink-soft">
            প্রয়োজন <time datetime="{{ $request->needed_at->toIso8601String() }}" class="font-medium text-ink">{{ \App\Support\Bn::dateTime($request->needed_at) }}</time>
            · পোস্ট {{ \App\Support\Bn::ago($request->created_at) }}
        </p>
        @if (! $compact)
            <p class="mt-1.5 text-[0.9375rem]">রোগী: {{ $request->patient_name }}</p>
            @if ($request->note)<p class="mt-1 line-clamp-2 text-[0.9375rem] text-ink-soft">{{ $request->note }}</p>@endif
        @endif

        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
            <span class="text-sm text-ink-soft">যোগাযোগ: {{ $request->contact_name }}</span>
            <x-ui.button size="sm" :href="'tel:+88'.$request->contact_mobile" :navigate="false" aria-label="কল করুন — {{ $request->contact_name }}, {{ $request->contact_mobile }}">
                <x-lucide-phone aria-hidden="true" />কল করুন
            </x-ui.button>
        </div>
    </div>
</article>
