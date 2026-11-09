<x-layouts.app>
    <x-site.page-header title="ডোনার তালিকা" intro="রক্তের গ্রুপ, নাম বা এলাকা দিয়ে খুঁজুন। গত ৯০ দিনে যাঁরা রক্ত দিয়েছেন তাঁদের আলাদা করে রাখা হয়।" />

    <div class="container-page section pt-5!">
        @auth
            <livewire:donors.search />
        @else
            <div class="grid gap-4 lg:grid-cols-12">
                <div data-reveal class="card p-6 sm:p-8 lg:col-span-7">
                    <span class="flex size-12 items-center justify-center rounded-full bg-crimson-soft text-crimson" aria-hidden="true"><x-lucide-lock class="size-6" /></span>
                    <h2 class="mt-4 text-2xl font-bold">ডোনার দেখতে লগইন করুন</h2>
                    <p class="mt-2 max-w-xl text-ink-soft">
                        ডোনারদের মোবাইল নম্বর যেন অপব্যবহার না হয়, তাই শুধু নিবন্ধিত সদস্যরাই তা দেখতে পারেন। লগইন বা বিনামূল্যে নিবন্ধন করুন — তারপর সরাসরি এই খোঁজে ফিরে আসবেন।
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <x-ui.button :href="route('login')" size="lg"><x-lucide-log-in aria-hidden="true" />লগইন</x-ui.button>
                        <x-ui.button :href="route('register')" size="lg" variant="soft">ডোনার হিসেবে যুক্ত হন</x-ui.button>
                    </div>
                </div>
                <div data-reveal class="rounded-xl bg-crimson p-6 text-white shadow-card sm:p-8 lg:col-span-5">
                    <h2 class="text-2xl font-bold text-white">জরুরি রক্ত প্রয়োজন?</h2>
                    <p class="mt-2 text-white/85">অনুরোধ পোস্ট করতে অ্যাকাউন্ট লাগে না। অনুরোধ তালিকা দেখে ডোনাররা সরাসরি আপনাকে কল করবেন।</p>
                    <x-ui.button :href="route('requests.create')" class="mt-5" variant="inverse"><x-lucide-plus aria-hidden="true" />অনুরোধ পোস্ট করুন</x-ui.button>
                </div>
            </div>
        @endauth
    </div>
</x-layouts.app>
