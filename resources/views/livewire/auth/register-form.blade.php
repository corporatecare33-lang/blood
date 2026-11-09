<form wire:submit="register" novalidate class="grid gap-4">
    <fieldset class="card grid gap-4 p-5 sm:p-6">
        <legend class="sr-only">আপনার তথ্য</legend>

        <x-ui.field label="পুরো নাম" for="name" required>
            <x-ui.input id="name" wire:model.blur="name" autocomplete="name" placeholder="যেমন: মো. রাহিম উদ্দিন" required />
        </x-ui.field>

        <x-ui.field label="ফোন নম্বর" for="mobile" required hint="এই নম্বর দিয়েই লগইন করবেন।">
            <x-ui.input id="mobile" type="tel" inputmode="numeric" wire:model.blur="mobile" autocomplete="tel-national" placeholder="01XXXXXXXXX" maxlength="14" required />
        </x-ui.field>

        <div class="grid gap-1.5" role="radiogroup" aria-labelledby="bg-label" @error('blood_group') aria-describedby="blood_group-error" @enderror>
            <p id="bg-label" class="text-[0.9375rem] font-medium">রক্তের গ্রুপ <span class="text-crimson" aria-hidden="true">*</span></p>
            <div class="grid grid-cols-4 gap-2">
                @foreach (\App\Enums\BloodGroup::cases() as $group)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="blood_group" value="{{ $group->value }}" class="peer sr-only">
                        <span lang="en" class="group-glyph flex h-12 items-center justify-center rounded-lg border border-crimson/40 bg-white text-xl text-crimson transition-colors hover:border-crimson
                                     peer-checked:border-crimson peer-checked:bg-crimson peer-checked:text-white peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-marigold">{{ $group->value }}</span>
                    </label>
                @endforeach
            </div>
            @error('blood_group')
                <p id="blood_group-error" class="flex items-start gap-1.5 text-sm font-medium text-destructive"><x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.field label="লিঙ্গ" for="gender" required>
                <x-ui.select id="gender" wire:model="gender" placeholder="বেছে নিন" required>
                    @foreach (\App\Enums\Gender::cases() as $g)
                        <option value="{{ $g->value }}">{{ $g->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="জন্মতারিখ" for="date_of_birth" required hint="বয়স কমপক্ষে ১৮ বছর।">
                <x-ui.input id="date_of_birth" type="date" wire:model.blur="date_of_birth" max="{{ now()->subYears(18)->toDateString() }}" autocomplete="bday" required />
            </x-ui.field>
            <x-ui.field label="পেশা" for="occupation" required>
                <x-ui.select id="occupation" wire:model="occupation" placeholder="বেছে নিন" required>
                    @foreach (\App\Enums\Occupation::cases() as $o)
                        <option value="{{ $o->value }}">{{ $o->label() }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="শেষ রক্তদান" for="last_donated_at" hint="কখনো না দিয়ে থাকলে খালি রাখুন।">
                <x-ui.input id="last_donated_at" type="date" wire:model.blur="last_donated_at" max="{{ now()->toDateString() }}" />
            </x-ui.field>
        </div>
    </fieldset>

    <fieldset class="card grid gap-4 p-5 sm:p-6">
        <legend class="float-left mb-1 font-sans text-lg font-bold">ঠিকানা</legend>
        <p class="-mt-3 clear-both text-sm text-ink-soft">মানুষ জেলা দিয়ে খোঁজেন, তাই এখন যেখানে থাকেন সেটি দিন।</p>
        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.field label="বিভাগ" for="division_id" required>
                <x-ui.select id="division_id" wire:model.live="division_id" placeholder="বেছে নিন" required>
                    @foreach ($this->divisions as $division)
                        <option value="{{ $division->id }}">{{ $division->bn_name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="জেলা" for="district_id" required>
                <x-ui.select id="district_id" wire:model.live="district_id" :placeholder="$division_id ? 'বেছে নিন' : 'আগে বিভাগ'" :disabled="! $division_id" required>
                    @foreach ($this->districts as $district)
                        <option value="{{ $district->id }}">{{ $district->bn_name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
            <x-ui.field label="থানা" for="thana_id">
                <x-ui.select id="thana_id" wire:model="thana_id" :placeholder="$district_id ? 'বেছে নিন' : 'আগে জেলা'" :disabled="! $district_id">
                    @foreach ($this->thanas as $thana)
                        <option value="{{ $thana->id }}">{{ $thana->bn_name ?? $thana->name }}</option>
                    @endforeach
                </x-ui.select>
            </x-ui.field>
        </div>
        <x-ui.field label="বিস্তারিত ঠিকানা" for="address" hint="ঐচ্ছিক। অন্য সদস্যরা দেখতে পাবেন না।">
            <x-ui.input id="address" wire:model.blur="address" autocomplete="street-address" placeholder="যেমন: মধুখালী, ফরিদপুর" />
        </x-ui.field>
    </fieldset>

    <fieldset class="card grid gap-4 p-5 sm:p-6">
        <legend class="float-left mb-1 font-sans text-lg font-bold">পাসওয়ার্ড</legend>
        <div class="clear-both grid gap-4 sm:grid-cols-2">
            <x-ui.field label="পাসওয়ার্ড" for="password" required hint="কমপক্ষে ৮ অক্ষর।">
                <x-ui.input id="password" type="password" wire:model="password" autocomplete="new-password" required />
            </x-ui.field>
            <x-ui.field label="আবার পাসওয়ার্ড" for="password_confirmation" required>
                <x-ui.input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required />
            </x-ui.field>
        </div>
    </fieldset>

    <div class="card grid gap-4 p-5 sm:p-6">
        <label class="flex cursor-pointer items-center gap-3 text-[1.0625rem] font-medium">
            <input type="checkbox" wire:model="is_available" class="size-5 shrink-0 accent-crimson">
            আমি রক্ত দিতে প্রস্তুত আছি
        </label>

        <details class="group rounded-lg bg-muted px-4 py-3">
            <summary class="flex cursor-pointer list-none items-center justify-between font-semibold [&::-webkit-details-marker]:hidden">
                আমাদের শর্তসমূহ পড়ুন
                <x-lucide-chevron-down class="size-5 text-crimson transition-transform group-open:rotate-180" aria-hidden="true" />
            </summary>
            <ul class="mt-3 space-y-2">
                @foreach (\App\Support\Site::conditions() as $condition)
                    <li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rotate-45 bg-crimson" aria-hidden="true"></span>{{ $condition }}</li>
                @endforeach
            </ul>
        </details>

        <label class="flex cursor-pointer items-start gap-3">
            <input type="checkbox" wire:model="agree" class="mt-1 size-5 shrink-0 accent-crimson" @error('agree') aria-invalid="true" aria-describedby="agree-error" @enderror>
            <span>আমি ব্লাডবন্ডের শর্তসমূহ ও <a href="{{ route('terms') }}" wire:navigate class="font-medium text-crimson underline">ব্যবহারের শর্তাবলি</a>-তে রাজি</span>
        </label>
        @error('agree')
            <p id="agree-error" class="-mt-2 text-sm font-medium text-destructive">{{ $message }}</p>
        @enderror

        @if ($errors->any())
            <x-ui.alert variant="error">{{ \App\Support\Bn::num($errors->count()) }}টি ঘরে ভুল আছে — লাল চিহ্নিত ঘরগুলো ঠিক করুন।</x-ui.alert>
        @endif

        <x-ui.button class="w-full" size="lg" wire:loading.attr="disabled" wire:target="register">
            <x-ui.spinner wire:loading wire:target="register" />
            <x-lucide-droplet wire:loading.remove wire:target="register" aria-hidden="true" />
            <span wire:loading.remove wire:target="register">রেজিস্টার করুন</span>
            <span wire:loading wire:target="register">রেজিস্টার হচ্ছে…</span>
        </x-ui.button>
        <p class="text-center text-[0.9375rem] text-ink-soft">আগেই নিবন্ধন করেছেন? <a href="{{ route('login') }}" wire:navigate class="font-semibold text-crimson underline">লগইন করুন</a></p>
    </div>
</form>
