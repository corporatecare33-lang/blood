{{-- BloodBond's five donation conditions (আমাদের শর্তসমূহ), verbatim from the original site. --}}
@props(['heading' => true])

@php
    // One marker per original condition, in config order; the age rule shows its number instead of an icon.
    // Conditions an admin added or reworded get a plain check, so an icon never mislabels new text.
    $markers = ['hand-heart', '18+', 'cigarette-off', 'utensils', 'map-pin'];
    $originals = config('bandhan.conditions');
@endphp

<div {{ $attributes->class('flex flex-col') }}>
    @if ($heading)
        <div data-reveal>
            <h2 class="text-2xl font-bold md:text-3xl">আমাদের শর্তসমূহ</h2>
            <p class="mt-1 text-ink-soft">রক্তদাতা ও রক্তগ্রহীতা — দুজনের জন্যই প্রযোজ্য</p>
        </div>
    @endif
    <ol data-reveal-stagger class="card mt-4 flex flex-1 flex-col justify-around p-2 sm:p-3">
        @foreach (\App\Support\Site::conditions() as $i => $condition)
            @php($marker = ($originals[$i] ?? null) === $condition ? $markers[$i] : 'check')
            <li class="flex items-center gap-4 rounded-lg px-3 py-3 sm:px-4">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-crimson-soft text-crimson ring-1 ring-crimson/10" aria-hidden="true">
                    @if ($marker === '18+')
                        <span class="font-display text-[0.95rem] leading-none font-bold">{{ \App\Support\Bn::num(18) }}+</span>
                    @else
                        <x-dynamic-component :component="'lucide-'.$marker" class="size-5" />
                    @endif
                </span>
                <span class="text-[1.0625rem] leading-relaxed">{{ $condition }}</span>
            </li>
        @endforeach
    </ol>
</div>
