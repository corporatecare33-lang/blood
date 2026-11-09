<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\OtpService;
use App\Support\Mobile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class ForgotPassword extends Component
{
    #[Locked]
    public string $step = 'request';

    public string $mobile = '';

    public string $code = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function sendCode(OtpService $otp): void
    {
        $this->mobile = Mobile::normalize($this->mobile);
        $this->validate(['mobile' => ['required', Mobile::RULE]], ['mobile.regex' => 'যে ১১ অঙ্কের নম্বরে নিবন্ধন করেছেন সেটি দিন।']);

        $key = 'otp:'.$this->mobile;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $this->addError('mobile', 'অনেকবার কোড চাওয়া হয়েছে। '.\App\Support\Bn::num((int) ceil(RateLimiter::availableIn($key) / 60)).' মিনিট পর আবার চেষ্টা করুন।');

            return;
        }
        RateLimiter::hit($key, 900);

        // Same response whether or not the number exists, so the form can't be used to find members.
        if (User::where('mobile', $this->mobile)->where('is_active', true)->exists()) {
            try {
                $otp->send($this->mobile, 'password_reset');
            } catch (Throwable $e) {
                report($e);
                $this->addError('mobile', 'এই মুহূর্তে এসএমএস পাঠানো যায়নি। কয়েক মিনিট পর আবার চেষ্টা করুন।');

                return;
            }
        }

        $this->step = 'verify';
    }

    public function resetPassword(OtpService $otp)
    {
        $this->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! $otp->verify($this->mobile, 'password_reset', $this->code)) {
            $this->addError('code', 'কোডটি ভুল অথবা মেয়াদ শেষ। এসএমএস আবার দেখুন বা নতুন কোড নিন।');

            return null;
        }

        $user = User::where('mobile', $this->mobile)->firstOrFail();
        $user->update(['password' => $this->password]);

        Auth::login($user);
        session()->regenerate();
        session()->flash('status', 'আপনার পাসওয়ার্ড পরিবর্তন হয়েছে।');

        return $this->redirectRoute('account', navigate: true);
    }

    public function startOver(): void
    {
        $this->reset();
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
