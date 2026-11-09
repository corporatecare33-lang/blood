<div x-data="{ filters: false }" class="grid grid-cols-1 gap-5 lg:grid-cols-12 lg:gap-8">
    {{-- Finder: name/area search, the patient's group, and which groups that patient can receive --}}
    <div class="min-w-0 lg:col-span-12">
        <div class="card overflow-hidden">
            <div class="flex gap-2 p-2">
                <label class="relative min-w-0 flex-1">
                    <span class="sr-only">নাম বা এলাকা দিয়ে ডোনার খুঁজুন</span>
                    <x-lucide-search class="pointer-events-none absolute top-1/2 left-3.5 size-5 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
                    <input type="search" wire:model.live.debounce.400ms="q" placeholder="ডোনার খুঁজুন — নাম, এলাকা…"
                           class="block h-12 w-full rounded-lg border border-transparent bg-blush pr-3 pl-11 text-base placeholder:text-ink-soft/70 focus:border-crimson focus:bg-white focus:ring-3 focus:ring-crimson/15 focus:outline-none">
                </label>
                <button type="button" x-on:click="filters = !filters" x-bind:aria-expanded="filters" aria-controls="donor-filters"
                        class="relative flex size-12 shrink-0 items-center justify-center rounded-lg bg-crimson-soft text-crimson lg:hidden">
                    <x-lucide-sliders-horizontal class="size-5" aria-hidden="true" /><span class="sr-only">ফিল্টার</span>
                    @if ($this->activeFilterCount)
                        <span class="absolute -top-1.5 -right-1.5 flex size-5 items-center justify-center rounded-full bg-crimson text-xs font-bold text-white">{{ \App\Support\Bn::num($this->activeFilterCount) }}</span>
                    @endif
                </button>
            </div>

            <fieldset class="min-w-0 border-t border-border px-4 pt-3 pb-4">
                <legend class="float-left mb-2.5 w-full text-sm font-semibold text-ink-soft">রোগীর রক্তের গ্রুপ</legend>
                <x-site.group-chips mode="radio" model="group" name="donor-group" class="clear-left" />
            </fieldset>

            @if ($this->selectedGroup)
                @php
                    $donorGroups = $this->selectedGroup->canReceiveFrom();
                @endphp
                <div class="flex flex-col gap-3 border-t border-crimson/10 bg-crimson-soft/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="flex flex-wrap items-center gap-x-2 gap-y-1.5 text-[0.9375rem]">
                        <span><span class="group-glyph" lang="en">{{ $this->selectedGroup->value }}</span> রোগী
                            {{ count($donorGroups) === 1 ? 'শুধু এই গ্রুপের রক্ত নিতে পারেন' : 'যেসব গ্রুপের রক্ত নিতে পারেন' }}</span>
                        @if (count($donorGroups) > 1)
                            <span class="flex flex-wrap gap-1.5">
                                @foreach ($donorGroups as $g)
                                    <span lang="en" class="group-glyph rounded-md bg-white px-2 py-1 text-sm text-crimson ring-1 ring-crimson/20">{{ $g->value }}</span>
                                @endforeach
                            </span>
                        @endif
                    </p>
                    @if (count($donorGroups) > 1)
                        <label class="flex shrink-0 cursor-pointer items-center gap-2.5 text-[0.9375rem] font-semibold">
                            <x-ui.switch wire:model.live="compatible" />
                            সব মানানসই গ্রুপ দেখান
                        </label>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Filters: a collapsible sheet on phones, a sidebar on desktop --}}
    <aside id="donor-filters" aria-labelledby="filters-heading" x-bind:class="filters ? 'block' : 'hidden'" class="hidden min-w-0 lg:col-span-4 lg:block xl:col-span-3">
        <div class="card lg:sticky lg:top-24">
            <div class="flex items-center justify-between gap-3 border-b border-border px-4 py-3">
                <h2 id="filters-heading" class="flex items-center gap-2 font-sans text-base font-bold">
                    <x-lucide-sliders-horizontal class="size-4 text-crimson" aria-hidden="true" />ফিল্টার
                </h2>
                @if ($this->activeFilterCount)
                    <button type="button" wire:click="clearFilters" class="text-sm font-semibold text-crimson hover:underline">সব মুছুন</button>
                @endif
            </div>

            <fieldset class="grid min-w-0 gap-4 p-4">
                <legend class="sr-only">এলাকা</legend>
                <x-ui.field label="বিভাগ" for="f-division">
                    <x-ui.select id="f-division" wire:model.live="division_id" placeholder="সারা দেশ">
                        @foreach ($this->divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->bn_name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                @if ($division_id)
                    <x-ui.field label="জেলা" for="f-district">
                        <x-ui.select id="f-district" wire:model.live="district_id" placeholder="সব জেলা">
                            @foreach ($this->districts as $district)
                                <option value="{{ $district->id }}">{{ $district->bn_name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                @endif

                @if ($district_id)
                    <x-ui.field label="থানা" for="f-thana">
                        <x-ui.select id="f-thana" wire:model.live="thana_id" placeholder="সব থানা">
                            @foreach ($this->thanas as $thana)
                                <option value="{{ $thana->id }}">{{ $thana->bn_name ?? $thana->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </x-ui.field>
                @endif

                @unless ($district_id)
                    <p class="-mt-1 text-sm text-ink-soft">{{ $division_id ? 'জেলা বেছে নিলে থানা দিয়েও খুঁজতে পারবেন।' : 'বিভাগ বেছে নিলে জেলা ও থানা দিয়েও খুঁজতে পারবেন।' }}</p>
                @endunless
            </fieldset>

            <label class="flex cursor-pointer items-start justify-between gap-4 border-t border-border p-4">
                <span class="text-[0.9375rem] font-medium">বিরতিতে থাকা ডোনারও দেখান
                    <span class="mt-0.5 block text-sm font-normal text-ink-soft">গত {{ \App\Support\Bn::num(config('bandhan.donation_gap_days')) }} দিনে রক্ত দিয়েছেন বা নিজে বিরতি নিয়েছেন</span>
                </span>
                <span class="mt-1"><x-ui.switch wire:model.live="includeUnavailable" /></span>
            </label>
        </div>
    </aside>

    <section class="min-w-0 lg:col-span-8 xl:col-span-9" aria-labelledby="results-heading">
        <a href="{{ route('requests.create') }}" wire:navigate
           class="flex items-center gap-3 rounded-xl bg-crimson px-4 py-3 text-white shadow-card transition-colors hover:bg-crimson-deep">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white text-crimson" aria-hidden="true"><x-lucide-siren class="size-5" /></span>
            <span class="flex-1 leading-snug"><strong class="font-semibold">জরুরি রক্ত প্রয়োজন?</strong> <span class="text-white/85">অনুরোধ পোস্ট করুন</span></span>
            <x-lucide-chevron-right class="size-5 shrink-0" aria-hidden="true" />
        </a>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
            <h2 id="results-heading" class="text-xl font-bold md:text-2xl" aria-live="polite">
                {{ \App\Support\Bn::num($this->donors->total()) }} জন ডোনার পাওয়া গেছে
            </h2>
            <span wire:loading.flex class="items-center gap-2 text-sm text-ink-soft"><x-ui.spinner />খোঁজা হচ্ছে…</span>
        </div>

        @if ($this->activeFilters)
            <ul class="mt-3 flex flex-wrap gap-2" aria-label="চালু ফিল্টার">
                @foreach ($this->activeFilters as $key => $label)
                    <li wire:key="pill-{{ $key }}">
                        <button type="button" wire:click="removeFilter('{{ $key }}')"
                                class="inline-flex h-8 items-center gap-1.5 rounded-full border border-border bg-white pr-2 pl-3 text-sm font-medium text-ink transition-colors hover:border-crimson hover:text-crimson">
                            {{ $label }}<x-lucide-x class="size-3.5 text-ink-soft" aria-hidden="true" /><span class="sr-only">ফিল্টারটি মুছুন</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        <div wire:loading.class="opacity-60" class="mt-4 transition-opacity">
            @if ($this->donors->isEmpty())
                @php
                    $filtered = $this->activeFilterCount || trim($q) !== '';
                    $suggestCompatible = $this->selectedGroup && ! $compatible && count($this->selectedGroup->canReceiveFrom()) > 1;
                @endphp
                <x-site.empty-state icon="search" :title="$filtered ? 'এই খোঁজে কোনো ডোনার পাওয়া যায়নি' : 'এই মুহূর্তে রক্ত দিতে পারবেন এমন কোনো ডোনার নেই'">
                    @if ($suggestCompatible)
                        মানানসই গ্রুপের ডোনাররাও রক্ত দিতে পারেন — সেগুলোও দেখুন, অথবা রক্তের অনুরোধ পোস্ট করুন যাতে ডোনাররা আপনাকে খুঁজে পান।
                    @elseif ($filtered)
                        এলাকা বড় করে দেখুন, অথবা রক্তের অনুরোধ পোস্ট করুন যাতে ডোনাররা আপনাকে খুঁজে পান।
                    @else
                        রক্তের অনুরোধ পোস্ট করুন — অনুরোধ তালিকা দেখে ডোনাররা সরাসরি আপনাকে কল করবেন।
                    @endif
                    <x-slot:action>
                        <div class="flex flex-wrap justify-center gap-3">
                            @if ($suggestCompatible)
                                <x-ui.button type="button" variant="soft" wire:click="$set('compatible', true)">মানানসই গ্রুপ দেখান</x-ui.button>
                            @elseif ($filtered)
                                <x-ui.button type="button" variant="outline" wire:click="clearFilters">সব ফিল্টার মুছুন</x-ui.button>
                            @else
                                <x-ui.button type="button" variant="outline" wire:click="$set('includeUnavailable', true)">বিরতিতে থাকা ডোনারও দেখান</x-ui.button>
                            @endif
                            <x-ui.button :href="route('requests.create')">অনুরোধ পোস্ট করুন</x-ui.button>
                        </div>
                    </x-slot:action>
                </x-site.empty-state>
            @else
                <ul class="grid gap-3 md:grid-cols-2">
                    @foreach ($this->donors as $donor)
                        <li wire:key="donor-{{ $donor->id }}"><x-site.donor-card :donor="$donor" /></li>
                    @endforeach
                </ul>
                <div class="mt-6">{{ $this->donors->links(data: ['scrollTo' => '#results-heading']) }}</div>
            @endif
        </div>
    </section>
</div>
