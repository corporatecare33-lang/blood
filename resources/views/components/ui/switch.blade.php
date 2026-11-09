{{-- shadcn Switch on a native checkbox: <x-ui.switch id="x" wire:model="is_active" /> or with wire:click for instant toggles. --}}
<span class="relative inline-flex h-5 w-9 shrink-0">
    <input type="checkbox" role="switch" {{ $attributes->class('peer absolute inset-0 z-10 size-full cursor-pointer appearance-none rounded-full focus-visible:outline-3 focus-visible:outline-offset-2 focus-visible:outline-ring disabled:cursor-not-allowed') }}>
    <span class="pointer-events-none absolute inset-0 rounded-full bg-input transition-colors peer-checked:bg-crimson peer-disabled:opacity-50" aria-hidden="true"></span>
    <span class="pointer-events-none absolute top-0.5 left-0.5 size-4 rounded-full bg-white shadow-xs transition-transform peer-checked:translate-x-4" aria-hidden="true"></span>
</span>
