<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            {{ \App\Support\Bn::num($this->videos->count()) }}টি ভিডিও · {{ \App\Support\Bn::num($this->videos->where('is_published', true)->count()) }}টি হোম পেজে দেখাচ্ছে।
            @if ($this->videos->count() > 1)<span class="hidden sm:inline">ক্রম বদলাতে ভিডিওর কোণের হাতল টেনে সরান।</span>@endif
        </p>
        <x-ui.button type="button" size="sm" wire:click="create"><x-lucide-square-play aria-hidden="true" />ভিডিও যোগ করুন</x-ui.button>
    </div>

    @if ($this->videos->isEmpty())
        <div class="admin-card mt-4">
            <x-admin.empty icon="youtube" title="এখনো কোনো ভিডিও নেই">YouTube ভিডিওর লিংক যোগ করুন — প্রকাশ করলে হোম পেজের "ভিডিও গ্যালারি" অংশে দেখাবে।</x-admin.empty>
        </div>
    @else
        <ul wire:sort="reorder" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
            @foreach ($this->videos as $video)
                <li wire:key="v-{{ $video->id }}" wire:sort:item="{{ $video->id }}" class="admin-card group overflow-hidden">
                    <div class="relative">
                        <img src="{{ $video->thumbUrl() }}" alt="{{ $video->title }}" width="480" height="360" loading="lazy"
                             @class(['aspect-video w-full bg-muted object-cover', 'opacity-45 grayscale' => ! $video->is_published])>
                        <span class="pointer-events-none absolute inset-0 flex items-center justify-center" aria-hidden="true">
                            <span class="flex size-11 items-center justify-center rounded-full bg-crimson/90 text-white shadow-md"><x-lucide-play class="ml-0.5 size-5 fill-white" /></span>
                        </span>
                        <span wire:sort:handle class="absolute top-2 left-2 flex size-8 cursor-grab items-center justify-center rounded-md bg-white/90 text-ink-soft shadow-xs active:cursor-grabbing" title="টেনে ক্রম বদলান">
                            <x-lucide-grip-vertical class="size-4" aria-hidden="true" /><span class="sr-only">টেনে ক্রম বদলান</span>
                        </span>
                        @unless ($video->is_published)
                            <span class="absolute top-2 right-2"><x-ui.badge class="bg-white! shadow-xs"><x-lucide-eye-off class="size-3" aria-hidden="true" />লুকানো</x-ui.badge></span>
                        @endunless
                    </div>
                    <div class="flex items-start gap-2 p-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ $video->title }}</p>
                            <p class="truncate text-xs text-muted-foreground" lang="en">youtu.be/{{ $video->youtube_id }}</p>
                        </div>
                        <x-ui.dropdown :label="$video->title.' — আরও কাজ'">
                            <button type="button" class="ui-menu-item" wire:click="edit({{ $video->id }})"><x-lucide-pencil aria-hidden="true" />লিংক ও শিরোনাম</button>
                            <button type="button" class="ui-menu-item" wire:click="togglePublished({{ $video->id }})">
                                @if ($video->is_published)<x-lucide-eye-off aria-hidden="true" />হোম পেজ থেকে লুকান@else<x-lucide-eye aria-hidden="true" />প্রকাশ করুন@endif
                            </button>
                            <a href="{{ $video->watchUrl() }}" target="_blank" rel="noopener noreferrer" class="ui-menu-item"><x-lucide-external-link aria-hidden="true" />YouTube-এ দেখুন</a>
                            <div class="-mx-1 my-1 h-px bg-border"></div>
                            <x-admin.confirm action="delete({{ $video->id }})" class="ui-menu-item is-danger" title="ভিডিওটি মুছবেন?" message="ভিডিওটি গ্যালারি থেকে সরে যাবে। YouTube-এ মূল ভিডিওটি থেকে যাবে।">
                                <x-lucide-trash-2 aria-hidden="true" />মুছুন
                            </x-admin.confirm>
                        </x-ui.dropdown>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @php($previewId = \App\Models\Video::idFrom($url))
    <x-ui.dialog model="showForm" form="save" :title="$editingId ? 'ভিডিও সম্পাদনা' : 'ভিডিও যোগ করুন'" description="YouTube-এ ভিডিওর Share বাটন থেকে লিংক কপি করে এখানে বসান।">
        <div class="grid gap-4">
            <x-ui.field label="YouTube লিংক" for="v-url" required error="url" hint="youtube.com/watch, youtu.be বা Shorts — যেকোনো লিংক চলবে।">
                <x-ui.input compact id="v-url" type="url" inputmode="url" lang="en" placeholder="https://youtu.be/…" wire:model.live.debounce.500ms="url" error="url" />
            </x-ui.field>
            @if ($previewId)
                <div class="relative overflow-hidden rounded-lg bg-muted">
                    <img src="https://i.ytimg.com/vi/{{ $previewId }}/hqdefault.jpg" alt="ভিডিওর প্রিভিউ" width="480" height="360" class="aspect-video w-full object-cover">
                    <span wire:loading wire:target="url" class="absolute inset-0 flex items-center justify-center gap-2 bg-white/70 text-sm"><x-ui.spinner />শিরোনাম আনা হচ্ছে…</span>
                </div>
            @endif
            <x-ui.field label="শিরোনাম" for="v-title" required error="title" hint="লিংক বসালে YouTube থেকে নিজে থেকেই আসে — চাইলে বদলাতে পারেন।">
                <x-ui.input compact id="v-title" wire:model="title" error="title" />
            </x-ui.field>
            <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium">
                <x-ui.switch wire:model="is_published" />হোম পেজে প্রকাশ করুন
            </label>
        </div>

        <x-slot:footer>
            <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false">বাতিল</x-ui.button>
            <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save,url"><x-ui.spinner wire:loading wire:target="save" />সংরক্ষণ করুন</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
