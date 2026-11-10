@php
    $live = $post?->isLive();
    $coverPreview = $cover && ! $errors->has('cover') ? $cover->temporaryUrl() : (! $removeCover ? $post?->coverThumbUrl() : null);
    $tools = [
        ['heading-2', 'উপশিরোনাম', "line('## ')"],
        ['heading-3', 'ছোট উপশিরোনাম', "line('### ')"],
        ['bold', 'মোটা লেখা (Ctrl+B)', "wrap('**', '**', 'মোটা লেখা')"],
        ['italic', 'বাঁকা লেখা (Ctrl+I)', "wrap('*', '*', 'বাঁকা লেখা')"],
        ['list', 'বুলেট তালিকা', "line('- ')"],
        ['list-ordered', 'নম্বর তালিকা', "line('1. ')"],
        ['text-quote', 'উদ্ধৃতি', "line('> ')"],
        ['link', 'লিংক', "link()"],
    ];
@endphp

<form wire:submit="save" class="grid gap-5">
    {{-- Action bar --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.posts') }}" wire:navigate class="mr-auto inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
            <x-lucide-arrow-left class="size-4" aria-hidden="true" />সব লেখা
        </a>
        @if ($post)
            @if ($live)
                <x-ui.badge variant="success">প্রকাশিত</x-ui.badge>
            @elseif ($post->isScheduled())
                <x-ui.badge variant="warning">নির্ধারিত</x-ui.badge>
            @else
                <x-ui.badge>খসড়া</x-ui.badge>
            @endif
            <x-ui.button :href="$post->url()" variant="outline" size="sm" :navigate="false" target="_blank" rel="noopener">
                <x-lucide-external-link aria-hidden="true" />{{ $live ? 'সাইটে দেখুন' : 'প্রিভিউ' }}
            </x-ui.button>
        @endif
        <x-ui.button size="sm" wire:loading.attr="disabled" wire:target="save,cover">
            <x-ui.spinner wire:loading wire:target="save" /><x-lucide-save wire:loading.remove wire:target="save" aria-hidden="true" />
            {{ $is_published ? ($post?->is_published ? 'সংরক্ষণ করুন' : 'প্রকাশ করুন') : 'খসড়া সংরক্ষণ' }}
        </x-ui.button>
    </div>

    @if ($errors->any())
        <x-ui.alert variant="error" title="কিছু তথ্য ঠিক করতে হবে">নিচে লাল চিহ্নিত ঘরগুলো দেখুন।</x-ui.alert>
    @endif

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        {{-- Main column --}}
        <div class="grid min-w-0 gap-5">
            <div class="admin-card grid gap-4 p-4 sm:p-5">
                <x-ui.field label="শিরোনাম" for="p-title" required error="title">
                    <x-ui.input id="p-title" wire:model.live.blur="title" error="title" maxlength="160" autocomplete="off"
                        class="font-display text-lg font-semibold" />
                </x-ui.field>

                <x-ui.field label="লিংক" for="p-slug" required error="slug" hint="শিরোনাম থেকে নিজে তৈরি হয়। ইংরেজিতে ছোট করে লিখলে লিংক শেয়ার করা সহজ হয় (যেমন sylhet-blood-camp)।">
                    <div class="flex items-stretch">
                        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-input bg-muted px-2.5 text-sm text-muted-foreground" lang="en">/blog/</span>
                        <x-ui.input compact id="p-slug" wire:model.live.blur="slug" error="slug" maxlength="120" autocomplete="off" class="rounded-l-none" />
                    </div>
                </x-ui.field>

                <x-ui.field label="সারাংশ" for="p-excerpt" error="excerpt" hint="এক-দুই বাক্যে লেখাটি কী নিয়ে। কার্ডে, গুগল সার্চে আর শেয়ার করলে এটিই দেখায়। খালি রাখলে লেখার শুরুর অংশ নেওয়া হবে।">
                    <x-ui.textarea id="p-excerpt" wire:model="excerpt" error="excerpt" rows="2" maxlength="300" class="text-[0.9375rem]" />
                </x-ui.field>
            </div>

            {{-- Body editor --}}
            <div class="admin-card overflow-hidden transition-shadow has-[textarea:focus-visible]:border-crimson has-[textarea:focus-visible]:ring-3 has-[textarea:focus-visible]:ring-crimson/15"
                 x-data="{
                    words: 0,
                    count() { this.words = ($refs.body?.value.trim().match(/\S+/g) || []).length },
                    bn(n) { return String(n).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]) },
                    edit(fn) {
                        let el = $refs.body, start = el.selectionStart, end = el.selectionEnd
                        let [text, selStart, selEnd] = fn(el.value, start, end)
                        el.value = text
                        el.dispatchEvent(new Event('input', { bubbles: true }))
                        el.focus()
                        el.setSelectionRange(selStart, selEnd)
                    },
                    wrap(before, after, fallback) {
                        this.edit((v, s, e) => {
                            let picked = v.slice(s, e) || fallback
                            return [v.slice(0, s) + before + picked + after + v.slice(e), s + before.length, s + before.length + picked.length]
                        })
                    },
                    line(prefix) {
                        this.edit((v, s, e) => {
                            let lineStart = v.lastIndexOf('\n', s - 1) + 1
                            let block = v.slice(lineStart, e).split('\n').map(l => prefix + l.replace(/^(#{1,6} |[-*] |\d+\. |> )/, '')).join('\n')
                            return [v.slice(0, lineStart) + block + v.slice(e), lineStart + block.length, lineStart + block.length]
                        })
                    },
                    link() {
                        let url = prompt('লিংকের ঠিকানা (https://… বা /tips)', 'https://')
                        if (! url || url === 'https://') return
                        this.edit((v, s, e) => {
                            let label = v.slice(s, e) || 'লিংকের লেখা'
                            let md = '[' + label + '](' + url + ')'
                            return [v.slice(0, s) + md + v.slice(e), s + 1, s + 1 + label.length]
                        })
                    },
                 }"
                 x-init="$nextTick(() => count())">
                <div class="flex flex-wrap items-center gap-1 border-b border-border bg-muted/40 px-2 py-1.5">
                    <div role="tablist" aria-label="লেখা বা প্রিভিউ" class="mr-1 inline-flex h-8 items-center gap-0.5 rounded-md bg-muted p-[3px]">
                        <button type="button" role="tab" aria-selected="{{ $previewing ? 'false' : 'true' }}" wire:click="$set('previewing', false)"
                            @class(['inline-flex h-full items-center rounded px-2.5 text-sm font-medium', 'bg-white text-ink shadow-xs' => ! $previewing, 'text-ink-soft hover:text-ink' => $previewing])>লিখুন</button>
                        <button type="button" role="tab" aria-selected="{{ $previewing ? 'true' : 'false' }}" wire:click="$set('previewing', true)"
                            @class(['inline-flex h-full items-center rounded px-2.5 text-sm font-medium', 'bg-white text-ink shadow-xs' => $previewing, 'text-ink-soft hover:text-ink' => ! $previewing])>প্রিভিউ</button>
                    </div>
                    @unless ($previewing)
                        <span class="mx-1 hidden h-5 w-px bg-border sm:block" aria-hidden="true"></span>
                        @foreach ($tools as [$icon, $label, $action])
                            <button type="button" x-on:click="{{ $action }}" title="{{ $label }}"
                                class="flex size-8 items-center justify-center rounded-md text-ink-soft hover:bg-white hover:text-ink hover:shadow-xs">
                                <x-dynamic-component :component="'lucide-'.$icon" class="size-4" aria-hidden="true" /><span class="sr-only">{{ $label }}</span>
                            </button>
                        @endforeach
                    @endunless
                    <span class="ml-auto pr-1 text-xs text-muted-foreground" x-show="words > 0" x-cloak>
                        <span x-text="bn(words)"></span> শব্দ · <span x-text="bn(Math.max(1, Math.ceil(words / 180)))"></span> মিনিটে পড়া
                    </span>
                </div>

                <div @class(['hidden' => $previewing])>
                    <label for="p-body" class="sr-only">লেখা</label>
                    <textarea id="p-body" x-ref="body" wire:model="body" x-on:input="count()"
                        x-on:keydown.ctrl.b.prevent="wrap('**', '**', 'মোটা লেখা')" x-on:keydown.meta.b.prevent="wrap('**', '**', 'মোটা লেখা')"
                        x-on:keydown.ctrl.i.prevent="wrap('*', '*', 'বাঁকা লেখা')" x-on:keydown.meta.i.prevent="wrap('*', '*', 'বাঁকা লেখা')"
                        rows="18" spellcheck="false"
                        @if ($errors->has('body')) aria-invalid="true" aria-describedby="p-body-error" @endif
                        placeholder="এখানে লেখা শুরু করুন। নতুন অনুচ্ছেদের জন্য মাঝে একটি ফাঁকা লাইন রাখুন।"
                        class="block min-h-[22rem] w-full resize-y border-0 bg-white px-4 py-4 text-base leading-relaxed text-ink placeholder:text-ink-soft/60 focus:ring-0 focus:outline-none sm:px-5"></textarea>
                </div>

                @if ($previewing)
                    <div class="min-h-[22rem] px-4 py-5 sm:px-6">
                        @if (trim($body) === '')
                            <p class="text-muted-foreground">প্রিভিউ দেখানোর মতো কিছু লেখা হয়নি।</p>
                        @else
                            <div class="prose-bandhan prose-post">{!! $previewHtml !!}</div>
                        @endif
                    </div>
                @endif

                @error('body')
                    <p id="p-body-error" class="flex items-start gap-1.5 border-t border-border px-4 py-2.5 text-sm font-medium text-destructive sm:px-5">
                        <x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}
                    </p>
                @enderror

                <details class="group border-t border-border text-sm">
                    <summary class="flex cursor-pointer list-none items-center gap-1.5 px-4 py-2.5 font-medium text-ink-soft hover:text-ink sm:px-5 [&::-webkit-details-marker]:hidden">
                        <x-lucide-chevron-right class="size-4 transition-transform group-open:rotate-90" aria-hidden="true" />লেখা সাজানোর নিয়ম
                    </summary>
                    <dl class="grid gap-x-6 gap-y-1.5 px-4 pb-4 sm:grid-cols-2 sm:px-5">
                        @foreach ([
                            ['## উপশিরোনাম', 'বড় উপশিরোনাম'],
                            ['**মোটা লেখা**', 'মোটা অক্ষর'],
                            ['- প্রথম কথা', 'বুলেট তালিকা'],
                            ['1. প্রথম ধাপ', 'নম্বর তালিকা'],
                            ['> উদ্ধৃতি', 'উদ্ধৃতি বা বিশেষ বার্তা'],
                            ['[লেখা](/tips)', 'লিংক'],
                        ] as [$syntax, $meaning])
                            <div class="flex items-baseline justify-between gap-3 border-b border-dashed border-border py-1">
                                <dt><code class="rounded bg-muted px-1.5 py-0.5 text-[0.8125rem]">{{ $syntax }}</code></dt>
                                <dd class="text-muted-foreground">{{ $meaning }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </details>
            </div>
        </div>

        {{-- Settings column --}}
        <aside class="grid gap-5 lg:sticky lg:top-20" aria-label="প্রকাশ ও কভার ছবি">
            <div class="admin-card grid gap-4 p-4">
                <h2 class="font-sans text-[0.9375rem] font-semibold">প্রকাশ</h2>
                <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium">
                    <x-ui.switch wire:model.live="is_published" />সাইটে প্রকাশ করুন
                </label>
                <x-ui.field label="প্রকাশের তারিখ ও সময়" for="p-published" :required="$is_published" error="published_at"
                    hint="ভবিষ্যতের সময় দিলে লেখাটি সেই সময়ে নিজে থেকেই প্রকাশ হবে।">
                    <x-ui.input compact id="p-published" type="datetime-local" wire:model="published_at" error="published_at" />
                </x-ui.field>
                @if ($post)
                    <p class="border-t border-border pt-3 text-xs text-muted-foreground">
                        শেষ সম্পাদনা {{ \App\Support\Bn::ago($post->updated_at) }}@if ($post->author) · লিখেছেন {{ $post->author->name }}@endif
                    </p>
                @endif
            </div>

            <div class="admin-card grid gap-4 p-4">
                <h2 class="font-sans text-[0.9375rem] font-semibold">কভার ছবি</h2>
                <div>
                    <label for="p-cover" class="flex cursor-pointer flex-col items-center justify-center gap-2 overflow-hidden rounded-lg border border-dashed border-input bg-muted/40 text-center text-sm hover:border-crimson hover:bg-crimson-soft/40">
                        @if ($coverPreview)
                            <img src="{{ $coverPreview }}" alt="কভার ছবির প্রিভিউ" class="aspect-[16/10] w-full object-cover">
                            <span class="pb-3 text-muted-foreground">অন্য ছবি বেছে নিতে ক্লিক করুন</span>
                        @else
                            <span class="flex flex-col items-center gap-2 px-4 py-8">
                                <x-lucide-image-up class="size-6 text-ink-soft" aria-hidden="true" />
                                <span class="font-semibold text-crimson">ছবি বেছে নিন</span>
                                <span class="text-xs text-muted-foreground">JPG, PNG বা WebP, সর্বোচ্চ ৮ MB। আড়াআড়ি (ল্যান্ডস্কেপ) ছবি সবচেয়ে ভালো দেখায়।</span>
                            </span>
                        @endif
                        <span wire:loading wire:target="cover" class="flex items-center gap-2 pb-3 text-muted-foreground"><x-ui.spinner />আপলোড হচ্ছে…</span>
                    </label>
                    <input id="p-cover" type="file" wire:model="cover" accept="image/jpeg,image/png,image/webp" class="sr-only"
                        @if ($errors->has('cover')) aria-invalid="true" aria-describedby="p-cover-error" @endif>
                    @error('cover')
                        <p id="p-cover-error" class="mt-1.5 flex items-start gap-1.5 text-sm font-medium text-destructive">
                            <x-lucide-circle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />{{ $message }}
                        </p>
                    @enderror
                    @if ($coverPreview)
                        <button type="button" wire:click="clearCover" class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-destructive hover:underline">
                            <x-lucide-x class="size-4" aria-hidden="true" />ছবি সরান
                        </button>
                    @endif
                </div>
                <x-ui.field label="ছবির বর্ণনা" for="p-cover-alt" error="cover_alt" hint="ছবিতে কী দেখা যাচ্ছে — যাঁরা চোখে দেখেন না তাঁদের স্ক্রিন রিডার এটি পড়ে শোনায়। খালি রাখলে শিরোনাম ব্যবহার হবে।">
                    <x-ui.input compact id="p-cover-alt" wire:model="cover_alt" error="cover_alt" maxlength="160" />
                </x-ui.field>
            </div>
        </aside>
    </div>
</form>
