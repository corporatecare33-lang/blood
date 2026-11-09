<x-layouts.auth-card title="ব্লাডবন্ডে লগইন করুন" intro="মোবাইল নম্বর আর পাসওয়ার্ড দিয়ে লগইন করুন।">
    <livewire:auth.login-form />

    <x-slot:footer>
        ব্লাডবন্ডে নতুন? <a href="{{ route('register') }}" wire:navigate class="font-semibold text-crimson underline">ডোনার হিসেবে যুক্ত হন</a>
    </x-slot:footer>
</x-layouts.auth-card>
