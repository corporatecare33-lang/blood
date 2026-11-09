<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function register(): View
    {
        Seo::make()
            ->title('ডোনার রেজিস্ট্রেশন')
            ->description('স্বেচ্ছাসেবী রক্তদাতা হিসেবে রক্তদূতে যুক্ত হন। রক্তের গ্রুপ ও এলাকা দিয়ে নিবন্ধন করুন, যাতে প্রয়োজনের সময় কাছের মানুষ আপনাকে খুঁজে পান।')
            ->breadcrumbs(['ডোনার রেজিস্ট্রেশন' => route('register')]);

        return view('pages.auth.register');
    }

    public function login(): View
    {
        Seo::make()
            ->title('লগইন')
            ->description('মোবাইল নম্বর দিয়ে রক্তদূতে লগইন করুন — ডোনার খুঁজুন ও আপনার প্রোফাইল ঠিক রাখুন।')
            ->breadcrumbs(['লগইন' => route('login')]);

        return view('pages.auth.login');
    }

    public function forgotPassword(): View
    {
        Seo::make()
            ->title('পাসওয়ার্ড রিসেট')
            ->description('মোবাইলে পাঠানো এককালীন কোড দিয়ে রক্তদূতের পাসওয়ার্ড রিসেট করুন।')
            ->breadcrumbs(['লগইন' => route('login'), 'পাসওয়ার্ড রিসেট' => route('password.request')])
            ->noindex();

        return view('pages.auth.forgot-password');
    }

    public function account(): View
    {
        Seo::make()->title('আমার প্রোফাইল')->description('আপনার রক্তদূত ডোনার প্রোফাইল পরিচালনা করুন।')->noindex();

        return view('pages.auth.account');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'আপনি লগআউট করেছেন।');
    }

    public function adminLogout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'আপনি অ্যাডমিন প্যানেল থেকে লগআউট করেছেন।');
    }
}
