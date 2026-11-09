{{-- Flash message from session('status') or a Livewire `notify` event. placement="admin": bottom-right, no tab bar to clear. --}}
@props(['placement' => 'site'])
<div
    x-data="{ show: false, message: '', timer: null,
        flash(text) { this.message = text; this.show = true; clearTimeout(this.timer); this.timer = setTimeout(() => this.show = false, 5000) } }"
    x-init="@if (session('status')) flash(@js(session('status'))) @endif"
    x-on:notify.window="flash($event.detail.message ?? $event.detail[0]?.message ?? $event.detail)"
    @class(['pointer-events-none fixed inset-x-0 z-[70] flex px-4', 'bottom-[5.5rem] justify-center lg:bottom-4' => $placement === 'site', 'bottom-4 justify-center sm:justify-end sm:px-6' => $placement === 'admin'])
    role="status" aria-live="polite"
>
    <div x-show="show" x-cloak
         x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0"
         class="pointer-events-auto flex max-w-md items-start gap-3 rounded-lg bg-ink px-4 py-3 text-[0.9375rem] text-white shadow-lg">
        <x-lucide-circle-check class="mt-0.5 size-5 shrink-0 text-marigold" aria-hidden="true" />
        <p x-text="message"></p>
        <button type="button" x-on:click="show = false" class="-mr-1 ml-2 rounded p-0.5 text-white/70 hover:text-white">
            <span class="sr-only">বন্ধ করুন</span><x-lucide-x class="size-4" aria-hidden="true" />
        </button>
    </div>
</div>
