<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            {{ \App\Support\Bn::num($this->items->count()) }}টি ছবি · {{ \App\Support\Bn::num($this->items->where('is_published', true)->count()) }}টি প্রকাশিত।
            @if ($this->items->count() > 1)<span class="hidden sm:inline">ক্রম বদলাতে ছবির কোণের হাতল টেনে সরান।</span>@endif
        </p>
        <x-ui.button type="button" size="sm" wire:click="create"><x-lucide-image-plus aria-hidden="true" />ছবি যোগ করুন</x-ui.button>
    </div>

    @if ($this->items->isEmpty())
        <div class="admin-card mt-4">
            <x-admin.empty icon="images" title="এখনো কোনো ছবি নেই">রক্তদান কর্মসূচির প্রথম ছবিটি যোগ করুন — প্রকাশ করলে গ্যালারি পাতায় দেখাবে।</x-admin.empty>
        </div>
    @else
        <ul wire:sort="reorder" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($this->items as $item)
                <li wire:key="g-{{ $item->id }}" wire:sort:item="{{ $item->id }}" class="admin-card group overflow-hidden">
                    <div class="relative">
                        <img src="{{ $item->thumbUrl() }}" alt="{{ $item->title }}" width="{{ $item->width }}" height="{{ $item->height }}" loading="lazy"
                             @class(['aspect-[4/3] w-full bg-muted object-cover', 'opacity-45 grayscale' => ! $item->is_published])>
                        <span wire:sort:handle class="absolute top-2 left-2 flex size-8 cursor-grab items-center justify-center rounded-md bg-white/90 text-ink-soft shadow-xs active:cursor-grabbing" title="টেনে ক্রম বদলান">
                            <x-lucide-grip-vertical class="size-4" aria-hidden="true" /><span class="sr-only">টেনে ক্রম বদলান</span>
                        </span>
                        @unless ($item->is_published)
                            <span class="absolute top-2 right-2"><x-ui.badge class="bg-white! shadow-xs"><x-lucide-eye-off class="size-3" aria-hidden="true" />লুকানো</x-ui.badge></span>
                        @endunless
                    </div>
                    <div class="flex items-start gap-2 p-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ $item->title }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ $item->caption ?: \App\Support\Bn::date($item->created_at) }}</p>
                        </div>
                        <x-ui.dropdown :label="$item->title.' — আরও কাজ'">
                            <button type="button" class="ui-menu-item" wire:click="edit({{ $item->id }})"><x-lucide-pencil aria-hidden="true" />শিরোনাম ও ক্যাপশন</button>
                            <button type="button" class="ui-menu-item" wire:click="togglePublished({{ $item->id }})">
                                @if ($item->is_published)<x-lucide-eye-off aria-hidden="true" />গ্যালারি থেকে লুকান@else<x-lucide-eye aria-hidden="true" />প্রকাশ করুন@endif
                            </button>
                            <a href="{{ $item->imageUrl() }}" target="_blank" rel="noopener" class="ui-menu-item"><x-lucide-maximize-2 aria-hidden="true" />পূর্ণ আকারে দেখুন</a>
                            <div class="-mx-1 my-1 h-px bg-border"></div>
                            <x-admin.confirm action="delete({{ $item->id }})" class="ui-menu-item is-danger" title="ছবিটি মুছবেন?" message="ছবিটি সার্ভার থেকে স্থায়ীভাবে মুছে যাবে।">
                                <x-lucide-trash-2 aria-hidden="true" />মুছুন
                            </x-admin.confirm>
                        </x-ui.dropdown>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <x-ui.dialog model="showForm" form="save" :title="$editingId ? 'ছবির তথ্য সম্পাদনা' : 'ছবি যোগ করুন'" :description="$editingId ? null : 'JPG, PNG বা WebP, সর্বোচ্চ ৮ MB। নিজে থেকেই WebP-তে রূপান্তর হবে।'">
        <div class="grid gap-4">
            @unless ($editingId)
                <x-ui.field label="ছবি" for="g-photo" required error="photo">
                    <label for="g-photo" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-input bg-muted/40 px-4 py-6 text-center text-sm hover:border-crimson hover:bg-crimson-soft/40">
                        @if ($photo && ! $errors->has('photo'))
                            <img src="{{ $photo->temporaryUrl() }}" alt="নির্বাচিত ছবির প্রিভিউ" class="max-h-44 rounded-md object-contain">
                            <span class="text-muted-foreground">অন্য ছবি বেছে নিতে ক্লিক করুন</span>
                        @else
                            <x-lucide-upload class="size-6 text-ink-soft" aria-hidden="true" />
                            <span class="font-semibold text-crimson">ছবি বেছে নিন</span>
                        @endif
                        <span wire:loading wire:target="photo" class="flex items-center gap-2 text-muted-foreground"><x-ui.spinner />আপলোড হচ্ছে…</span>
                    </label>
                    <input id="g-photo" type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="sr-only">
                </x-ui.field>
            @endunless
            <x-ui.field label="শিরোনাম" for="g-title" required error="title" hint="ছবিতে কী আছে লিখুন — এটিই ছবির বিকল্প লেখা (alt text) হবে।">
                <x-ui.input compact id="g-title" wire:model="title" error="title" />
            </x-ui.field>
            <x-ui.field label="ক্যাপশন" for="g-caption" error="caption">
                <x-ui.textarea id="g-caption" wire:model="caption" error="caption" rows="3" class="text-sm" />
            </x-ui.field>
            <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium">
                <x-ui.switch wire:model="is_published" />গ্যালারিতে প্রকাশ করুন
            </label>
        </div>

        <x-slot:footer>
            <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false">বাতিল</x-ui.button>
            <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save,photo"><x-ui.spinner wire:loading wire:target="save" />{{ $editingId ? 'সংরক্ষণ করুন' : 'আপলোড করুন' }}</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
