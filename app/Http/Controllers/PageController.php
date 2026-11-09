<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use App\Support\SiteCache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        Seo::make()
            ->description('রক্তের গ্রুপ ও জেলা দিয়ে বাংলাদেশে স্বেচ্ছাসেবী রক্তদাতা খুঁজুন, রক্তের অনুরোধ পোস্ট করুন অথবা ডোনার হিসেবে যুক্ত হন। রক্তদূত সবার জন্য বিনামূল্যে।')
            ->canonical(url('/'));

        return view('pages.home', [
            'requests' => SiteCache::latestRequests(3),
            'donorCount' => SiteCache::donorCount(),
        ]);
    }

    public function about(): View
    {
        Seo::make()
            ->title('আমাদের সম্পর্কে')
            ->description('রক্তদূত বাংলাদেশের একটি বিনামূল্যের স্বেচ্ছাসেবী রক্তদাতা নেটওয়ার্ক। কীভাবে কাজ করে, কারা রক্ত দিতে পারেন এবং ডোনারের তথ্য কীভাবে সুরক্ষিত থাকে জানুন।')
            ->breadcrumbs(['আমাদের সম্পর্কে' => route('about')])
            ->schema(['@type' => 'AboutPage', 'url' => route('about'), 'name' => 'রক্তদূত সম্পর্কে']);

        return view('pages.about', [
            'sections' => SiteCache::about(),
            'donorCount' => SiteCache::donorCount(),
        ]);
    }

    public function gallery(): View
    {
        Seo::make()
            ->title('গ্যালারি')
            ->description('রক্তদূতের রক্তদান কর্মসূচি ও স্বেচ্ছাসেবী কার্যক্রমের ছবি।')
            ->breadcrumbs(['গ্যালারি' => route('gallery')]);

        return view('pages.gallery', ['items' => SiteCache::gallery()]);
    }

    public function tips(): View
    {
        $faq = trans('tips.faq');

        Seo::make()
            ->title('রক্তদানের আগে ও পরে: ডোনার টিপস')
            ->appBar('ডোনার টিপস')
            ->description('কারা রক্ত দিতে পারেন, কত দিন পর পর দেওয়া যায়, আর রক্তদানের আগে-পরে কী করবেন — স্বেচ্ছাসেবী রক্তদাতাদের জন্য সাধারণ নির্দেশনা।')
            ->breadcrumbs(['ডোনার টিপস' => route('tips')])
            ->schema([
                '@type' => 'FAQPage',
                'mainEntity' => collect($faq)->map(fn ($item) => [
                    '@type' => 'Question',
                    'name' => $item['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
                ])->all(),
            ]);

        return view('pages.tips', ['faq' => $faq]);
    }

    public function terms(): View
    {
        Seo::make()
            ->title('ব্যবহারের শর্তাবলি')
            ->description('ডোনার বা রক্তপ্রার্থী হিসেবে রক্তদূত ব্যবহারের নিয়মাবলি।')
            ->breadcrumbs(['ব্যবহারের শর্তাবলি' => route('terms')]);

        return view('pages.terms');
    }

    public function privacy(): View
    {
        Seo::make()
            ->title('গোপনীয়তা নীতি')
            ->description('রক্তদূত ডোনারদের কোন তথ্য সংগ্রহ করে, কারা তা দেখতে পারেন এবং কীভাবে তা মুছে ফেলা যায়।')
            ->breadcrumbs(['গোপনীয়তা নীতি' => route('privacy')]);

        return view('pages.privacy');
    }
}
