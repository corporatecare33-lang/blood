{{--
    Blood request form. Three steps in one panel (what · where & when · who to call) and,
    beside it, a live preview of the exact card that will appear on the public board.
    Date/time is picked with Bangla day chips + an hour list instead of the browser's
    English datetime-local widget; Alpine composes them into `needed_at`.
--}}
<form wire:submit="save" novalidate
      x-data="{
          bn: s => String(s).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]),
          districts: @js($districts->pluck('bn_name', 'id')),
          hours: @js($hours),
          quickDays: @js(array_column($days, 'value')),
          nowHour: {{ now()->hour }},
          choice: '', otherDate: '', time: '',
          init() {
              const [d, t] = ($wire.needed_at || '').split('T');
              if (d) { this.choice = this.quickDays.includes(d) ? d : 'other'; this.otherDate = this.choice === 'other' ? d : ''; }
              if (t) this.time = t.slice(0, 5);
          },
          get date() { return this.choice === 'other' ? this.otherDate : this.choice },
          get neededAt() { return this.date && this.time ? this.date + 'T' + this.time : '' },
          get urgent() { return !! this.neededAt && new Date(this.neededAt) - Date.now() < 864e5 },
          get whenText() {
              if (! this.date) return '';
              const day = new Date(this.date + 'T00:00').toLocaleDateString('bn-BD', { weekday: 'long', day: 'numeric', month: 'long' });
              return this.time ? day + ', ' + this.hours[this.time] : day;
          },
          pastHour(h) { return this.date === this.quickDays[0] && h < this.nowHour },
      }"
      x-effect="$wire.needed_at = neededAt"
      class="grid gap-5 lg:grid-cols-12 lg:items-start lg:gap-6">

    <div class="hidden" aria-hidden="true">
        <label for="website">এটি খালি রাখুন</label>
        <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="card lg:col-span-7 xl:col-span-8">
        {{-- Step 1 — what is needed --}}
        <fieldset class="rq-step">
            <legend class="rq-step-title"><span class="rq-step-no" aria-hidden="true">১</span>কী রক্ত লাগবে</legend>

            <div class="grid gap-2" role="radiogroup" aria-labelledby="rq-bg-label" @error('blood_group') aria-describedby="rq-bg-error" @enderror>
                <p id="rq-bg-label" class="text-[0.9375rem] font-medium">রক্তের গ্রুপ <span class="text-crimson" aria-hidden="true">*</span><span class="sr-only">(আবশ্যক)</span></p>
                <div class="grid grid-cols-4 gap-2 sm:gap-2.5">
                    @foreach (\App\Enums\BloodGroup::cases() as $g)
                        <label class="cursor-pointer">
                            <input type="radio" name="blood_group" wire:model="blood_group" value="{{ $g->value }}" class="peer sr-only">
                            <span lang="en" @class([
                                'group-glyph relative flex h-14 items-center justify-center rounded-lg border-2 bg-white text-xl text-crimson transition-[background-color,border-color,color,transform] duration-150 active:scale-95 sm:h-16 sm:text-2xl',
                                'hover:border-crimson hover:bg-crimson-soft',
                                'peer-checked:border-crimson peer-checked:bg-crimson peer-checked:text-white peer-checked:shadow-panel',
                                'peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-marigold',
                                'border-destructive' => $errors->has('blood_group'),
                                'border-crimson/25' => ! $errors->has('blood_group'),
                            ])>{{ $g->value }}</span>
                        </label>
                    @endforeach
                </div>
                @error('blood_group')<p id="rq-bg-error" class="flex items-start gap-1.5 text-sm font-medium text-destructive"><x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-[auto_1fr]">
                <div class="grid content-start gap-1.5">
                    <p id="rq-bags-label" class="text-[0.9375rem] font-medium">কত ব্যাগ <span class="text-crimson" aria-hidden="true">*</span></p>
                    <div class="flex h-12 items-center rounded-lg border border-input bg-white" role="group" aria-labelledby="rq-bags-label">
                        <button type="button" x-on:click="$wire.bags = Math.max(1, $wire.bags - 1)" x-bind:disabled="$wire.bags <= 1"
                                class="flex h-full w-12 items-center justify-center rounded-l-lg text-crimson transition-colors hover:bg-crimson-soft disabled:text-ink-soft/40 disabled:hover:bg-transparent"
                                aria-label="এক ব্যাগ কমান"><x-lucide-minus class="size-5" aria-hidden="true" /></button>
                        <output class="min-w-20 flex-1 text-center text-lg font-semibold sm:flex-none" aria-live="polite">
                            <span x-text="bn($wire.bags)">{{ \App\Support\Bn::num($bags) }}</span> ব্যাগ
                        </output>
                        <button type="button" x-on:click="$wire.bags = Math.min(10, $wire.bags + 1)" x-bind:disabled="$wire.bags >= 10"
                                class="flex h-full w-12 items-center justify-center rounded-r-lg text-crimson transition-colors hover:bg-crimson-soft disabled:text-ink-soft/40 disabled:hover:bg-transparent"
                                aria-label="এক ব্যাগ বাড়ান"><x-lucide-plus class="size-5" aria-hidden="true" /></button>
                    </div>
                    @error('bags')<p class="flex items-start gap-1.5 text-sm font-medium text-destructive"><x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}</p>@enderror
                </div>

                <x-ui.field label="রোগীর নাম" for="patient_name" required>
                    <x-ui.input id="patient_name" wire:model="patient_name" maxlength="80" required />
                </x-ui.field>
            </div>
        </fieldset>

        {{-- Step 2 — where and when --}}
        <fieldset class="rq-step">
            <legend class="rq-step-title"><span class="rq-step-no" aria-hidden="true">২</span>কোথায়, কখন</legend>

            <div class="grid gap-4 sm:grid-cols-5">
                <x-ui.field label="হাসপাতাল" for="hospital" required hint="জানা থাকলে ওয়ার্ড বা বেড নম্বরসহ লিখুন।" class="sm:col-span-3">
                    <x-ui.input id="hospital" wire:model="hospital" maxlength="150" required />
                </x-ui.field>
                <x-ui.field label="জেলা" for="district_id" required class="sm:col-span-2">
                    <x-ui.select id="district_id" wire:model="district_id" placeholder="জেলা বেছে নিন" required>
                        @foreach ($districts as $d)
                            <option value="{{ $d->id }}">{{ $d->bn_name }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            </div>

            <div class="grid gap-2" role="group" aria-labelledby="rq-when-label" @error('needed_at') aria-describedby="needed_at-error" @enderror>
                <p id="rq-when-label" class="text-[0.9375rem] font-medium">কবে দরকার <span class="text-crimson" aria-hidden="true">*</span><span class="sr-only">(আবশ্যক)</span></p>
                <div class="grid grid-cols-4 gap-2">
                    @foreach ($days as $day)
                        <label class="cursor-pointer">
                            <input type="radio" name="rq-day" value="{{ $day['value'] }}" x-model="choice" class="peer sr-only">
                            <span class="rq-chip">
                                <span class="font-semibold">{{ $day['name'] }}</span>
                                <span class="text-xs opacity-75">{{ $day['date'] }}</span>
                            </span>
                        </label>
                    @endforeach
                    <label class="cursor-pointer">
                        <input type="radio" name="rq-day" value="other" x-model="choice" class="peer sr-only">
                        <span class="rq-chip">
                            <x-lucide-calendar-days class="size-4" aria-hidden="true" />
                            <span class="text-xs font-semibold sm:text-sm">অন্য দিন</span>
                        </span>
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div x-show="choice === 'other'" x-cloak class="grid gap-1.5">
                        <label for="rq-other-date" class="text-sm text-ink-soft">তারিখ</label>
                        <x-ui.input id="rq-other-date" type="date" x-model="otherDate" :class="$errors->has('needed_at') ? 'border-destructive!' : ''"
                                    min="{{ now()->toDateString() }}" max="{{ now()->addDays(59)->toDateString() }}" />
                    </div>
                    <div class="grid gap-1.5">
                        <label for="rq-time" class="text-sm text-ink-soft">কয়টার সময়</label>
                        <x-ui.select id="rq-time" x-model="time" placeholder="সময় বেছে নিন" :class="$errors->has('needed_at') ? 'border-destructive!' : ''">
                            @foreach ($hours as $value => $label)
                                <option value="{{ $value }}" x-bind:disabled="pastHour({{ (int) $value }})">{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>
                @error('needed_at')<p id="needed_at-error" class="flex items-start gap-1.5 text-sm font-medium text-destructive"><x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}</p>@enderror
            </div>
        </fieldset>

        {{-- Step 3 — who donors should call --}}
        <fieldset class="rq-step">
            <legend class="rq-step-title"><span class="rq-step-no" aria-hidden="true">৩</span>ডোনাররা কাকে কল করবেন</legend>

            @auth
                <p class="-mt-2 flex items-center gap-2 text-sm text-ink-soft"><x-lucide-user-round-check class="size-4 shrink-0 text-leaf" aria-hidden="true" />আপনার অ্যাকাউন্ট থেকে নেওয়া — অন্য কেউ কল ধরলে বদলে দিন।</p>
            @endauth

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="যোগাযোগকারীর নাম" for="contact_name" required>
                    <x-ui.input id="contact_name" wire:model="contact_name" autocomplete="name" maxlength="80" required />
                </x-ui.field>
                <x-ui.field label="মোবাইল নম্বর" for="contact_mobile" required>
                    <div class="relative">
                        <x-lucide-phone class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
                        <x-ui.input id="contact_mobile" type="tel" inputmode="numeric" lang="en" wire:model="contact_mobile" autocomplete="tel-national" placeholder="01XXXXXXXXX" maxlength="14" required class="pl-10" />
                    </div>
                </x-ui.field>
            </div>

            <div class="grid gap-1.5">
                <div class="flex items-baseline justify-between gap-3">
                    <label for="note" class="text-[0.9375rem] font-medium">ডোনারদের জন্য নোট <span class="font-normal text-ink-soft">(ঐচ্ছিক)</span></label>
                    <span class="text-xs text-ink-soft tabular-nums" aria-hidden="true"><span x-text="bn(($wire.note || '').length)">০</span>/৫০০</span>
                </div>
                <x-ui.textarea id="note" wire:model="note" rows="3" maxlength="500" placeholder="যেমন: রোগী ICU-তে আছেন, সকাল ১০টার মধ্যে পৌঁছাতে হবে।" />
                @error('note')<p id="note-error" class="flex items-start gap-1.5 text-sm font-medium text-destructive"><x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}</p>@enderror
            </div>
        </fieldset>
    </div>

    {{-- Live preview + submit --}}
    <aside class="lg:sticky lg:top-24 lg:col-span-5 xl:col-span-4" aria-labelledby="rq-preview-title">
        <div class="overflow-hidden rounded-xl bg-crimson-deep shadow-panel">
            <div class="flex items-center justify-between gap-3 px-5 pt-4 pb-3 text-white">
                <h2 id="rq-preview-title" class="font-display text-lg font-bold text-white">বোর্ডে যেভাবে দেখাবে</h2>
                <span class="flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium"><span class="size-1.5 rounded-full bg-white motion-safe:animate-pulse" aria-hidden="true"></span>লাইভ</span>
            </div>

            {{-- Mirrors components/site/request-card — keep the two in step. --}}
            <article class="mx-2 flex gap-3.5 rounded-lg bg-card p-4" aria-hidden="true">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-crimson text-white">
                    <x-lucide-droplet class="size-5 fill-white" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-sans text-[1.0625rem] leading-snug font-bold">
                        <span x-show="urgent" class="text-crimson">জরুরি:</span>
                        <span class="group-glyph text-crimson" lang="en" x-show="$wire.blood_group" x-text="$wire.blood_group"></span><span x-show="! $wire.blood_group" class="rq-blank">গ্রুপ</span> রক্ত প্রয়োজন
                        <span class="font-medium text-ink-soft">· <span x-text="bn($wire.bags)"></span> ব্যাগ</span>
                    </p>
                    <p class="mt-0.5 text-[0.9375rem] text-ink-soft">
                        <span x-text="$wire.hospital || 'হাসপাতাল'" x-bind:class="! $wire.hospital && 'rq-blank'"></span>,
                        <span x-text="districts[$wire.district_id] || 'জেলা'" x-bind:class="! districts[$wire.district_id] && 'rq-blank'"></span>
                    </p>
                    <p class="text-sm text-ink-soft">প্রয়োজন <span class="font-medium text-ink" x-text="whenText || 'দিন ও সময়'" x-bind:class="! whenText && 'rq-blank'"></span></p>
                    <p class="mt-1.5 text-[0.9375rem]">রোগী: <span x-text="$wire.patient_name || 'নাম'" x-bind:class="! $wire.patient_name && 'rq-blank'"></span></p>
                    <p x-show="$wire.note" class="mt-1 line-clamp-2 text-[0.9375rem] break-words text-ink-soft" x-text="$wire.note"></p>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm text-ink-soft">যোগাযোগ: <span x-text="$wire.contact_name || 'নাম'" x-bind:class="! $wire.contact_name && 'rq-blank'"></span></span>
                        <span class="inline-flex h-9 items-center gap-2 rounded-lg bg-primary px-3 text-sm font-semibold text-white"><x-lucide-phone class="size-[1.1em]" />কল করুন</span>
                    </div>
                </div>
            </article>

            <p class="flex gap-2 px-5 pt-3 pb-4 text-sm text-white/90">
                <x-lucide-eye class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                <span>"কল করুন" চাপলে <span x-show="$wire.contact_mobile" lang="en" class="font-semibold text-white" x-text="$wire.contact_mobile"></span><span x-show="! $wire.contact_mobile">আপনার</span> নম্বরে কল যাবে — নম্বরটি সবাই দেখতে পাবেন।</span>
            </p>
        </div>

        <div class="card mt-4 p-5">
            <ul class="space-y-2.5 text-[0.9375rem]">
                <li class="flex gap-2.5"><x-lucide-bus class="mt-0.5 size-5 shrink-0 text-crimson" aria-hidden="true" />শর্ত অনুযায়ী ডোনারের যাতায়াত ও খাবারের খরচ আপনি দেবেন।</li>
                <li class="flex gap-2.5"><x-lucide-clock class="mt-0.5 size-5 shrink-0 text-crimson" aria-hidden="true" />প্রয়োজনের সময়ের এক দিন পর অনুরোধটি তালিকা থেকে সরে যাবে।</li>
            </ul>

            @if ($errors->any())
                <x-ui.alert variant="error" class="mt-4">{{ \App\Support\Bn::num($errors->count()) }}টি ঘরে ভুল আছে — লাল চিহ্নিত ঘরগুলো ঠিক করুন।</x-ui.alert>
            @endif

            <x-ui.button class="mt-5 w-full" size="lg" wire:loading.attr="disabled" wire:target="save">
                <x-ui.spinner wire:loading wire:target="save" />
                <x-lucide-send wire:loading.remove wire:target="save" aria-hidden="true" />
                <span wire:loading.remove wire:target="save">অনুরোধ পোস্ট করুন</span>
                <span wire:loading wire:target="save">পোস্ট হচ্ছে…</span>
            </x-ui.button>
        </div>
    </aside>
</form>
