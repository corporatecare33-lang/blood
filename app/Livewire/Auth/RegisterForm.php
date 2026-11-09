<?php

namespace App\Livewire\Auth;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Occupation;
use App\Enums\Role;
use App\Livewire\Concerns\WithLocationFields;
use App\Models\User;
use App\Support\Mobile;
use App\Support\SiteCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class RegisterForm extends Component
{
    use WithLocationFields;

    public string $name = '';

    public string $mobile = '';

    public string $gender = '';

    public $division_id = null;

    public $district_id = null;

    public $thana_id = null;

    public string $address = '';

    public string $date_of_birth = '';

    public string $last_donated_at = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $blood_group = '';

    public string $occupation = '';

    public bool $is_available = true;

    public bool $agree = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'mobile' => ['required', Mobile::RULE, Rule::unique('users', 'mobile')],
            'gender' => ['required', new Enum(Gender::class)],
            'division_id' => ['required', 'exists:divisions,id'],
            'district_id' => ['required', Rule::exists('districts', 'id')->where('division_id', $this->division_id)],
            'thana_id' => ['nullable', Rule::exists('thanas', 'id')->where('district_id', $this->district_id)],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString(), 'after:'.now()->subYears(80)->toDateString()],
            'last_donated_at' => ['nullable', 'date', 'before_or_equal:today'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'blood_group' => ['required', new Enum(BloodGroup::class)],
            'occupation' => ['required', new Enum(Occupation::class)],
            'is_available' => ['boolean'],
            'agree' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'mobile.regex' => '১১ অঙ্কের বাংলাদেশি মোবাইল নম্বর দিন, যেমন 017XXXXXXXX।',
            'mobile.unique' => 'এই নম্বরে আগেই নিবন্ধন করা আছে। লগইন করুন।',
            'date_of_birth.before_or_equal' => 'রক্তদাতার বয়স কমপক্ষে ১৮ বছর হতে হবে।',
            'date_of_birth.after' => 'সঠিক জন্মতারিখ দিন।',
            'agree.accepted' => 'নিবন্ধনের জন্য আমাদের শর্তসমূহে রাজি হতে হবে।',
        ];
    }

    public function updatedMobile(): void
    {
        $this->mobile = Mobile::normalize($this->mobile);
    }

    public function register()
    {
        $this->mobile = Mobile::normalize($this->mobile);

        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('mobile', 'এই সংযোগ থেকে অনেকবার নিবন্ধনের চেষ্টা হয়েছে। এক ঘণ্টা পর আবার চেষ্টা করুন।');

            return null;
        }

        $data = $this->validate();
        RateLimiter::hit($key, 3600);

        $user = User::create([
            ...collect($data)->except(['agree'])->all(),
            'thana_id' => $data['thana_id'] ?: null,
            'address' => $data['address'] ?: null,
            'last_donated_at' => $data['last_donated_at'] ?: null,
            'role' => Role::Donor,
        ]);

        SiteCache::forgetDonorCount();
        Auth::login($user);
        session()->regenerate();
        session()->flash('status', 'রক্তদূতে স্বাগতম, '.$user->name.'! আপনি এখন ডোনার তালিকায় আছেন।');

        return $this->redirectRoute('account', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register-form');
    }
}
