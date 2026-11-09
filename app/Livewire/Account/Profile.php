<?php

namespace App\Livewire\Account;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Occupation;
use App\Enums\RequestStatus;
use App\Livewire\Concerns\WithLocationFields;
use App\Models\BloodRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Profile extends Component
{
    use WithLocationFields;

    public string $name = '';

    public string $gender = '';

    public string $blood_group = '';

    public string $occupation = '';

    public string $date_of_birth = '';

    public string $last_donated_at = '';

    public $division_id = null;

    public $district_id = null;

    public $thana_id = null;

    public string $address = '';

    public bool $is_available = true;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->fill([
            'name' => $user->name,
            'gender' => $user->gender->value,
            'blood_group' => $user->blood_group->value,
            'occupation' => $user->occupation->value,
            'date_of_birth' => $user->date_of_birth->toDateString(),
            'last_donated_at' => $user->last_donated_at?->toDateString() ?? '',
            'division_id' => $user->division_id,
            'district_id' => $user->district_id,
            'thana_id' => $user->thana_id,
            'address' => $user->address ?? '',
            'is_available' => $user->is_available,
        ]);
    }

    public function saveProfile(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'gender' => ['required', new Enum(Gender::class)],
            'blood_group' => ['required', new Enum(BloodGroup::class)],
            'occupation' => ['required', new Enum(Occupation::class)],
            'date_of_birth' => ['required', 'date', 'before_or_equal:'.now()->subYears(18)->toDateString()],
            'last_donated_at' => ['nullable', 'date', 'before_or_equal:today'],
            'division_id' => ['required', 'exists:divisions,id'],
            'district_id' => ['required', Rule::exists('districts', 'id')->where('division_id', $this->division_id)],
            'thana_id' => ['nullable', Rule::exists('thanas', 'id')->where('district_id', $this->district_id)],
            'address' => ['nullable', 'string', 'max:255'],
            'is_available' => ['boolean'],
        ]);

        auth()->user()->update([
            ...$data,
            'thana_id' => $data['thana_id'] ?: null,
            'address' => $data['address'] ?: null,
            'last_donated_at' => $data['last_donated_at'] ?: null,
        ]);

        $this->dispatch('notify', message: 'প্রোফাইল সংরক্ষণ হয়েছে।');
    }

    public function donatedToday(): void
    {
        $this->last_donated_at = now()->toDateString();
        auth()->user()->update(['last_donated_at' => $this->last_donated_at]);
        $this->dispatch('notify', message: 'রক্ত দেওয়ার জন্য ধন্যবাদ! '.\App\Support\Bn::num(config('bandhan.donation_gap_days')).' দিন পর আবার তালিকায় দেখাবেন।');
    }

    public function toggleAvailability(): void
    {
        $this->is_available = ! $this->is_available;
        auth()->user()->update(['is_available' => $this->is_available]);
        $this->dispatch('notify', message: $this->is_available ? 'আপনি আবার ডোনার তালিকায় দেখাচ্ছেন।' : 'আপনাকে ডোনার তালিকা থেকে লুকানো হয়েছে।');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        auth()->user()->update(['password' => $this->password]);
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('notify', message: 'পাসওয়ার্ড পরিবর্তন হয়েছে।');
    }

    public function setRequestStatus(int $id, string $status): void
    {
        $request = BloodRequest::where('user_id', auth()->id())->findOrFail($id);
        $request->update(['status' => RequestStatus::from($status)]);
        unset($this->myRequests);
        $this->dispatch('notify', message: 'অনুরোধের অবস্থা: '.RequestStatus::from($status)->label());
    }

    #[Computed]
    public function myRequests()
    {
        return BloodRequest::where('user_id', auth()->id())->with('district')->latest()->limit(10)->get();
    }

    public function render()
    {
        return view('livewire.account.profile', ['user' => auth()->user()]);
    }
}
