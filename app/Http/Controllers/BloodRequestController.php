<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\View\View;

class BloodRequestController extends Controller
{
    public function index(): View
    {
        Seo::make()
            ->title('রক্তের অনুরোধ')
            ->description('সারা বাংলাদেশের রোগী ও পরিবারের খোলা রক্তের অনুরোধ। রক্তের গ্রুপ ও জেলা দিয়ে খুঁজুন এবং যোগাযোগকারীকে সরাসরি কল করুন।')
            ->breadcrumbs(['রক্তের অনুরোধ' => route('requests.index')]);

        return view('pages.requests.index');
    }

    public function create(): View
    {
        Seo::make()
            ->title('রক্তের অনুরোধ পোস্ট করুন')
            ->appBar('নতুন অনুরোধ')
            ->description('রোগীর রক্ত দরকার? রক্তের গ্রুপ, হাসপাতাল আর সময় লিখে অনুরোধ পোস্ট করুন — কাছের রক্তদূত ডোনাররা আপনাকে কল করবেন।')
            ->breadcrumbs(['রক্তের অনুরোধ' => route('requests.index'), 'নতুন অনুরোধ' => route('requests.create')]);

        return view('pages.requests.create');
    }
}
