<div>
    @if ($step === 'request')
        <form wire:submit="sendCode" novalidate class="grid gap-4">
            <p class="text-ink-soft">যে মোবাইল নম্বরে নিবন্ধন করেছেন সেটি দিন। আমরা ৬ অঙ্কের একটি কোড পাঠাব।</p>
            <x-ui.field label="মোবাইল নম্বর" for="mobile" required>
                <x-ui.input id="mobile" type="tel" inputmode="numeric" wire:model="mobile" autocomplete="tel-national" placeholder="01XXXXXXXXX" maxlength="14" required autofocus />
            </x-ui.field>
            <x-ui.button class="w-full" size="lg" wire:loading.attr="disabled" wire:target="sendCode">
                <x-ui.spinner wire:loading wire:target="sendCode" />কোড পাঠান
            </x-ui.button>
        </form>
    @else
        <form wire:submit="resetPassword" novalidate class="grid gap-4">
            <x-ui.alert variant="success">
                {{ $mobile }} নম্বরটি নিবন্ধিত হলে কোড পাঠানো হয়েছে। কোডের মেয়াদ {{ \App\Support\Bn::num(\App\Services\OtpService::TTL_MINUTES) }} মিনিট।
            </x-ui.alert>
            <x-ui.field label="৬ অঙ্কের কোড" for="code" required>
                <x-ui.input id="code" inputmode="numeric" wire:model="code" autocomplete="one-time-code" maxlength="6" required autofocus class="tracking-[0.4em]" />
            </x-ui.field>
            <x-ui.field label="নতুন পাসওয়ার্ড" for="password" required hint="কমপক্ষে ৮ অক্ষর।">
                <x-ui.input id="password" type="password" wire:model="password" autocomplete="new-password" required />
            </x-ui.field>
            <x-ui.field label="আবার নতুন পাসওয়ার্ড" for="password_confirmation" required>
                <x-ui.input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required />
            </x-ui.field>
            <x-ui.button class="w-full" size="lg" wire:loading.attr="disabled" wire:target="resetPassword">
                <x-ui.spinner wire:loading wire:target="resetPassword" />পাসওয়ার্ড পরিবর্তন করুন
            </x-ui.button>
            <button type="button" wire:click="startOver" class="text-[0.9375rem] font-semibold text-crimson hover:underline">অন্য নম্বর ব্যবহার করুন</button>
        </form>
    @endif
</div>
