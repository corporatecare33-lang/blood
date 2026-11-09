<form wire:submit="save" novalidate class="grid gap-6">
    <div class="grid gap-6 xl:grid-cols-2">
        <x-ui.card title="যোগাযোগ" description="ফুটার, আমাদের সম্পর্কে, শর্তাবলি ও গোপনীয়তা পাতায় দেখায়।">
            <x-ui.field label="ইমেইল" for="s-email" required error="email">
                <x-ui.input compact id="s-email" type="email" wire:model="email" error="email" lang="en" />
            </x-ui.field>
        </x-ui.card>

        <x-ui.card title="পৃষ্ঠপোষক" description="সাইটের তথ্য ফাইলে (llms.txt) শুধু নাম দেখায় — কোনো লিংক নয়। ফুটারে আর দেখায় না।">
            <x-ui.field label="পৃষ্ঠপোষকের নাম" for="s-sponsor" required error="sponsor_name">
                <x-ui.input compact id="s-sponsor" wire:model="sponsor_name" error="sponsor_name" lang="en" />
            </x-ui.field>
        </x-ui.card>
    </div>

    <x-ui.card title="আমাদের শর্তসমূহ" description="হোমপেজ, আমাদের সম্পর্কে, শর্তাবলি পাতা আর নিবন্ধন ফর্মে এই ক্রমে দেখায়।">
        <x-slot:action>
            @if ($conditionsChanged)
                <x-admin.confirm action="restoreConditions()" :danger="false" confirm="ফিরিয়ে আনুন" title="মূল শর্তসমূহ ফিরিয়ে আনবেন?" message="ব্লাডবন্ডের মূল ৫টি শর্ত ফর্মে বসবে। সংরক্ষণ না করা পর্যন্ত সাইটে বদলাবে না।"
                    class="inline-flex h-8 items-center gap-1.5 rounded-lg px-2.5 text-sm font-medium text-ink-soft hover:bg-muted hover:text-ink">
                    <x-lucide-rotate-ccw class="size-4" aria-hidden="true" />মূল শর্ত
                </x-admin.confirm>
            @endif
        </x-slot:action>

        @error('conditions')<x-ui.alert variant="error" class="mb-4">{{ $message }}</x-ui.alert>@enderror

        <ol wire:sort="moveCondition" class="grid gap-2">
            @foreach ($conditions as $i => $condition)
                <li wire:key="cond-{{ $i }}-{{ md5($condition) }}" wire:sort:item="{{ $i }}" class="flex items-start gap-2">
                    <span wire:sort:handle class="mt-0.5 flex size-8 shrink-0 cursor-grab items-center justify-center rounded-md text-ink-soft hover:bg-muted active:cursor-grabbing" title="টেনে ক্রম বদলান">
                        <x-lucide-grip-vertical class="size-4" aria-hidden="true" /><span class="sr-only">টেনে ক্রম বদলান</span>
                    </span>
                    <span class="mt-1.5 w-6 shrink-0 text-right text-sm text-muted-foreground tabular-nums" aria-hidden="true">{{ \App\Support\Bn::num($i + 1) }}.</span>
                    <div class="min-w-0 flex-1">
                        <label for="cond-{{ $i }}" class="sr-only">শর্ত {{ \App\Support\Bn::num($i + 1) }}</label>
                        <x-ui.input compact id="cond-{{ $i }}" wire:model.blur="conditions.{{ $i }}" error="conditions.{{ $i }}" />
                        @error('conditions.'.$i)<p class="mt-1 text-sm text-destructive">{{ $message }}</p>@enderror
                    </div>
                    <button type="button" wire:click="removeCondition({{ $i }})" class="flex size-9 shrink-0 items-center justify-center rounded-lg text-ink-soft hover:bg-crimson-soft hover:text-destructive">
                        <x-lucide-x class="size-4" aria-hidden="true" /><span class="sr-only">শর্ত {{ \App\Support\Bn::num($i + 1) }} সরান</span>
                    </button>
                </li>
            @endforeach
        </ol>
        <x-ui.button type="button" variant="outline" size="sm" class="mt-3 shadow-xs" wire:click="addCondition"><x-lucide-plus aria-hidden="true" />শর্ত যোগ করুন</x-ui.button>
    </x-ui.card>

    <div class="sticky bottom-0 z-10 -mx-4 flex items-center justify-end gap-3 border-t border-border bg-white/95 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
        <p wire:dirty class="mr-auto text-sm text-muted-foreground">সংরক্ষণ না করা পরিবর্তন আছে</p>
        <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save"><x-ui.spinner wire:loading wire:target="save" />সংরক্ষণ করুন</x-ui.button>
    </div>
</form>
