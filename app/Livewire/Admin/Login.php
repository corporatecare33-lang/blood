<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Support\Bn;
use App\Support\Mobile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/** Sign-in for the admin panel. Only admins get a session; donors are told to use the public login. */
class Login extends Component
{
    public string $mobile = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $this->mobile = Mobile::normalize($this->mobile);

        $this->validate([
            'mobile' => ['required', Mobile::RULE],
            'password' => ['required', 'string'],
        ], [
            'mobile.regex' => '১১ অঙ্কের মোবাইল নম্বর দিন (01XXXXXXXXX)।',
        ]);

        $key = 'admin-login|'.$this->mobile.'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('mobile', 'অনেকবার চেষ্টা হয়েছে। '.Bn::num(RateLimiter::availableIn($key)).' সেকেন্ড পর আবার চেষ্টা করুন।');

            return null;
        }

        $credentials = ['mobile' => $this->mobile, 'password' => $this->password, 'is_active' => true];
        $user = Auth::validate($credentials) ? User::firstWhere('mobile', $this->mobile) : null;

        if (! $user?->isAdmin()) {
            RateLimiter::hit($key, 60);
            $this->reset('password');
            $this->addError('mobile', $user
                ? 'এই অ্যাকাউন্টের অ্যাডমিন অধিকার নেই। ডোনাররা সাধারণ লগইন পাতা ব্যবহার করুন।'
                : 'মোবাইল নম্বর বা পাসওয়ার্ড সঠিক নয়।');

            return null;
        }

        RateLimiter::clear($key);
        Auth::login($user, $this->remember);
        session()->regenerate();

        return $this->redirectIntended(route('admin.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.login');
    }
}
