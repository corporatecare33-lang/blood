<?php

namespace App\Livewire\Auth;

use App\Support\Mobile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class LoginForm extends Component
{
    public string $mobile = '';

    public string $password = '';

    public bool $remember = true;

    public function login()
    {
        $this->mobile = Mobile::normalize($this->mobile);

        $this->validate([
            'mobile' => ['required', Mobile::RULE],
            'password' => ['required', 'string'],
        ], [
            'mobile.regex' => 'যে ১১ অঙ্কের নম্বরে নিবন্ধন করেছেন সেটি দিন।',
        ]);

        $key = Str::lower($this->mobile).'|'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('mobile', 'অনেকবার চেষ্টা হয়েছে। '.\App\Support\Bn::num($seconds).' সেকেন্ড পর আবার চেষ্টা করুন।');

            return null;
        }

        if (! Auth::attempt(['mobile' => $this->mobile, 'password' => $this->password, 'is_active' => true], $this->remember)) {
            RateLimiter::hit($key, 60);
            $this->addError('mobile', 'মোবাইল নম্বর বা পাসওয়ার্ড সঠিক নয়।');
            $this->reset('password');

            return null;
        }

        RateLimiter::clear($key);
        session()->regenerate();

        $default = Auth::user()->isAdmin() ? route('admin.dashboard') : route('donors.search');

        return $this->redirectIntended($default, navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
