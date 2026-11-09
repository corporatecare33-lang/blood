<div>
    <div class="flex flex-wrap items-center gap-2">
        <label for="t-district" class="sr-only">জেলা</label>
        <x-ui.select compact id="t-district" wire:model.live="district" placeholder="জেলা বেছে নিন" class="w-56">
            @foreach ($districts as $d)<option value="{{ $d->id }}">{{ $d->bn_name }} — {{ $d->name }}</option>@endforeach
        </x-ui.select>
        @if ($district)
            <div class="relative w-full sm:w-60">
                <x-lucide-search class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
                <label for="t-q" class="sr-only">থানা খুঁজুন</label>
                <x-ui.input compact id="t-q" type="search" wire:model.live.debounce.300ms="q" placeholder="থানা খুঁজুন…" class="pl-8" />
            </div>
            <x-ui.button type="button" size="sm" class="sm:ml-auto" wire:click="create"><x-lucide-plus aria-hidden="true" />নতুন থানা</x-ui.button>
        @endif
    </div>

    <div class="admin-card mt-4 overflow-hidden">
        @if (! $district)
            <x-admin.empty icon="map-pin" title="একটি জেলা বেছে নিন">সেই জেলার থানাগুলো দেখা, যোগ ও সম্পাদনা করা যাবে।</x-admin.empty>
        @else
            <div class="relative overflow-x-auto" wire:loading.class="opacity-60" wire:target="district,q">
                <table class="ui-table min-w-[32rem]">
                    <caption class="sr-only">থানার তালিকা</caption>
                    <thead>
                        <tr>
                            <th scope="col">নাম (ইংরেজি)</th>
                            <th scope="col">নাম (বাংলা)</th>
                            <th scope="col" class="text-right">ডোনার</th>
                            <th scope="col" class="w-24"><span class="sr-only">কাজ</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->thanas as $t)
                            <tr wire:key="t-{{ $t->id }}">
                                <td class="font-medium" lang="en">{{ $t->name }}</td>
                                <td>{{ $t->bn_name ?? '—' }}</td>
                                <td class="text-right tabular-nums">{{ \App\Support\Bn::num((int) $t->donors) }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <x-ui.button type="button" size="icon-sm" variant="ghost" wire:click="edit({{ $t->id }})"><x-lucide-pencil aria-hidden="true" /><span class="sr-only">{{ $t->name }} সম্পাদনা</span></x-ui.button>
                                    @if ($t->donors)
                                        <span class="inline-flex size-8 items-center justify-center text-ink-soft/40" title="ডোনার নিবন্ধিত আছেন — মোছা যাবে না"><x-lucide-trash-2 class="size-4" aria-hidden="true" /></span>
                                    @else
                                        <x-admin.confirm action="delete({{ $t->id }})" title="{{ $t->name }} মুছবেন?" message="থানাটি নিবন্ধন ফর্ম থেকে সরে যাবে।"
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-destructive hover:bg-crimson-soft">
                                            <x-lucide-trash-2 class="size-4" aria-hidden="true" /><span class="sr-only">{{ $t->name }} মুছুন</span>
                                        </x-admin.confirm>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($this->thanas->isEmpty())
                <x-admin.empty icon="map-pin" :title="$q !== '' ? 'এই নামে কোনো থানা নেই' : 'এই জেলায় এখনো কোনো থানা নেই'" class="border-t border-border" />
            @endif
            <p class="border-t border-border px-4 py-3 text-sm text-muted-foreground">মোট {{ \App\Support\Bn::num($this->thanas->count()) }}টি থানা</p>
        @endif
    </div>

    <x-ui.dialog model="showForm" form="save" width="max-w-md" :title="$editingId ? 'থানা সম্পাদনা' : 'নতুন থানা'" :description="($districts->firstWhere('id', (int) $district)?->bn_name ?? '').' জেলা'">
        <div class="grid gap-4">
            <x-ui.field label="নাম (ইংরেজি)" for="t-name" required error="name">
                <x-ui.input compact id="t-name" wire:model="name" error="name" lang="en" />
            </x-ui.field>
            <x-ui.field label="নাম (বাংলা)" for="t-bn" error="bn_name" hint="সাইটে বাংলা নামটি দেখায়; না থাকলে ইংরেজি নাম।">
                <x-ui.input compact id="t-bn" wire:model="bn_name" error="bn_name" />
            </x-ui.field>
        </div>
        <x-slot:footer>
            <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false">বাতিল</x-ui.button>
            <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save"><x-ui.spinner wire:loading wire:target="save" />সংরক্ষণ করুন</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
