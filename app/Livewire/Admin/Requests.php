<?php

namespace App\Livewire\Admin;

use App\Enums\BloodGroup;
use App\Enums\RequestStatus;
use App\Models\BloodRequest;
use App\Support\Bn;
use App\Support\Mobile;
use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Requests extends Component
{
    use WithPagination;

    private const FILTERS = ['status', 'q', 'group'];

    /** open | fulfilled | closed | all */
    #[Url(except: 'open')]
    public string $status = 'open';

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $group = '';

    /** @var list<int> */
    public array $selected = [];

    // ---- Edit sheet ---------------------------------------------------------

    public bool $showEditor = false;

    public ?int $editingId = null;

    public string $patient_name = '';

    public string $blood_group = '';

    public int $bags = 1;

    public string $needed_at = '';

    public string $hospital = '';

    public $district_id = null;

    public string $contact_name = '';

    public string $contact_mobile = '';

    public string $note = '';

    public string $request_status = 'open';

    public function updated(string $property): void
    {
        if (in_array($property, self::FILTERS, true)) {
            $this->resetPage();
            $this->selected = [];
        }
    }

    public function updatedPaginators(): void
    {
        $this->selected = [];
    }

    public function setStatus(int $id, string $status): void
    {
        $request = BloodRequest::findOrFail($id);
        $request->update(['status' => RequestStatus::from($status)]);
        $this->dispatch('notify', message: $request->patient_name.'-এর অনুরোধ: '.$request->status->label().'।');
    }

    public function bulkStatus(string $status): void
    {
        $status = RequestStatus::from($status);
        $count = BloodRequest::whereIn('id', $this->selected)->update(['status' => $status]);
        SiteCache::forgetRequests();
        $this->selected = [];
        $this->dispatch('notify', message: Bn::num($count).'টি অনুরোধ: '.$status->label().'।');
    }

    public function delete(int $id): void
    {
        BloodRequest::findOrFail($id)->delete();
        $this->selected = array_values(array_diff($this->selected, [$id]));
        $this->dispatch('notify', message: 'অনুরোধ মুছে ফেলা হয়েছে।');
    }

    public function bulkDelete(): void
    {
        $count = BloodRequest::whereIn('id', $this->selected)->get()->each->delete()->count();
        $this->selected = [];
        $this->dispatch('notify', message: Bn::num($count).'টি অনুরোধ মুছে ফেলা হয়েছে।');
    }

    public function edit(int $id): void
    {
        $r = BloodRequest::findOrFail($id);
        $this->resetValidation();
        $this->fill([
            'editingId' => $r->id,
            'patient_name' => $r->patient_name,
            'blood_group' => $r->blood_group->value,
            'bags' => $r->bags,
            'needed_at' => $r->needed_at->format('Y-m-d\TH:i'),
            'hospital' => $r->hospital,
            'district_id' => $r->district_id,
            'contact_name' => $r->contact_name,
            'contact_mobile' => $r->contact_mobile,
            'note' => $r->note ?? '',
            'request_status' => $r->status->value,
            'showEditor' => true,
        ]);
    }

    public function save(): void
    {
        $request = BloodRequest::findOrFail($this->editingId);
        $this->contact_mobile = Mobile::normalize($this->contact_mobile);

        $data = $this->validate([
            'patient_name' => ['required', 'string', 'max:80'],
            'blood_group' => ['required', new Enum(BloodGroup::class)],
            'bags' => ['required', 'integer', 'min:1', 'max:10'],
            'needed_at' => ['required', 'date'],
            'hospital' => ['required', 'string', 'max:150'],
            'district_id' => ['required', 'exists:districts,id'],
            'contact_name' => ['required', 'string', 'max:80'],
            'contact_mobile' => ['required', Mobile::RULE],
            'note' => ['nullable', 'string', 'max:500'],
            'request_status' => ['required', Rule::enum(RequestStatus::class)],
        ], ['contact_mobile.regex' => '১১ অঙ্কের মোবাইল নম্বর দিন (01XXXXXXXXX)।']);

        $data['status'] = $data['request_status'];
        $data['note'] = $data['note'] ?: null;
        unset($data['request_status']);

        $request->update($data);
        $this->showEditor = false;
        $this->dispatch('notify', message: 'অনুরোধ হালনাগাদ হয়েছে।');
    }

    private function filtered(bool $withStatus = true): Builder
    {
        $group = BloodGroup::fromSlug($this->group);
        $term = trim($this->q);

        return BloodRequest::query()
            ->when($withStatus && $this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($group, fn ($q) => $q->where('blood_group', $group))
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('patient_name', 'like', "%{$term}%")
                ->orWhere('hospital', 'like', "%{$term}%")
                ->orWhere('contact_name', 'like', "%{$term}%")
                ->orWhere('contact_mobile', 'like', '%'.Mobile::normalize($term).'%')));
    }

    #[Computed]
    public function requests()
    {
        // Open requests: soonest need first. Everything else: newest first.
        return $this->filtered()->with('district')
            ->when($this->status === 'open', fn ($q) => $q->orderBy('needed_at'), fn ($q) => $q->latest())
            ->paginate(20);
    }

    public function render()
    {
        $counts = $this->filtered(withStatus: false)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('livewire.admin.requests', [
            'tabs' => [
                'open' => ['খোলা', (int) ($counts['open'] ?? 0)],
                'fulfilled' => ['ডোনার পাওয়া গেছে', (int) ($counts['fulfilled'] ?? 0)],
                'closed' => ['বন্ধ', (int) ($counts['closed'] ?? 0)],
                'all' => ['সব', (int) $counts->sum()],
            ],
            'allDistricts' => SiteCache::districts(),
        ]);
    }
}
