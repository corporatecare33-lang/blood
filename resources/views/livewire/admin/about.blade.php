<div>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            অংশগুলো এই ক্রমে <a href="{{ route('about') }}" target="_blank" rel="noopener" class="font-medium text-crimson underline-offset-4 hover:underline">আমাদের সম্পর্কে</a> পাতায় দেখায়।
            <span class="hidden sm:inline">হাতল টেনে ক্রম বদলান।</span>
        </p>
        <x-ui.button type="button" size="sm" wire:click="create"><x-lucide-plus aria-hidden="true" />নতুন অংশ</x-ui.button>
    </div>

    <div class="admin-card mt-4 overflow-hidden">
        @if ($this->sections->isEmpty())
            <x-admin.empty icon="file-text" title="এখনো কোনো অংশ নেই">পাতায় এখন শুধু আমাদের শর্তসমূহ দেখাচ্ছে।</x-admin.empty>
        @else
            <ol wire:sort="reorder" class="divide-y divide-border">
                @foreach ($this->sections as $i => $s)
                    <li wire:key="s-{{ $s->id }}" wire:sort:item="{{ $s->id }}" class="flex gap-3 bg-white px-4 py-4">
                        <span wire:sort:handle class="mt-0.5 flex size-7 shrink-0 cursor-grab items-center justify-center rounded-md text-ink-soft hover:bg-muted active:cursor-grabbing" title="টেনে ক্রম বদলান">
                            <x-lucide-grip-vertical class="size-4" aria-hidden="true" /><span class="sr-only">টেনে ক্রম বদলান</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold"><span class="mr-1.5 text-muted-foreground tabular-nums">{{ \App\Support\Bn::num($i + 1) }}.</span>{{ $s->title }}</p>
                            <p class="mt-1 line-clamp-2 text-sm text-muted-foreground">{{ $s->body }}</p>
                        </div>
                        <div class="flex shrink-0 items-start gap-1">
                            <x-ui.button type="button" size="icon-sm" variant="ghost" wire:click="edit({{ $s->id }})"><x-lucide-pencil aria-hidden="true" /><span class="sr-only">{{ $s->title }} সম্পাদনা</span></x-ui.button>
                            <x-admin.confirm action="delete({{ $s->id }})" title="“{{ $s->title }}” অংশটি মুছবেন?" message="অংশটি পাতা থেকে স্থায়ীভাবে মুছে যাবে।"
                                class="flex size-8 items-center justify-center rounded-lg text-destructive hover:bg-crimson-soft">
                                <x-lucide-trash-2 class="size-4" aria-hidden="true" /><span class="sr-only">{{ $s->title }} মুছুন</span>
                            </x-admin.confirm>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>

    <x-ui.dialog model="showForm" form="save" width="max-w-2xl" :title="$editingId ? 'অংশ সম্পাদনা' : 'নতুন অংশ'" description="অনুচ্ছেদের মাঝে একটি ফাঁকা লাইন রাখুন।">
        <div class="grid gap-4">
            <x-ui.field label="শিরোনাম" for="a-title" required error="title">
                <x-ui.input compact id="a-title" wire:model="title" error="title" />
            </x-ui.field>
            <x-ui.field label="লেখা" for="a-body" required error="body">
                <x-ui.textarea id="a-body" wire:model="body" error="body" rows="12" class="text-sm" />
            </x-ui.field>
        </div>
        <x-slot:footer>
            <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false">বাতিল</x-ui.button>
            <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save"><x-ui.spinner wire:loading wire:target="save" />সংরক্ষণ করুন</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
