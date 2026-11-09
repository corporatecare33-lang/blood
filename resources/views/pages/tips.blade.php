<x-layouts.app>
    <x-site.page-header title="রক্তদানের আগে ও পরে" intro="স্বেচ্ছাসেবী ডোনারদের জন্য সাধারণ নির্দেশনা। রক্ত নেওয়ার আগে হাসপাতাল বা ব্লাড ব্যাংক সবসময় পরীক্ষা করে চূড়ান্ত সিদ্ধান্ত নেয়।" />

    <div class="container-page section pt-5! grid gap-6 lg:grid-cols-12 lg:items-start">
        <div class="grid gap-6 lg:col-span-7">
            <section aria-labelledby="eligible-heading">
                <h2 id="eligible-heading" data-reveal class="text-2xl font-bold">আমি কি রক্ত দিতে পারব?</h2>
                <dl data-reveal-stagger class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ([
                        ['বয়স', '১৮ থেকে ৬৫ বছর', 'রক্তদূতের ডোনারদের বয়স কমপক্ষে ১৮ বছর।'],
                        ['ওজন', 'কমপক্ষে ৫০ কেজি', ''],
                        ['হিমোগ্লোবিন', 'নারী ১২.০, পুরুষ ১৩.০ গ্রাম/ডেসিলিটার', 'রক্ত দেওয়ার আগে পরীক্ষা করা হয়।'],
                        ['দুই রক্তদানের মাঝে', 'পুরুষ ১২ সপ্তাহ, নারী ১৬ সপ্তাহ', 'রক্তদূত ৯০ দিন পর আবার তালিকায় দেখায়।'],
                    ] as [$term, $value, $note])
                        <div class="card p-4">
                            <dt class="text-[0.9375rem] text-ink-soft">{{ $term }}</dt>
                            <dd class="mt-0.5 text-lg font-bold">{{ $value }}</dd>
                            @if ($note)<dd class="mt-1 text-sm text-ink-soft">{{ $note }}</dd>@endif
                        </div>
                    @endforeach
                </dl>
                <p class="mt-2 text-sm text-ink-soft">সূত্র: বিশ্ব স্বাস্থ্য সংস্থা (WHO), <cite lang="en">Blood donor selection: guidelines on assessing donor suitability for blood donation</cite> (২০১২)।</p>
            </section>

            <div class="grid gap-3 sm:grid-cols-2">
                <section data-reveal class="card p-5">
                    <h2 class="font-sans text-lg font-bold">রক্ত দেওয়ার আগে</h2>
                    <ul class="mt-3 space-y-2.5">
                        @foreach (['আগের রাতে ভালো করে ঘুমান।', 'কয়েক ঘণ্টা আগে স্বাভাবিক খাবার খান।', 'বেশি করে পানি পান করুন।', 'জাতীয় পরিচয়পত্র বা ছবিসহ অন্য পরিচয়পত্র সাথে নিন।', 'যে ওষুধ খাচ্ছেন বা সম্প্রতি অসুস্থ ছিলেন কিনা — কর্মীদের জানান।'] as $tip)
                            <li class="flex gap-2.5"><x-lucide-check class="mt-1 size-5 shrink-0 text-leaf" aria-hidden="true" />{{ $tip }}</li>
                        @endforeach
                    </ul>
                </section>
                <section data-reveal class="card p-5">
                    <h2 class="font-sans text-lg font-bold">রক্ত দেওয়ার পরে</h2>
                    <ul class="mt-3 space-y-2.5">
                        @foreach (['১০–১৫ মিনিট বিশ্রাম নিন, কিছু পান করুন ও হালকা খান।', 'পরের এক দিন বেশি করে পানি পান করুন।', 'কয়েক ঘণ্টা ব্যান্ডেজ খুলবেন না।', 'সেদিন ভারী কাজ ও কঠোর ব্যায়াম এড়িয়ে চলুন।', 'মাথা ঘুরলে শুয়ে পড়ুন, পা একটু উঁচু করে রাখুন।'] as $tip)
                            <li class="flex gap-2.5"><x-lucide-check class="mt-1 size-5 shrink-0 text-leaf" aria-hidden="true" />{{ $tip }}</li>
                        @endforeach
                    </ul>
                    <p class="mt-4 text-sm text-ink-soft">রক্ত দেওয়ার পর <a href="{{ route('account') }}" wire:navigate class="font-semibold text-crimson underline">প্রোফাইলে</a> শেষ রক্তদানের তারিখ হালনাগাদ করুন, যাতে কেউ খুব তাড়াতাড়ি আপনাকে ফোন না করেন।</p>
                </section>
            </div>
        </div>

        <aside data-reveal class="card p-5 lg:col-span-5" aria-labelledby="compat-heading">
            <h2 id="compat-heading" class="font-sans text-lg font-bold">কে কার কাছ থেকে রক্ত নিতে পারেন</h2>
            <p class="mt-1 text-sm text-ink-soft">লোহিত কণিকার সামঞ্জস্য। রক্ত দেওয়ার আগে ব্লাড ব্যাংক সবসময় ক্রস-ম্যাচ করে।</p>
            <table class="mt-4 w-full text-left">
                <thead>
                    <tr class="border-b border-border text-sm text-ink-soft">
                        <th scope="col" class="py-2 pr-4 font-medium">রোগী</th>
                        <th scope="col" class="py-2 font-medium">যাঁদের কাছ থেকে নিতে পারেন</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border" lang="en">
                    @foreach (\App\Enums\BloodGroup::cases() as $group)
                        <tr>
                            <th scope="row" class="group-glyph py-2.5 pr-4 text-2xl text-crimson">{{ $group->value }}</th>
                            <td class="py-2.5">
                                <span class="flex flex-wrap gap-1.5">
                                    @foreach ($group->canReceiveFrom() as $donor)
                                        <span class="rounded-md border border-crimson/30 px-2 py-0.5 text-[0.9375rem] font-semibold text-crimson">{{ $donor->value }}</span>
                                    @endforeach
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </aside>
    </div>

    <section class="container-page max-w-3xl! pb-4" aria-labelledby="faq-heading">
        <h2 id="faq-heading" data-reveal class="text-2xl font-bold">সাধারণ প্রশ্ন</h2>
        <div data-reveal-stagger class="mt-3 grid gap-2">
            @foreach ($faq as $item)
                <details class="card group px-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-4 text-[1.0625rem] font-semibold [&::-webkit-details-marker]:hidden">
                        {{ $item['q'] }}
                        <x-lucide-chevron-down class="size-5 shrink-0 text-crimson transition-transform group-open:rotate-180" aria-hidden="true" />
                    </summary>
                    <p class="pb-4 text-ink-soft">{{ $item['a'] }}</p>
                </details>
            @endforeach
        </div>
    </section>
</x-layouts.app>
