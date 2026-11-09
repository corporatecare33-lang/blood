<?php

// Verified organisation facts. See CLAUDE.md §2 before adding anything here.
return [
    'name' => 'BloodBond',
    'name_bn' => 'ব্লাডবন্ড',
    'tagline_bn' => 'রক্ত দিন জীবন বাঁচান। রক্ত নিন নিজে বাঁচুন।',
    'subtitle_bn' => 'স্বেচ্ছায় রক্তদাতাদের সংগঠন',
    'description' => 'BloodBond is a free, voluntary blood donor network in Bangladesh that connects people who need blood with donors by blood group and location.',
    'description_bn' => 'ব্লাডবন্ড বাংলাদেশের একটি বিনামূল্যের স্বেচ্ছাসেবী রক্তদাতা নেটওয়ার্ক — রক্তের গ্রুপ ও এলাকা দিয়ে রক্তপ্রার্থীর সাথে রক্তদাতার যোগাযোগ করিয়ে দেয়।',

    'email' => 'bandhanblood24@gmail.com',

    // Name only. The owner asked (2026-11-09) that the site link to no other website.
    'sponsor' => 'TohsinBD',

    'conditions' => [
        'স্বেচ্ছায় রক্ত দিবেন।',
        'রক্তদাতাকে অবশ্যই ১৮ বছর হতে হবে।',
        'রক্তদাতাকে অবশ্যই অধুমপায়ী এবং মাদক গ্রহন মুক্ত থাকতে হবে।',
        'রক্ত গ্রহীতা ব্যক্তি অবশ্যই রক্তদাতা ব্যক্তির যাতায়াত খরচ এবং প্রয়োজনীয় খাবারের ব্যবস্থা করবেন।',
        'বর্তমান অবস্থান উল্লেখ করতে হবে।',
    ],

    'why' => ['১০০% স্বয়ংক্রিয়', 'সবসময় বিনামূল্যে', '২৪ ঘণ্টা সেবা', 'তথ্য সম্পূর্ণ সুরক্ষিত'],

    // Minimum days between whole-blood donations before a donor is shown as eligible.
    'donation_gap_days' => 90,

    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'api_url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID'),
    ],

    'admin' => [
        'name' => env('ADMIN_NAME', 'BloodBond Admin'),
        'mobile' => env('ADMIN_MOBILE'),
        'password' => env('ADMIN_PASSWORD'),
    ],
];
