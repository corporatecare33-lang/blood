<x-layouts.app>
    <x-site.page-header title="রক্তের অনুরোধ" intro="যাঁদের এখন রক্ত দরকার। আপনার গ্রুপ মিললে এবং দিতে পারলে সরাসরি কল করুন।">
        <x-slot:actions>
            <x-ui.button :href="route('requests.create')"><x-lucide-plus aria-hidden="true" />অনুরোধ পোস্ট করুন</x-ui.button>
        </x-slot:actions>
    </x-site.page-header>

    <div class="container-page section pt-5!">
        <livewire:requests.board />
    </div>
</x-layouts.app>
