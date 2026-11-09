<x-layouts.app>
    <x-site.page-header title="গ্যালারি" intro="রক্তদূতের রক্তদান কর্মসূচি ও স্বেচ্ছাসেবী কার্যক্রমের ছবি।" />

    <div class="container-page section pt-5!">
        @if ($items->isEmpty())
            <x-site.empty-state :level="2" data-reveal icon="image" title="এখনো কোনো ছবি নেই">
                স্বেচ্ছাসেবকরা রক্তদান কর্মসূচির ছবি আপলোড করলে এখানে দেখা যাবে।
            </x-site.empty-state>
        @else
            <div x-data="{ active: null, items: @js($items->map(fn ($i) => ['src' => $i->imageUrl(), 'title' => $i->title, 'caption' => $i->caption, 'w' => $i->width, 'h' => $i->height])) }">
                <ul data-reveal-stagger class="columns-1 gap-4 sm:columns-2 lg:columns-3">
                    @foreach ($items as $item)
                        <li class="mb-4 break-inside-avoid">
                            <figure>
                                <button type="button" x-on:click="active = {{ $loop->index }}" class="block w-full overflow-hidden rounded-xl bg-muted shadow-card">
                                    <img src="{{ $item->thumbUrl() }}" alt="{{ $item->title }}" width="{{ $item->width }}" height="{{ $item->height }}"
                                         loading="{{ $loop->index < 3 ? 'eager' : 'lazy' }}" decoding="async" class="h-auto w-full transition-transform duration-300 hover:scale-[1.02]">
                                    <span class="sr-only">বড় করে দেখুন</span>
                                </button>
                                <figcaption class="mt-2 text-[0.9375rem]">
                                    <span class="font-medium">{{ $item->title }}</span>
                                    @if ($item->caption)<span class="block text-ink-soft">{{ $item->caption }}</span>@endif
                                </figcaption>
                            </figure>
                        </li>
                    @endforeach
                </ul>

                <template x-teleport="body">
                    <div x-show="active !== null" x-cloak x-trap.noscroll="active !== null" x-on:keydown.escape.window="active = null"
                         x-on:keydown.right.window="if (active !== null) active = (active + 1) % items.length"
                         x-on:keydown.left.window="if (active !== null) active = (active - 1 + items.length) % items.length"
                         x-transition.opacity role="dialog" aria-modal="true" aria-label="ছবি দেখার জানালা"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-ink/90 p-4" x-on:click.self="active = null">
                        <template x-if="active !== null">
                            <figure class="max-h-full max-w-5xl">
                                <img :src="items[active].src" :alt="items[active].title" :width="items[active].w" :height="items[active].h" class="max-h-[80vh] w-auto rounded-md">
                                <figcaption class="mt-3 text-white"><span class="font-medium" x-text="items[active].title"></span> <span class="text-white/70" x-text="items[active].caption"></span></figcaption>
                            </figure>
                        </template>
                        <button type="button" x-on:click="active = null" class="absolute top-4 right-4 rounded-md p-2 text-white hover:bg-white/10">
                            <span class="sr-only">বন্ধ করুন</span><x-lucide-x class="size-6" aria-hidden="true" />
                        </button>
                    </div>
                </template>
            </div>
        @endif
    </div>
</x-layouts.app>
