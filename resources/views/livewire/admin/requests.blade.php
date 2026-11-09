@use('App\Enums\RequestStatus')
@php
    $bn = fn ($n) => \App\Support\Bn::num($n);
    $page = $this->requests;
    $ids = $page->pluck('id')->map(fn ($id) => (string) $id)->values();
    $badge = fn ($s) => match ($s) { RequestStatus::Open => 'danger', RequestStatus::Fulfilled => 'success', default => 'neutral' };
@endphp

<div>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2">
        <x-ui.tabs model="status" :value="$status" :options="$tabs" label="অবস্থা অনুযায়ী ফিল্টার" class="w-full sm:w-auto" />
        <div class="relative w-full sm:ml-auto sm:w-64">
            <x-lucide-search class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
            <label for="r-q" class="sr-only">রোগী, হাসপাতাল বা মোবাইল দিয়ে খুঁজুন</label>
            <x-ui.input compact id="r-q" type="search" wire:model.live.debounce.400ms="q" placeholder="রোগী, হাসপাতাল, মোবাইল…" class="pl-8" />
        </div>
        <label for="r-group" class="sr-only">রক্তের গ্রুপ</label>
        <x-ui.select compact id="r-group" wire:model.live="group" placeholder="সব গ্রুপ" class="w-32">
            @foreach (\App\Enums\BloodGroup::cases() as $g)<option value="{{ $g->slug() }}">{{ $g->value }}</option>@endforeach
        </x-ui.select>
    </div>

    {{-- Bulk bar --}}
    @if ($selected)
        <div class="mt-3 flex flex-wrap items-center gap-2 rounded-lg border border-crimson/20 bg-crimson-soft/60 px-3 py-2 text-sm">
            <span class="font-medium">{{ $bn(count($selected)) }}টি বাছাই করা হয়েছে</span>
            <span class="mx-1 h-4 w-px bg-crimson/20" aria-hidden="true"></span>
            <button type="button" wire:click="bulkStatus('fulfilled')" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-white px-2.5 font-medium shadow-xs hover:bg-muted"><x-lucide-circle-check class="size-4" aria-hidden="true" />ডোনার পাওয়া গেছে</button>
            <button type="button" wire:click="bulkStatus('closed')" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-white px-2.5 font-medium shadow-xs hover:bg-muted"><x-lucide-circle-x class="size-4" aria-hidden="true" />বন্ধ করুন</button>
            <x-admin.confirm action="bulkDelete()" confirm="মুছুন" title="{{ $bn(count($selected)) }}টি অনুরোধ মুছবেন?" message="অনুরোধগুলো সাইট থেকে স্থায়ীভাবে মুছে যাবে।"
                class="inline-flex h-8 items-center gap-1.5 rounded-md bg-white px-2.5 font-medium text-destructive shadow-xs hover:bg-crimson-soft"><x-lucide-trash-2 class="size-4" aria-hidden="true" />মুছুন</x-admin.confirm>
            <button type="button" wire:click="$set('selected', [])" class="ml-auto text-sm font-medium text-ink-soft hover:text-ink">বাছাই বাতিল</button>
        </div>
    @endif

    <div class="admin-card mt-4 overflow-hidden">
        <div class="relative overflow-x-auto" wire:loading.class="opacity-60" wire:target="status,q,group,previousPage,nextPage,gotoPage">
            <table class="ui-table min-w-[60rem]">
                <caption class="sr-only">রক্তের অনুরোধ</caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-10">
                            <input type="checkbox" class="ui-checkbox" aria-label="এই পাতার সব অনুরোধ বাছাই করুন"
                                x-data="{ ids: @js($ids) }"
                                x-effect="let s = ($wire.selected || []).map(String); $el.checked = ids.length > 0 && ids.every(i => s.includes(i)); $el.indeterminate = ! $el.checked && s.length > 0"
                                x-on:click="$wire.set('selected', $el.checked ? ids : [])" @disabled($ids->isEmpty())>
                        </th>
                        <th scope="col">গ্রুপ</th>
                        <th scope="col">রোগী ও হাসপাতাল</th>
                        <th scope="col">কখন দরকার</th>
                        <th scope="col">যোগাযোগ</th>
                        <th scope="col">অবস্থা</th>
                        <th scope="col">পোস্ট</th>
                        <th scope="col" class="w-12"><span class="sr-only">কাজ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($page as $r)
                        @php($past = $r->needed_at->isPast())
                        <tr wire:key="r-{{ $r->id }}" data-selected="{{ in_array((string) $r->id, array_map('strval', $selected), true) ? 'true' : 'false' }}">
                            <td><input type="checkbox" class="ui-checkbox" wire:model.live="selected" value="{{ $r->id }}" aria-label="{{ $r->patient_name }}-এর অনুরোধ বাছাই করুন"></td>
                            <td>
                                <span class="group-glyph inline-flex h-7 min-w-10 items-center justify-center rounded-md bg-crimson-soft px-1.5 text-sm text-crimson-deep" lang="en">{{ $r->blood_group->value }}</span>
                                <span class="mt-1 block text-xs text-muted-foreground">{{ $bn($r->bags) }} ব্যাগ</span>
                            </td>
                            <td class="max-w-64">
                                <button type="button" wire:click="edit({{ $r->id }})" class="text-left font-medium hover:text-crimson hover:underline">{{ $r->patient_name }}</button>
                                <span class="block truncate text-xs text-muted-foreground">{{ $r->hospital }}, {{ $r->district->bn_name }}</span>
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="block">{{ \App\Support\Bn::dateTime($r->needed_at) }}</span>
                                @if ($r->status === RequestStatus::Open)
                                    @if ($past)
                                        <span class="text-xs text-muted-foreground">সময় পেরিয়ে গেছে</span>
                                    @elseif ($r->isUrgent())
                                        <span class="text-xs font-semibold text-destructive">জরুরি · {{ \App\Support\Bn::ago($r->needed_at) }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="block">{{ $r->contact_name }}</span>
                                <a href="tel:{{ $r->contact_mobile }}" class="text-xs text-muted-foreground tabular-nums hover:text-crimson" lang="en">{{ $r->contact_mobile }}</a>
                            </td>
                            <td><x-ui.badge :variant="$badge($r->status)">{{ $r->status->label() }}</x-ui.badge></td>
                            <td class="whitespace-nowrap text-muted-foreground">{{ \App\Support\Bn::ago($r->created_at) }}</td>
                            <td class="text-right">
                                <x-ui.dropdown :label="$r->patient_name.'-এর অনুরোধ — আরও কাজ'">
                                    <button type="button" class="ui-menu-item" wire:click="edit({{ $r->id }})"><x-lucide-pencil aria-hidden="true" />দেখুন ও সম্পাদনা</button>
                                    <a href="tel:{{ $r->contact_mobile }}" class="ui-menu-item"><x-lucide-phone aria-hidden="true" />যোগাযোগকারীকে কল</a>
                                    <div class="-mx-1 my-1 h-px bg-border"></div>
                                    <p class="px-2 py-1.5 text-xs font-medium text-muted-foreground">অবস্থা বদলান</p>
                                    @foreach (RequestStatus::cases() as $s)
                                        @continue($s === $r->status)
                                        <button type="button" class="ui-menu-item" wire:click="setStatus({{ $r->id }}, '{{ $s->value }}')">
                                            <x-dynamic-component :component="'lucide-'.match ($s) { RequestStatus::Open => 'rotate-ccw', RequestStatus::Fulfilled => 'circle-check', RequestStatus::Closed => 'circle-x' }" aria-hidden="true" />{{ $s->label() }}
                                        </button>
                                    @endforeach
                                    <div class="-mx-1 my-1 h-px bg-border"></div>
                                    <x-admin.confirm action="delete({{ $r->id }})" class="ui-menu-item is-danger" confirm="মুছুন"
                                        title="{{ $r->patient_name }}-এর অনুরোধ মুছবেন?" message="অনুরোধটি সাইট থেকে স্থায়ীভাবে মুছে যাবে।">
                                        <x-lucide-trash-2 aria-hidden="true" />মুছুন
                                    </x-admin.confirm>
                                </x-ui.dropdown>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($page->isEmpty())
            <x-admin.empty icon="clipboard-list" title="এখানে কোনো অনুরোধ নেই" class="border-t border-border">
                @if ($q !== '' || $group !== '')ফিল্টার বদলে আবার দেখুন।@else নতুন অনুরোধ পোস্ট হলে এখানে দেখাবে।@endif
            </x-admin.empty>
        @endif
        <x-admin.pagination :paginator="$page" unit="টি" />
    </div>

    {{-- Edit sheet --}}
    <x-ui.dialog model="showEditor" side="right" width="max-w-xl" form="save" title="অনুরোধের তথ্য" description="সংরক্ষণ করলে সাথে সাথে সাইটে দেখাবে।">
        @if ($editingId)
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.field label="অবস্থা" for="q-status" required error="request_status" class="sm:col-span-2">
                    <x-ui.select compact id="q-status" wire:model="request_status" error="request_status">
                        @foreach (RequestStatus::cases() as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="রোগীর নাম" for="q-patient" required error="patient_name" class="sm:col-span-2">
                    <x-ui.input compact id="q-patient" wire:model="patient_name" error="patient_name" />
                </x-ui.field>
                <x-ui.field label="রক্তের গ্রুপ" for="q-group" required error="blood_group">
                    <x-ui.select compact id="q-group" wire:model="blood_group" error="blood_group">
                        @foreach (\App\Enums\BloodGroup::cases() as $g)<option value="{{ $g->value }}">{{ $g->value }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="ব্যাগ" for="q-bags" required error="bags">
                    <x-ui.input compact id="q-bags" type="number" min="1" max="10" wire:model="bags" error="bags" />
                </x-ui.field>
                <x-ui.field label="কখন দরকার" for="q-needed" required error="needed_at">
                    <x-ui.input compact id="q-needed" type="datetime-local" wire:model="needed_at" error="needed_at" />
                </x-ui.field>
                <x-ui.field label="জেলা" for="q-district" required error="district_id">
                    <x-ui.select compact id="q-district" wire:model="district_id" error="district_id">
                        @foreach ($allDistricts as $d)<option value="{{ $d->id }}">{{ $d->bn_name }}</option>@endforeach
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="হাসপাতাল" for="q-hospital" required error="hospital" class="sm:col-span-2">
                    <x-ui.input compact id="q-hospital" wire:model="hospital" error="hospital" />
                </x-ui.field>
                <x-ui.field label="যোগাযোগকারীর নাম" for="q-cname" required error="contact_name">
                    <x-ui.input compact id="q-cname" wire:model="contact_name" error="contact_name" />
                </x-ui.field>
                <x-ui.field label="যোগাযোগের মোবাইল" for="q-cmobile" required error="contact_mobile">
                    <x-ui.input compact id="q-cmobile" type="tel" inputmode="numeric" wire:model="contact_mobile" error="contact_mobile" lang="en" />
                </x-ui.field>
                <x-ui.field label="নোট" for="q-note" error="note" class="sm:col-span-2">
                    <x-ui.textarea id="q-note" wire:model="note" error="note" rows="3" class="text-sm" />
                </x-ui.field>
            </div>
        @endif

        <x-slot:footer>
            <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false">বাতিল</x-ui.button>
            <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save"><x-ui.spinner wire:loading wire:target="save" />সংরক্ষণ করুন</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
