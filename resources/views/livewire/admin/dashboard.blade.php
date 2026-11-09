<div class="grid grid-cols-[minmax(0,1fr)] gap-6">
    {{-- Range filter: one row above everything it controls --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-ui.tabs model="range" :value="$range" label="সময়সীমা"
            :options="[7 => ['৭ দিন', null], 30 => ['৩০ দিন', null], 90 => ['৯০ দিন', null]]" />
        <p wire:loading class="flex items-center gap-2 text-sm text-muted-foreground"><x-ui.spinner />হালনাগাদ হচ্ছে…</p>
    </div>

    {{-- Stat tiles --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" wire:loading.class="opacity-60">
        @foreach ($stats as $s)
            <a href="{{ $s['href'] }}" wire:navigate class="admin-card group flex flex-col p-5 transition-colors hover:border-crimson/30">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-muted-foreground">{{ $s['label'] }}</p>
                    <span class="flex size-8 items-center justify-center rounded-lg bg-crimson-soft text-crimson" aria-hidden="true">
                        <x-dynamic-component :component="'lucide-'.$s['icon']" class="size-4" />
                    </span>
                </div>
                <p class="mt-2 text-3xl font-semibold text-ink" data-count-to="{{ $s['value'] }}">{{ \App\Support\Bn::num($s['value']) }}</p>
                <p class="mt-1 text-xs text-muted-foreground">{{ $s['note'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 xl:grid-cols-5">
        {{-- Trend --}}
        <section x-data="{ metric: 'donors' }" class="admin-card p-5 xl:col-span-3" aria-labelledby="trend-title">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 id="trend-title" class="font-sans text-base font-semibold"
                        x-text="metric === 'donors' ? 'নতুন ডোনার নিবন্ধন' : 'নতুন রক্তের অনুরোধ'">নতুন ডোনার নিবন্ধন</h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">গত {{ \App\Support\Bn::num($range) }} দিন, {{ $range > 30 ? 'সাপ্তাহিক' : 'দৈনিক' }}</p>
                </div>
                <div role="tablist" aria-label="কোন তথ্য দেখাবে" class="inline-flex h-8 items-center rounded-lg bg-muted p-[3px] text-sm">
                    @foreach (['donors' => 'ডোনার', 'requests' => 'অনুরোধ'] as $key => $label)
                        <button type="button" role="tab" x-on:click="metric = '{{ $key }}'" x-bind:aria-selected="metric === '{{ $key }}'"
                            x-bind:class="metric === '{{ $key }}' ? 'bg-white text-ink shadow-xs' : 'text-ink-soft hover:text-ink'"
                            class="h-full rounded-md px-3 font-medium">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="mt-6" wire:loading.class="opacity-60">
                <div x-show="metric === 'donors'">
                    <x-admin.column-chart :points="$trend['donors']" unit="জন" caption="নতুন ডোনার নিবন্ধন, গত {{ \App\Support\Bn::num($range) }} দিন" />
                </div>
                <div x-show="metric === 'requests'" x-cloak>
                    <x-admin.column-chart :points="$trend['requests']" unit="টি" caption="নতুন রক্তের অনুরোধ, গত {{ \App\Support\Bn::num($range) }} দিন" />
                </div>
            </div>
        </section>

        {{-- Blood groups --}}
        <section class="admin-card p-5 xl:col-span-2" aria-labelledby="groups-title">
            <h2 id="groups-title" class="font-sans text-base font-semibold">রক্তের গ্রুপ অনুযায়ী ডোনার</h2>
            <p class="mt-0.5 text-sm text-muted-foreground">সক্রিয় ডোনার · পাশে কতজন এখন দিতে পারবেন</p>
            @php($max = max(1, $groups->max('total')))
            <table class="mt-5 w-full text-sm">
                <caption class="sr-only">প্রতিটি রক্তের গ্রুপে সক্রিয় ডোনার এবং এখন দিতে পারবেন এমন ডোনারের সংখ্যা</caption>
                <thead class="sr-only"><tr><th scope="col">গ্রুপ</th><th scope="col">ডোনার</th><th scope="col">এখন দিতে পারবেন</th></tr></thead>
                <tbody>
                    @foreach ($groups as $g)
                        <tr>
                            <th scope="row" class="w-12 py-[7px] pr-3 text-left">
                                <a href="{{ route('admin.donors', ['group' => $g['slug']]) }}" wire:navigate class="group-glyph text-base text-ink hover:text-crimson" lang="en">{{ $g['group'] }}</a>
                            </th>
                            <td class="py-[7px]">
                                {{-- pr-8 reserves room for the value at the tip; bar % resolves against the remaining width. --}}
                                <div class="flex items-center gap-2 pr-8">
                                    <span class="h-3 shrink-0 rounded-r-[4px] bg-crimson" style="width: {{ $g['total'] ? max(2, $g['total'] / $max * 100) : 0 }}%" aria-hidden="true"></span>
                                    <span class="font-semibold tabular-nums">{{ \App\Support\Bn::num($g['total']) }}</span>
                                </div>
                            </td>
                            <td class="w-24 py-[7px] text-right text-xs whitespace-nowrap text-muted-foreground">{{ \App\Support\Bn::num($g['ready']) }} প্রস্তুত</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </div>

    <div class="grid grid-cols-[minmax(0,1fr)] gap-6 xl:grid-cols-2">
        {{-- Urgent requests --}}
        <x-ui.card title="সবচেয়ে কাছের প্রয়োজন" description="খোলা অনুরোধ, যেটা আগে দরকার সেটা আগে" :padded="false">
            <x-slot:action>
                <x-ui.button :href="route('admin.requests')" variant="outline" size="xs">সব দেখুন</x-ui.button>
            </x-slot:action>
            @if ($urgent->isEmpty())
                <x-admin.empty icon="clipboard-check" title="কোনো খোলা অনুরোধ নেই">নতুন অনুরোধ এলে এখানে দেখাবে।</x-admin.empty>
            @else
                <ul class="divide-y divide-border border-t border-border">
                    @foreach ($urgent as $r)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <span class="group-glyph flex size-10 shrink-0 items-center justify-center rounded-lg bg-crimson-soft text-sm text-crimson-deep" lang="en">{{ $r->blood_group->value }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ $r->patient_name }} <span class="font-normal text-muted-foreground">· {{ \App\Support\Bn::num($r->bags) }} ব্যাগ</span></p>
                                <p class="truncate text-xs text-muted-foreground">{{ $r->hospital }}, {{ $r->district->bn_name }}</p>
                            </div>
                            <div class="max-w-[40%] shrink-0 text-right">
                                @if ($r->isUrgent())<x-ui.badge variant="danger">জরুরি</x-ui.badge>@endif
                                <p class="mt-0.5 text-xs text-muted-foreground">{{ \App\Support\Bn::dateTime($r->needed_at) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        {{-- Latest donors --}}
        <x-ui.card title="নতুন ডোনার" description="সাম্প্রতিক নিবন্ধন" :padded="false">
            <x-slot:action>
                <x-ui.button :href="route('admin.donors')" variant="outline" size="xs">সব দেখুন</x-ui.button>
            </x-slot:action>
            @if ($latest->isEmpty())
                <x-admin.empty icon="user-plus" title="এখনো কোনো ডোনার নিবন্ধন করেননি" />
            @else
                <ul class="divide-y divide-border border-t border-border">
                    @foreach ($latest as $donor)
                        <li class="flex items-center gap-3 px-5 py-3">
                            <x-ui.avatar :user="$donor" size="size-9 text-xs" />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ $donor->name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ $donor->district->bn_name }} · {{ \App\Support\Bn::ago($donor->created_at) }}</p>
                            </div>
                            <span class="group-glyph text-sm text-ink" lang="en">{{ $donor->blood_group->value }}</span>
                            @unless ($donor->is_active)<x-ui.badge>নিষ্ক্রিয়</x-ui.badge>@endunless
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>
</div>
