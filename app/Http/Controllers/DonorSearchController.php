<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonorSearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        Seo::make()
            ->title('ডোনার তালিকা')
            ->description('রক্তের গ্রুপ, বিভাগ, জেলা ও থানা দিয়ে বাংলাদেশের স্বেচ্ছাসেবী রক্তদাতা খুঁজুন। ডোনারের মোবাইল নম্বর দেখতে লগইন করুন।')
            ->breadcrumbs(['ডোনার তালিকা' => route('donors.search')])
            ->canonical(route('donors.search'));

        // Guests see a sign-in prompt; after logging in they come back to the same search.
        if (! $request->user()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return view('pages.donors.search');
    }
}
