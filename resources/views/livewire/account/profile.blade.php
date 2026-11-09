@php($wait = $user->daysUntilEligible())
<div class="grid gap-4 lg:grid-cols-12 lg:items-start">
    {{-- Status --}}
    <aside class="lg:col-span-4">
        <div class="card overflow-hidden lg:sticky lg:top-24">
            <div class="flex items-center gap-4 bg-crimson p-5 text-white">
                <span class="group-glyph flex size-16 shrink-0 items-center justify-center rounded-full bg-white text-2xl text-crimson" lang="en">{{ $user->blood_group->value }}</span>
                <div class="min-w-0">
                    <p class="truncate text-lg font-bold">{{ $user->name }}</p>
                    <p class="text-white/85">{{ $user->mobile }}</p>
                </div>
            </div>
            <div class="grid gap-4 p-5">
                <div>
                    @if (! $is_available)
                        <x-ui.badge>তালিকা থেকে লুকানো</x-ui.badge>
                        <p class="mt-2 text-[0.9375rem] text-ink-soft">যাঁরা ডোনার খুঁজছেন তাঁরা আপনাকে দেখতে পাবেন না।</p>
                    @elseif ($wait > 0)
                        <x-ui.badge variant="warning">{{ \App\Support\Bn::num($wait) }} দিন পর দিতে পারবেন</x-ui.badge>
                        <p class="mt-2 text-[0.9375rem] text-ink-soft">{{ \App\Support\Bn::date($user->last_donated_at->copy()->addDays(config('bandhan.donation_gap_days'))) }} থেকে আবার তালিকায় দেখাবেন।</p>
                    @else
                        <x-ui.badge variant="success"><x-lucide-check class="size-3.5" aria-hidden="true" />ডোনার তালিকায় দেখাচ্ছেন</x-ui.badge>
                        <p class="mt-2 text-[0.9375rem] text-ink-soft">{{ $user->district->bn_name }}-এ <span lang="en">{{ $user->blood_group->value }}</span> রক্ত দরকার হলে মানুষ আপনাকে খুঁজে পাবেন।</p>
                    @endif
                </div>
                <div class="grid gap-2">
                    <x-ui.button type="button" wire:click="donatedToday" wire:confirm="আজকের তারিখকে শেষ রক্তদানের তারিখ হিসেবে রাখবেন?">
                        <x-lucide-droplet aria-hidden="true" />আজ রক্ত দিয়েছি
                    </x-ui.button>
                    <x-ui.button type="button" wire:click="toggleAvailability" variant="soft">
                        {{ $is_available ? 'বিরতি নিন — তালিকা থেকে লুকান' : 'আবার তালিকায় দেখান' }}
                    </x-ui.button>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="border-t border-border pt-4">
                    @csrf
                    <button class="inline-flex items-center gap-2 text-[0.9375rem] font-semibold text-ink-soft hover:text-crimson">
                        <x-lucide-log-out class="size-4" aria-hidden="true" />লগআউট
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="grid gap-4 lg:col-span-8">
        <form wire:submit="saveProfile" novalidate aria-labelledby="profile-heading" class="card p-5 sm:p-6">
            <h2 id="profile-heading" class="font-sans text-lg font-bold">ডোনার প্রোফাইল</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <x-ui.field label="পুরো নাম" for="name" required class="sm:col-span-2">
                    <x-ui.input id="name" wire:model="name" autocomplete="name" required />
                </x-ui.field>
                <x-ui.field label="রক্তের গ্রুপ" for="blood_group" required>
                    <x-ui.select id="blood_group" wire:model="blood_group" required>
                        @foreach (\App\Enums\BloodGroup::cases() as $g)<option value="{{ $g->value }}">{{ $g->value }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="শেষ রক্তদান" for="last_donated_at">
                    <x-ui.input id="last_donated_at" type="date" wire:model="last_donated_at" max="{{ now()->toDateString() }}" />
                </x-ui.field>
                <x-ui.field label="লিঙ্গ" for="gender" required>
                    <x-ui.select id="gender" wire:model="gender" required>
                        @foreach (\App\Enums\Gender::cases() as $g)<option value="{{ $g->value }}">{{ $g->label() }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="পেশা" for="occupation" required>
                    <x-ui.select id="occupation" wire:model="occupation" required>
                        @foreach (\App\Enums\Occupation::cases() as $o)<option value="{{ $o->value }}">{{ $o->label() }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="জন্মতারিখ" for="date_of_birth" required>
                    <x-ui.input id="date_of_birth" type="date" wire:model="date_of_birth" required />
                </x-ui.field>
                <x-ui.field label="বিভাগ" for="division_id" required>
                    <x-ui.select id="division_id" wire:model.live="division_id" placeholder="বেছে নিন" required>
                        @foreach ($this->divisions as $d)<option value="{{ $d->id }}">{{ $d->bn_name }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="জেলা" for="district_id" required>
                    <x-ui.select id="district_id" wire:model.live="district_id" placeholder="বেছে নিন" :disabled="! $division_id" required>
                        @foreach ($this->districts as $d)<option value="{{ $d->id }}">{{ $d->bn_name }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="থানা" for="thana_id">
                    <x-ui.select id="thana_id" wire:model="thana_id" placeholder="বেছে নিন" :disabled="! $district_id">
                        @foreach ($this->thanas as $t)<option value="{{ $t->id }}">{{ $t->bn_name ?? $t->name }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="বিস্তারিত ঠিকানা" for="address" class="sm:col-span-2">
                    <x-ui.input id="address" wire:model="address" autocomplete="street-address" />
                </x-ui.field>
            </div>
            <x-ui.button class="mt-5" wire:loading.attr="disabled" wire:target="saveProfile">
                <x-ui.spinner wire:loading wire:target="saveProfile" />প্রোফাইল সংরক্ষণ করুন
            </x-ui.button>
        </form>

        <section aria-labelledby="my-requests-heading" class="card p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="my-requests-heading" class="font-sans text-lg font-bold">আমার রক্তের অনুরোধ</h2>
                <x-ui.button :href="route('requests.create')" size="sm" variant="soft"><x-lucide-plus aria-hidden="true" />নতুন অনুরোধ</x-ui.button>
            </div>
            @if ($this->myRequests->isEmpty())
                <p class="mt-3 text-ink-soft">আপনি এখনো কোনো অনুরোধ পোস্ট করেননি।</p>
            @else
                <ul class="mt-3 divide-y divide-border">
                    @foreach ($this->myRequests as $r)
                        <li wire:key="my-req-{{ $r->id }}" class="flex flex-wrap items-center gap-x-4 gap-y-2 py-3.5">
                            <span class="group-glyph w-12 text-2xl text-crimson" lang="en">{{ $r->blood_group->value }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium">{{ $r->patient_name }} — {{ $r->hospital }}</p>
                                <p class="text-sm text-ink-soft">{{ $r->district->bn_name }}, {{ \App\Support\Bn::dateTime($r->needed_at) }}</p>
                            </div>
                            <x-ui.badge :variant="$r->status === \App\Enums\RequestStatus::Open ? 'danger' : 'neutral'">{{ $r->status->label() }}</x-ui.badge>
                            @if ($r->status === \App\Enums\RequestStatus::Open)
                                <div class="flex gap-2">
                                    <x-ui.button type="button" size="sm" variant="soft" wire:click="setRequestStatus({{ $r->id }}, 'fulfilled')">ডোনার পেয়েছি</x-ui.button>
                                    <x-ui.button type="button" size="sm" variant="ghost" wire:click="setRequestStatus({{ $r->id }}, 'closed')">বন্ধ করুন</x-ui.button>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <form wire:submit="changePassword" novalidate aria-labelledby="pw-heading" class="card p-5 sm:p-6">
            <h2 id="pw-heading" class="font-sans text-lg font-bold">পাসওয়ার্ড পরিবর্তন</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <x-ui.field label="বর্তমান পাসওয়ার্ড" for="current_password" required>
                    <x-ui.input id="current_password" type="password" wire:model="current_password" autocomplete="current-password" required />
                </x-ui.field>
                <x-ui.field label="নতুন পাসওয়ার্ড" for="password" required>
                    <x-ui.input id="password" type="password" wire:model="password" autocomplete="new-password" required />
                </x-ui.field>
                <x-ui.field label="আবার নতুন পাসওয়ার্ড" for="password_confirmation" required>
                    <x-ui.input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required />
                </x-ui.field>
            </div>
            <x-ui.button class="mt-5" variant="secondary" wire:loading.attr="disabled" wire:target="changePassword">পাসওয়ার্ড পরিবর্তন করুন</x-ui.button>
        </form>
    </div>
</div>
