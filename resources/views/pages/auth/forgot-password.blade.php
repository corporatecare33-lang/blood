<x-layouts.auth-card title="পাসওয়ার্ড রিসেট করুন">
    <livewire:auth.forgot-password />

    <x-slot:footer>
        মনে পড়েছে? <a href="{{ route('login') }}" wire:navigate class="font-semibold text-crimson underline">লগইন করুন</a>
    </x-slot:footer>
</x-layouts.auth-card>
