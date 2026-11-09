<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

/** The signed-in admin's own name, email and password. */
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email ?? '';
    }

    public function saveDetails(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);

        Auth::user()->update(['name' => $data['name'], 'email' => $data['email'] ?: null]);
        $this->dispatch('notify', message: 'আপনার তথ্য হালনাগাদ হয়েছে।');
    }

    public function savePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers(), 'different:current_password'],
        ], [
            'current_password.current_password' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।',
            'password.different' => 'নতুন পাসওয়ার্ড আগেরটির চেয়ে আলাদা হতে হবে।',
        ]);

        Auth::user()->update(['password' => $this->password]);
        // Sign out every other device that still holds the old password.
        Auth::logoutOtherDevices($this->password);
        session()->regenerate();

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('notify', message: 'পাসওয়ার্ড বদলানো হয়েছে। অন্য সব ডিভাইস থেকে লগআউট করা হয়েছে।');
    }

    public function render()
    {
        return view('livewire.admin.profile');
    }
}
