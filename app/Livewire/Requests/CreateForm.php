<?php

namespace App\Livewire\Requests;

use App\Enums\BloodGroup;
use App\Enums\RequestStatus;
use App\Models\BloodRequest;
use App\Support\Bn;
use App\Support\Mobile;
use App\Support\SiteCache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Enum;
use Livewire\Component;

class CreateForm extends Component
{
    public string $patient_name = '';

    public string $blood_group = '';

    public int $bags = 1;

    public string $needed_at = '';

    public string $hospital = '';

    public string $district_id = '';

    public string $contact_name = '';

    public string $contact_mobile = '';

    public string $note = '';

    /** Honeypot: real people never see or fill this. */
    public string $website = '';

    public function mount(): void
    {
        if ($user = auth()->user()) {
            $this->contact_name = $user->name;
            $this->contact_mobile = $user->mobile;
            $this->district_id = (string) $user->district_id;
        }
    }

    protected function rules(): array
    {
        return [
            'patient_name' => ['required', 'string', 'max:80'],
            'blood_group' => ['required', new Enum(BloodGroup::class)],
            'bags' => ['required', 'integer', 'min:1', 'max:10'],
            'needed_at' => ['required', 'date', 'after:-1 hour', 'before:+60 days'],
            'hospital' => ['required', 'string', 'max:150'],
            'district_id' => ['required', 'exists:districts,id'],
            'contact_name' => ['required', 'string', 'max:80'],
            'contact_mobile' => ['required', Mobile::RULE],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'contact_mobile.regex' => '১১ অঙ্কের বাংলাদেশি মোবাইল নম্বর দিন, যেমন 017XXXXXXXX।',
            'needed_at.after' => 'এমন সময় দিন যা এখনো পার হয়নি।',
            'needed_at.before' => 'সর্বোচ্চ ৬০ দিন আগে পর্যন্ত অনুরোধ পোস্ট করা যায়।',
        ];
    }

    public function save()
    {
        $this->contact_mobile = Mobile::normalize($this->contact_mobile);

        if ($this->website !== '') {
            return $this->redirectRoute('requests.index', navigate: true);
        }

        $key = 'blood-request:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('patient_name', 'এই সংযোগ থেকে অনেক অনুরোধ পোস্ট হয়েছে। এক ঘণ্টা পর আবার চেষ্টা করুন।');

            return null;
        }

        $data = $this->validate();
        RateLimiter::hit($key, 3600);

        BloodRequest::create($data + [
            'user_id' => auth()->id(),
            'status' => RequestStatus::Open,
            'note' => $data['note'] ?: null,
        ]);

        SiteCache::forgetRequests();
        session()->flash('status', 'অনুরোধ পোস্ট হয়েছে। ফোন কাছে রাখুন — ডোনাররা '.$data['contact_mobile'].' নম্বরে কল করবেন।');

        return $this->redirectRoute('requests.index', navigate: true);
    }

    public function render()
    {
        $today = now()->startOfDay();

        return view('livewire.requests.create-form', [
            'districts' => SiteCache::districts(),
            // Quick-pick day chips; any other date goes through the date input.
            'days' => collect(['আজ', 'কাল', 'পরশু'])->map(fn ($name, $i) => [
                'value' => $today->copy()->addDays($i)->toDateString(),
                'name' => $name,
                'date' => Bn::num($today->copy()->addDays($i)->locale('bn')->translatedFormat('j F')),
            ])->all(),
            'hours' => collect(range(0, 23))->mapWithKeys(fn ($h) => [
                sprintf('%02d:00', $h) => Bn::time($today->copy()->setHour($h)),
            ])->all(),
        ]);
    }
}
