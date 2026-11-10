@php
    $bn = fn ($n) => \App\Support\Bn::num($n);
    $page = $this->posts;
@endphp

<div>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2">
        <x-ui.tabs model="status" :value="$status" :options="$tabs" label="অবস্থা অনুযায়ী ফিল্টার" class="w-full sm:w-auto" />
        <div class="relative w-full sm:ml-auto sm:w-64">
            <x-lucide-search class="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-ink-soft" aria-hidden="true" />
            <label for="p-q" class="sr-only">শিরোনাম দিয়ে খুঁজুন</label>
            <x-ui.input compact id="p-q" type="search" wire:model.live.debounce.400ms="q" placeholder="শিরোনাম দিয়ে খুঁজুন…" class="pl-8" />
        </div>
        <x-ui.button :href="route('admin.posts.create')" size="sm" class="w-full sm:w-auto"><x-lucide-square-pen aria-hidden="true" />নতুন লেখা</x-ui.button>
    </div>

    <div class="admin-card mt-4 overflow-hidden">
        <div class="relative overflow-x-auto" wire:loading.class="opacity-60" wire:target="status,q,previousPage,nextPage,gotoPage">
            <table class="ui-table min-w-[44rem]">
                <caption class="sr-only">ব্লগের লেখা</caption>
                <thead>
                    <tr>
                        <th scope="col">লেখা</th>
                        <th scope="col">অবস্থা</th>
                        <th scope="col">প্রকাশের তারিখ</th>
                        <th scope="col">শেষ সম্পাদনা</th>
                        <th scope="col" class="w-12"><span class="sr-only">কাজ</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($page as $post)
                        <tr wire:key="p-{{ $post->id }}">
                            <td class="max-w-[26rem]">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-16 shrink-0 items-center justify-center overflow-hidden rounded-md bg-crimson-soft">
                                        @if ($post->hasCover())
                                            @php([$tw, $th] = $post->thumbSize())
                                            <img src="{{ $post->coverThumbUrl() }}" alt="" width="{{ $tw }}" height="{{ $th }}" loading="lazy" class="size-full object-cover">
                                        @else
                                            <x-lucide-droplet class="size-4 fill-crimson/15 text-crimson/40" aria-hidden="true" />
                                        @endif
                                    </span>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.posts.edit', $post) }}" wire:navigate class="line-clamp-2 font-medium hover:text-crimson hover:underline">{{ $post->title }}</a>
                                        <span class="block truncate text-xs text-muted-foreground">/blog/{{ $post->slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($post->isLive())
                                    <x-ui.badge variant="success">প্রকাশিত</x-ui.badge>
                                @elseif ($post->isScheduled())
                                    <x-ui.badge variant="warning"><x-lucide-clock class="size-3" aria-hidden="true" />নির্ধারিত</x-ui.badge>
                                @else
                                    <x-ui.badge>খসড়া</x-ui.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                @if ($post->published_at)
                                    {{ \App\Support\Bn::date($post->published_at) }}
                                    <span class="block text-xs text-muted-foreground">{{ \App\Support\Bn::time($post->published_at) }}</span>
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-muted-foreground">{{ \App\Support\Bn::ago($post->updated_at) }}</td>
                            <td class="text-right">
                                <x-ui.dropdown :label="$post->title.' — আরও কাজ'">
                                    <a href="{{ route('admin.posts.edit', $post) }}" wire:navigate class="ui-menu-item"><x-lucide-pencil aria-hidden="true" />সম্পাদনা করুন</a>
                                    <a href="{{ $post->url() }}" target="_blank" rel="noopener" class="ui-menu-item"><x-lucide-external-link aria-hidden="true" />{{ $post->isLive() ? 'সাইটে দেখুন' : 'প্রিভিউ দেখুন' }}</a>
                                    <button type="button" class="ui-menu-item" wire:click="togglePublished({{ $post->id }})">
                                        @if ($post->is_published)<x-lucide-eye-off aria-hidden="true" />খসড়ায় ফেরান@else<x-lucide-send aria-hidden="true" />প্রকাশ করুন@endif
                                    </button>
                                    <div class="-mx-1 my-1 h-px bg-border"></div>
                                    <x-admin.confirm action="delete({{ $post->id }})" class="ui-menu-item is-danger" confirm="মুছুন"
                                        title="লেখাটি মুছবেন?" message="লেখা ও তার কভার ছবি স্থায়ীভাবে মুছে যাবে।">
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
            <x-admin.empty icon="newspaper" :title="$q !== '' || $status !== 'all' ? 'এখানে কোনো লেখা নেই' : 'এখনো কোনো লেখা নেই'" class="border-t border-border">
                @if ($q !== '' || $status !== 'all')
                    ফিল্টার বদলে আবার দেখুন।
                @else
                    রক্তদান কর্মসূচির খবর বা সচেতনতামূলক প্রথম লেখাটি লিখুন — প্রকাশ করলে ব্লগ ও হোম পাতায় দেখাবে।
                @endif
            </x-admin.empty>
        @endif
        <x-admin.pagination :paginator="$page" unit="টি লেখা" />
    </div>
</div>
