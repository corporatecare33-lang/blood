@php
    $bn = fn ($n) => \App\Support\Bn::num($n);
    $super = auth()->user()->isSuperAdmin();
    $page = $this->donors;
    $ids = $page->pluck('id')->map(fn ($id) => (string) $id)->values();
@endphp

<div>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="relative w-full sm:w-72">
            <x-lucide-search class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
            <label for="d-q" class="sr-only">নাম, মোবাইল বা ইমেইল দিয়ে খুঁজুন</label>
            <x-ui.input compact id="d-q" type="search" wire:model.live.debounce.400ms="q" placeholder="নাম, মোবাইল বা ইমেইল…" class="pl-8" />
        </div>
        <label for="d-group" class="sr-only">রক্তের গ্রুপ</label>
        <x-ui.select compact id="d-group" wire:model.live="group" placeholder="সব গ্রুপ" class="w-32">
            @foreach (\App\Enums\BloodGroup::cases() as $g)<option value="{{ $g->slug() }}">{{ $g->value }}</option>@endforeach
        </x-ui.select>
        <label for="d-district" class="sr-only">জেলা</label>
        <x-ui.select compact id="d-district" wire:model.live="district" placeholder="সব জেলা" class="w-36">
            @foreach ($allDistricts as $d)<option value="{{ $d->id }}">{{ $d->bn_name }}</option>@endforeach
        </x-ui.select>
        <label for="d-status" class="sr-only">অবস্থা</label>
        <x-ui.select compact id="d-status" wire:model.live="status" placeholder="সব অবস্থা" class="w-44">
            <option value="eligible">এখন দিতে পারবেন</option>
            <option value="unavailable">প্রস্তুত নন</option>
            <option value="active">সক্রিয়</option>
            <option value="inactive">নিষ্ক্রিয়</option>
        </x-ui.select>
        @if ($filtering)
            <x-ui.button type="button" variant="ghost" size="sm" wire:click="clearFilters"><x-lucide-x aria-hidden="true" />ফিল্টার মুছুন</x-ui.button>
        @endif
        <x-ui.button type="button" variant="outline" size="sm" class="shadow-xs sm:ml-auto" wire:click="export" wire:loading.attr="disabled" wire:target="export">
            <x-lucide-download aria-hidden="true" />{{ $selected ? 'বাছাই করা '.$bn(count($selected)).' জন' : 'CSV' }} এক্সপোর্ট
        </x-ui.button>
    </div>

    {{-- Bulk bar --}}
    @if ($selected)
        <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-crimson/20 bg-crimson-soft/60 px-3 py-2 text-sm">
            <span class="font-medium">{{ $bn(count($selected)) }} জন বাছাই করা হয়েছে</span>
            <span class="mx-1 h-4 w-px bg-crimson/20" aria-hidden="true"></span>
            <x-admin.confirm action="bulkSetActive(false)" :danger="false" confirm="নিষ্ক্রিয় করুন"
                title="{{ $bn(count($selected)) }} জন ডোনারকে নিষ্ক্রিয় করবেন?" message="তাঁরা ডোনার তালিকায় দেখাবেন না এবং লগইন করতে পারবেন না। পরে আবার সক্রিয় করা যাবে।"
                class="inline-flex h-8 items-center gap-1.5 rounded-md bg-white px-2.5 font-medium shadow-xs hover:bg-muted"><x-lucide-user-x class="size-4" aria-hidden="true" />নিষ্ক্রিয়</x-admin.confirm>
            <button type="button" wire:click="bulkSetActive(true)" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-white px-2.5 font-medium shadow-xs hover:bg-muted"><x-lucide-user-check class="size-4" aria-hidden="true" />সক্রিয়</button>
            @if ($super)
                <x-admin.confirm action="bulkDelete()" confirm="স্থায়ীভাবে মুছুন"
                    title="{{ $bn(count($selected)) }} জন ডোনারকে মুছবেন?" message="তাঁদের অ্যাকাউন্ট ও সব তথ্য স্থায়ীভাবে মুছে যাবে। এটি আর ফেরানো যাবে না।"
                    class="inline-flex h-8 items-center gap-1.5 rounded-md bg-white px-2.5 font-medium text-destructive shadow-xs hover:bg-crimson-soft"><x-lucide-trash-2 class="size-4" aria-hidden="true" />মুছুন</x-admin.confirm>
            @endif
            <button type="button" wire:click="$set('selected', [])" class="ml-auto text-sm font-medium text-ink-soft hover:text-ink">বাছাই বাতিল</button>
        </div>
    @endif

    {{-- Table --}}
    <div class="admin-card mt-4 overflow-hidden">
        <div class="relative overflow-x-auto" wire:loading.class="opacity-60" wire:target="q,group,district,status,sortBy,clearFilters,previousPage,nextPage,gotoPage">
            <table class="ui-table min-w-[56rem]">
                <caption class="sr-only">নিবন্ধিত ডোনার</caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-10">
                            <input type="checkbox" class="ui-checkbox" aria-label="এই পাতার সবাইকে বাছাই করুন"
                                x-data="{ ids: @js($ids) }"
                                x-effect="let s = ($wire.selected || []).map(String); $el.checked = ids.length > 0 && ids.every(i => s.includes(i)); $el.indeterminate = ! $el.checked && s.length > 0"
                                x-on:click="$wire.set('selected', $el.checked ? ids : [])" @disabled($ids->isEmpty())>
                        </th>
                        <x-admin.sort-header column="name" :sort="$sort" :dir="$dir">ডোনার</x-admin.sort-header>
                        <th scope="col">গ্রুপ</th>
                        <th scope="col">এলাকা</th>
                        <x-admin.sort-header column="last_donated_at" :sort="$sort" :dir="$dir">শেষ রক্তদান</x-admin.sort-header>
                        <th scope="col">অবস্থা</th>
                        <x-admin.sort-header column="created_at" :sort="$sort" :dir="$dir">নিবন্ধন</x-admin.sort-header>
                        <th scope="col" class="w-12"><span class="sr-only">কাজ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($page as $donor)
                        @php($wait = $donor->daysUntilEligible())
                        <tr wire:key="donor-{{ $donor->id }}" data-selected="{{ in_array((string) $donor->id, array_map('strval', $selected), true) ? 'true' : 'false' }}">
                            <td><input type="checkbox" class="ui-checkbox" wire:model.live="selected" value="{{ $donor->id }}" aria-label="{{ $donor->name }} বাছাই করুন"></td>
                            <td>
                                <button type="button" wire:click="edit({{ $donor->id }})" class="group flex items-center gap-3 text-left">
                                    <x-ui.avatar :user="$donor" size="size-9 text-xs" />
                                    <span class="min-w-0">
                                        <span class="block font-medium group-hover:text-crimson group-hover:underline">{{ $donor->name }}</span>
                                        <span class="block text-xs text-muted-foreground tabular-nums" lang="en">{{ $donor->mobile }}</span>
                                    </span>
                                </button>
                            </td>
                            <td><span class="group-glyph inline-flex h-7 min-w-10 items-center justify-center rounded-md bg-crimson-soft px-1.5 text-sm text-crimson-deep" lang="en">{{ $donor->blood_group->value }}</span></td>
                            <td>
                                <span class="block">{{ $donor->district->bn_name }}</span>
                                @if ($donor->thana)<span class="block text-xs text-muted-foreground">{{ $donor->thana->bn_name ?? $donor->thana->name }}</span>@endif
                            </td>
                            <td class="whitespace-nowrap">{{ $donor->last_donated_at ? \App\Support\Bn::date($donor->last_donated_at) : '—' }}</td>
                            <td>
                                @if (! $donor->is_active)
                                    <x-ui.badge><x-lucide-ban class="size-3" aria-hidden="true" />নিষ্ক্রিয়</x-ui.badge>
                                @elseif (! $donor->is_available)
                                    <x-ui.badge>প্রস্তুত নন</x-ui.badge>
                                @elseif ($wait > 0)
                                    <x-ui.badge variant="warning"><x-lucide-clock class="size-3" aria-hidden="true" />{{ $bn($wait) }} দিন পর</x-ui.badge>
                                @else
                                    <x-ui.badge variant="success"><x-lucide-circle-check class="size-3" aria-hidden="true" />দিতে পারবেন</x-ui.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-muted-foreground">{{ \App\Support\Bn::date($donor->created_at) }}</td>
                            <td class="text-right">
                                <x-ui.dropdown :label="$donor->name.' — আরও কাজ'">
                                    <button type="button" class="ui-menu-item" wire:click="edit({{ $donor->id }})"><x-lucide-pencil aria-hidden="true" />দেখুন ও সম্পাদনা</button>
                                    <a href="tel:{{ $donor->mobile }}" class="ui-menu-item"><x-lucide-phone aria-hidden="true" />কল করুন</a>
                                    <div class="-mx-1 my-1 h-px bg-border"></div>
                                    @if ($donor->is_active)
                                        <x-admin.confirm action="setActive({{ $donor->id }}, false)" :danger="false" confirm="নিষ্ক্রিয় করুন" class="ui-menu-item"
                                            title="{{ $donor->name }}-কে নিষ্ক্রিয় করবেন?" message="তিনি ডোনার তালিকায় দেখাবেন না এবং লগইন করতে পারবেন না। পরে আবার সক্রিয় করা যাবে।">
                                            <x-lucide-user-x aria-hidden="true" />নিষ্ক্রিয় করুন
                                        </x-admin.confirm>
                                    @else
                                        <button type="button" class="ui-menu-item" wire:click="setActive({{ $donor->id }}, true)"><x-lucide-user-check aria-hidden="true" />আবার সক্রিয় করুন</button>
                                    @endif
                                    @if ($super)
                                        <x-admin.confirm action="delete({{ $donor->id }})" class="ui-menu-item is-danger" confirm="স্থায়ীভাবে মুছুন"
                                            title="{{ $donor->name }}-কে মুছবেন?" message="তাঁর অ্যাকাউন্ট ও সব তথ্য স্থায়ীভাবে মুছে যাবে। এটি আর ফেরানো যাবে না।">
                                            <x-lucide-trash-2 aria-hidden="true" />মুছুন
                                        </x-admin.confirm>
                                    @endif
                                </x-ui.dropdown>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($page->isEmpty())
            <x-admin.empty icon="users" :title="$filtering ? 'এই ফিল্টারে কোনো ডোনার নেই' : 'এখনো কোনো ডোনার নিবন্ধন করেননি'" class="border-t border-border">
                @if ($filtering)ফিল্টার বদলান বা মুছে আবার দেখুন।@endif
            </x-admin.empty>
        @endif
        <x-admin.pagination :paginator="$page" unit="জন" />
    </div>

    {{-- Edit sheet --}}
    <x-ui.dialog model="showEditor" side="right" width="max-w-xl" form="save" title="ডোনারের তথ্য" description="সংরক্ষণ করলে সাথে সাথে সাইটে দেখাবে।">
        @if ($editingId)
            <div class="grid gap-5">
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 rounded-lg border border-border bg-muted/40 px-4 py-3">
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium">
                        <x-ui.switch wire:model="is_active" />সক্রিয় অ্যাকাউন্ট
                    </label>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium">
                        <x-ui.switch wire:model="is_available" />রক্ত দিতে প্রস্তুত
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="নাম" for="e-name" required error="name" class="sm:col-span-2">
                        <x-ui.input compact id="e-name" wire:model="name" error="name" required />
                    </x-ui.field>
                    <x-ui.field label="মোবাইল নম্বর" for="e-mobile" required error="mobile">
                        <x-ui.input compact id="e-mobile" type="tel" inputmode="numeric" wire:model="mobile" error="mobile" lang="en" required />
                    </x-ui.field>
                    <x-ui.field label="ইমেইল" for="e-email" error="email">
                        <x-ui.input compact id="e-email" type="email" wire:model="email" error="email" lang="en" />
                    </x-ui.field>
                    <x-ui.field label="রক্তের গ্রুপ" for="e-group" required error="blood_group">
                        <x-ui.select compact id="e-group" wire:model="blood_group" error="blood_group">
                            @foreach (\App\Enums\BloodGroup::cases() as $g)<option value="{{ $g->value }}">{{ $g->value }}</option>@endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="লিঙ্গ" for="e-gender" required error="gender">
                        <x-ui.select compact id="e-gender" wire:model="gender" error="gender">
                            @foreach (\App\Enums\Gender::cases() as $g)<option value="{{ $g->value }}">{{ $g->label() }}</option>@endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="পেশা" for="e-occ" required error="occupation">
                        <x-ui.select compact id="e-occ" wire:model="occupation" error="occupation">
                            @foreach (\App\Enums\Occupation::cases() as $o)<option value="{{ $o->value }}">{{ $o->label() }}</option>@endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="জন্ম তারিখ" for="e-dob" required error="date_of_birth">
                        <x-ui.input compact id="e-dob" type="date" wire:model="date_of_birth" error="date_of_birth" />
                    </x-ui.field>
                    <x-ui.field label="শেষ রক্তদান" for="e-last" error="last_donated_at" hint="জানা না থাকলে ফাঁকা রাখুন।" class="sm:col-span-2">
                        <x-ui.input compact id="e-last" type="date" wire:model="last_donated_at" error="last_donated_at" max="{{ now()->toDateString() }}" class="sm:max-w-[calc(50%-0.5rem)]" />
                    </x-ui.field>
                </div>

                <fieldset class="grid gap-4 border-t border-border pt-5 sm:grid-cols-3">
                    <legend class="sr-only">এলাকা</legend>
                    <x-ui.field label="বিভাগ" for="e-division" required error="division_id">
                        <x-ui.select compact id="e-division" wire:model.live="division_id" error="division_id" placeholder="বেছে নিন">
                            @foreach ($this->divisions as $d)<option value="{{ $d->id }}">{{ $d->bn_name }}</option>@endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="জেলা" for="e-district" required error="district_id">
                        <x-ui.select compact id="e-district" wire:model.live="district_id" error="district_id" placeholder="বেছে নিন" :disabled="! $division_id">
                            @foreach ($this->districts as $d)<option value="{{ $d->id }}">{{ $d->bn_name }}</option>@endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="থানা" for="e-thana" error="thana_id">
                        <x-ui.select compact id="e-thana" wire:model="thana_id" error="thana_id" placeholder="বেছে নিন" :disabled="! $district_id">
                            @foreach ($this->thanas as $t)<option value="{{ $t->id }}">{{ $t->bn_name ?? $t->name }}</option>@endforeach
                        </x-ui.select>
                    </x-ui.field>
                    <x-ui.field label="ঠিকানা" for="e-address" error="address" class="sm:col-span-3">
                        <x-ui.input compact id="e-address" wire:model="address" error="address" />
                    </x-ui.field>
                </fieldset>
            </div>
        @endif

        <x-slot:footer>
            <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false">বাতিল</x-ui.button>
            <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save"><x-ui.spinner wire:loading wire:target="save" />সংরক্ষণ করুন</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
