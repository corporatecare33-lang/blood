<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="max-w-2xl text-sm text-muted-foreground">অ্যাডমিনরা ডোনার, অনুরোধ, থানা, গ্যালারি, আমাদের সম্পর্কে পাতা ও সাইট সেটিংস পরিচালনা করতে পারেন। ডোনার মোছা আর অ্যাডমিন যোগ করা শুধু সুপার অ্যাডমিনের কাজ।</p>
        <x-ui.button type="button" size="sm" wire:click="create"><x-lucide-user-plus aria-hidden="true" />অ্যাডমিন যোগ করুন</x-ui.button>
    </div>

    <div class="admin-card mt-4 overflow-hidden">
        <div class="relative overflow-x-auto">
            <table class="ui-table min-w-[36rem]">
                <caption class="sr-only">অ্যাডমিনদের তালিকা</caption>
                <thead>
                    <tr>
                        <th scope="col">নাম</th>
                        <th scope="col">ভূমিকা</th>
                        <th scope="col">জেলা</th>
                        <th scope="col" class="w-40"><span class="sr-only">কাজ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->admins as $a)
                        <tr wire:key="adm-{{ $a->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :user="$a" size="size-9 text-xs" />
                                    <div class="min-w-0">
                                        <p class="font-medium">{{ $a->name }} @if ($a->is(auth()->user()))<span class="text-xs font-normal text-muted-foreground">(আপনি)</span>@endif</p>
                                        <p class="text-xs text-muted-foreground tabular-nums" lang="en">{{ $a->mobile }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <x-ui.badge :variant="$a->isSuperAdmin() ? 'danger' : 'neutral'">
                                    @if ($a->isSuperAdmin())<x-lucide-shield-check class="size-3" aria-hidden="true" />@endif{{ $a->role->label() }}
                                </x-ui.badge>
                            </td>
                            <td>{{ $a->district?->bn_name ?? '—' }}</td>
                            <td class="text-right">
                                @unless ($a->isSuperAdmin())
                                    <x-admin.confirm action="demote({{ $a->id }})" confirm="অধিকার সরান" title="{{ $a->name }}-এর অ্যাডমিন অধিকার সরাবেন?" message="তিনি আবার সাধারণ ডোনার হবেন এবং অ্যাডমিন প্যানেলে ঢুকতে পারবেন না।"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg px-2.5 text-sm font-medium text-destructive hover:bg-crimson-soft">
                                        <x-lucide-user-minus class="size-4" aria-hidden="true" />অ্যাডমিন থেকে সরান
                                    </x-admin.confirm>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <x-ui.dialog model="showForm" form="promote" width="max-w-md" title="অ্যাডমিন যোগ করুন" description="তাঁকে আগে ডোনার হিসেবে নিবন্ধিত হতে হবে।">
        <x-ui.field label="তাঁর মোবাইল নম্বর" for="adm-mobile" required error="mobile">
            <x-ui.input compact id="adm-mobile" type="tel" inputmode="numeric" wire:model="mobile" error="mobile" placeholder="01XXXXXXXXX" lang="en" />
        </x-ui.field>
        <x-slot:footer>
            <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false">বাতিল</x-ui.button>
            <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="promote"><x-ui.spinner wire:loading wire:target="promote" />অ্যাডমিন করুন</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
