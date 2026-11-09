{{--
    Single-series column chart (counts over time) in plain HTML.
    <x-admin.column-chart :points="[['label' => '৯ নভেম্বর', 'tip' => '…', 'value' => 3], …]" unit="জন" caption="…" />
    One hue (crimson), no legend (the card title names the series), hairline grid with three clean ticks,
    columns ≤ 24px with a 4px rounded cap, hover/focus tooltip per column, and a screen-reader table.
--}}
@props(['points', 'unit' => '', 'caption'])
@php
    $max = max(1, max(array_column($points, 'value')));
    // Round the axis up to a clean step: 1, 2, 5 × 10ⁿ, split into two intervals.
    $magnitude = 10 ** floor(log10($max));
    $top = collect([1, 2, 5, 10])->map(fn ($m) => $m * $magnitude)->first(fn ($v) => $v >= $max);
    $top = max(2, (int) (($top % 2) ? $top + 1 : $top));
    $ticks = [$top, $top / 2, 0];
    $count = count($points);
    $labelAt = array_unique([0, intdiv($count - 1, 2), $count - 1]);
    $empty = array_sum(array_column($points, 'value')) === 0;
@endphp

<figure {{ $attributes->class('relative') }}>
    <div class="flex gap-3">
        {{-- y axis --}}
        <div class="flex h-52 flex-col justify-between py-0 text-right text-xs text-muted-foreground tabular-nums" aria-hidden="true">
            @foreach ($ticks as $t)<span class="-my-2 leading-4">{{ \App\Support\Bn::num($t) }}</span>@endforeach
        </div>

        <div class="relative min-w-0 flex-1">
            {{-- grid --}}
            <div class="pointer-events-none absolute inset-x-0 top-0 flex h-52 flex-col justify-between" aria-hidden="true">
                @foreach ($ticks as $t)<span class="h-px w-full bg-border"></span>@endforeach
            </div>

            {{-- columns --}}
            <div class="relative flex h-52 items-end gap-[2px]" aria-hidden="true">
                @foreach ($points as $i => $p)
                    @php($h = $p['value'] / $top * 100)
                    <div class="group relative flex h-full min-w-0 flex-1 items-end justify-center">
                        <span class="absolute inset-0 rounded-sm group-hover:bg-muted/60"></span>
                        <span class="relative w-full max-w-6 rounded-t-[4px] bg-crimson transition-[height] duration-500 ease-out group-hover:bg-crimson-deep"
                              style="height: {{ $p['value'] ? max(1.5, $h) : 0 }}%"></span>
                        <span @class([
                                'pointer-events-none absolute bottom-full z-10 mb-2 hidden w-max max-w-48 rounded-md bg-ink px-2.5 py-1.5 text-xs leading-snug text-white shadow-pop group-hover:block',
                                'left-0' => $i < $count / 4,
                                'right-0' => $i > $count * 3 / 4,
                                'left-1/2 -translate-x-1/2' => $i >= $count / 4 && $i <= $count * 3 / 4,
                            ])>
                            <span class="block text-white/70">{{ $p['tip'] }}</span>
                            <span class="font-semibold">{{ \App\Support\Bn::num($p['value']) }} {{ $unit }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

            @if ($empty)
                <p class="absolute inset-x-0 top-[38%] text-center text-sm text-muted-foreground">এই সময়ে নতুন কিছু নেই</p>
            @endif

            {{-- x labels: first, middle, last --}}
            <div class="relative mt-2 h-4 text-xs text-muted-foreground" aria-hidden="true">
                @foreach ($labelAt as $i)
                    <span @class([
                        'absolute top-0 whitespace-nowrap',
                        'left-0' => $i === 0,
                        'right-0' => $i === $count - 1 && $i !== 0,
                        '-translate-x-1/2' => $i !== 0 && $i !== $count - 1,
                    ]) @if ($i !== 0 && $i !== $count - 1) style="left: {{ ($i + 0.5) / $count * 100 }}%" @endif>{{ $points[$i]['label'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- A <table> ignores sr-only's 1px box, so the wrapper hides it. --}}
    <div class="sr-only"><table>
        <caption>{{ $caption }}</caption>
        <thead><tr><th scope="col">সময়</th><th scope="col">সংখ্যা</th></tr></thead>
        <tbody>
            @foreach ($points as $p)<tr><td>{{ $p['tip'] }}</td><td>{{ \App\Support\Bn::num($p['value']) }}</td></tr>@endforeach
        </tbody>
    </table></div>
</figure>
