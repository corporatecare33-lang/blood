{{-- shadcn AlertDialog shared by every <x-admin.confirm> on the page. --}}
<div
    x-data="{
        open: false, busy: false, title: '', message: '', confirm: '', danger: true, run: null,
        show(d) { Object.assign(this, { title: d.title, message: d.message ?? '', confirm: d.confirm ?? 'নিশ্চিত', danger: d.danger ?? true, run: d.run }); this.open = true },
        async go() { this.busy = true; try { await this.run?.() } finally { this.busy = false; this.open = false } },
    }"
    x-on:confirm.window="show($event.detail)"
>
    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center sm:p-4" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message">
            <div x-show="open" x-transition.opacity.duration.150ms class="absolute inset-0 bg-ink/45" x-on:click="busy || (open = false)"></div>
            <div x-show="open" x-trap.noscroll="open" x-on:keydown.escape="busy || (open = false)"
                 x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:scale-95"
                 x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 sm:scale-95"
                 class="relative w-full max-w-md rounded-t-xl bg-white p-6 shadow-pop sm:rounded-xl">
                <h2 id="confirm-title" class="font-sans text-lg font-semibold" x-text="title"></h2>
                <p id="confirm-message" class="mt-2 text-sm text-muted-foreground" x-show="message" x-text="message"></p>
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-ui.button type="button" variant="outline" size="sm" x-on:click="open = false" x-bind:disabled="busy">বাতিল</x-ui.button>
                    <button type="button" x-on:click="go()" x-bind:disabled="busy"
                        x-bind:class="danger ? 'bg-destructive hover:bg-destructive/90' : 'bg-primary hover:bg-primary-hover'"
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-lg px-4 text-sm font-semibold text-white disabled:opacity-60">
                        <x-ui.spinner x-show="busy" x-cloak /><span x-text="confirm"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
