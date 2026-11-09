<?php

namespace App\Livewire\Admin;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Occupation;
use App\Enums\Role;
use App\Livewire\Concerns\WithLocationFields;
use App\Models\User;
use App\Support\Mobile;
use App\Support\SiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Donors extends Component
{
    use WithLocationFields, WithPagination;

    private const FILTERS = ['q', 'group', 'district', 'status'];

    private const SORTABLE = ['created_at', 'name', 'last_donated_at'];

    #[Url(except: '')]
    public string $q = '';

    #[Url(except: '')]
    public string $group = '';

    #[Url(except: '')]
    public string $district = '';

    /** '' | active | inactive | eligible | unavailable */
    #[Url(except: '')]
    public string $status = '';

    #[Url(except: 'created_at')]
    public string $sort = 'created_at';

    #[Url(except: 'desc')]
    public string $dir = 'desc';

    /** @var list<int> ids ticked on the current page */
    public array $selected = [];

    // ---- Edit sheet ---------------------------------------------------------

    public bool $showEditor = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $mobile = '';

    public string $email = '';

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

    public bool $is_active = true;

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

    public function sortBy(string $column): void
    {
        abort_unless(in_array($column, self::SORTABLE, true), 422);

        if ($this->sort === $column) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->dir = $column === 'name' ? 'asc' : 'desc';
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(...self::FILTERS);
        $this->resetPage();
        $this->selected = [];
    }

    // ---- Row & bulk actions -------------------------------------------------

    public function setActive(int $id, bool $active): void
    {
        $user = $this->donorQuery()->findOrFail($id);
        $user->update(['is_active' => $active]);
        SiteCache::forgetDonorCount();
        $this->dispatch('notify', message: $active ? $user->name.' আবার সক্রিয়।' : $user->name.' নিষ্ক্রিয় — তালিকায় দেখাবেন না, লগইনও করতে পারবেন না।');
    }

    public function bulkSetActive(bool $active): void
    {
        $count = $this->donorQuery()->whereIn('id', $this->selected)->update(['is_active' => $active]);
        SiteCache::forgetDonorCount();
        $this->selected = [];
        $this->dispatch('notify', message: \App\Support\Bn::num($count).' জন ডোনার '.($active ? 'সক্রিয়' : 'নিষ্ক্রিয়').' করা হয়েছে।');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $this->donorQuery()->findOrFail($id)->delete();
        SiteCache::forgetDonorCount();
        $this->selected = array_values(array_diff($this->selected, [$id]));
        $this->dispatch('notify', message: 'ডোনার মুছে ফেলা হয়েছে।');
    }

    public function bulkDelete(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $count = $this->donorQuery()->whereIn('id', $this->selected)->get()->each->delete()->count();
        SiteCache::forgetDonorCount();
        $this->selected = [];
        $this->dispatch('notify', message: \App\Support\Bn::num($count).' জন ডোনার মুছে ফেলা হয়েছে।');
    }

    /** CSV of the ticked rows, or of every row matching the filters when nothing is ticked. */
    public function export(): StreamedResponse
    {
        $query = $this->filtered()->with(['district', 'thana']);
        if ($this->selected) {
            $query->whereIn('id', $this->selected);
        }

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Bangla correctly
            fputcsv($out, ['নাম', 'মোবাইল', 'ইমেইল', 'রক্তের গ্রুপ', 'লিঙ্গ', 'পেশা', 'জন্ম তারিখ', 'জেলা', 'থানা', 'ঠিকানা', 'শেষ রক্তদান', 'রক্ত দিতে প্রস্তুত', 'সক্রিয়', 'নিবন্ধন']);
            $query->chunk(500, function ($users) use ($out) {
                foreach ($users as $u) {
                    fputcsv($out, [
                        $u->name, $u->mobile, $u->email, $u->blood_group->value, $u->gender->label(), $u->occupation->label(),
                        $u->date_of_birth?->toDateString(), $u->district?->bn_name, $u->thana?->bn_name ?? $u->thana?->name, $u->address,
                        $u->last_donated_at?->toDateString(), $u->is_available ? 'হ্যাঁ' : 'না', $u->is_active ? 'হ্যাঁ' : 'না', $u->created_at->toDateTimeString(),
                    ]);
                }
            });
            fclose($out);
        }, 'bandhan-donors-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ---- Edit sheet ---------------------------------------------------------

    public function edit(int $id): void
    {
        $u = $this->donorQuery()->findOrFail($id);
        $this->resetValidation();
        $this->fill([
            'editingId' => $u->id,
            'name' => $u->name,
            'mobile' => $u->mobile,
            'email' => $u->email ?? '',
            'gender' => $u->gender->value,
            'blood_group' => $u->blood_group->value,
            'occupation' => $u->occupation->value,
            'date_of_birth' => $u->date_of_birth?->toDateString() ?? '',
            'last_donated_at' => $u->last_donated_at?->toDateString() ?? '',
            'division_id' => $u->division_id,
            'district_id' => $u->district_id,
            'thana_id' => $u->thana_id,
            'address' => $u->address ?? '',
            'is_available' => $u->is_available,
            'is_active' => $u->is_active,
            'showEditor' => true,
        ]);
    }

    public function save(): void
    {
        $user = $this->donorQuery()->findOrFail($this->editingId);
        $this->mobile = Mobile::normalize($this->mobile);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:80'],
            'mobile' => ['required', Mobile::RULE, Rule::unique('users', 'mobile')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:120'],
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
            'is_active' => ['boolean'],
        ], [
            'mobile.unique' => 'এই নম্বরে আরেকজন সদস্য নিবন্ধিত আছেন।',
            'mobile.regex' => '১১ অঙ্কের মোবাইল নম্বর দিন (01XXXXXXXXX)।',
        ]);

        $user->update(array_merge($data, [
            'email' => $data['email'] ?: null,
            'thana_id' => $data['thana_id'] ?: null,
            'address' => $data['address'] ?: null,
            'last_donated_at' => $data['last_donated_at'] ?: null,
        ]));

        SiteCache::forgetDonorCount();
        $this->showEditor = false;
        $this->dispatch('notify', message: $user->name.'-এর তথ্য হালনাগাদ হয়েছে।');
    }

    // ---- Queries ------------------------------------------------------------

    private function donorQuery(): Builder
    {
        return User::where('role', Role::Donor);
    }

    private function filtered(): Builder
    {
        $group = BloodGroup::fromSlug($this->group);
        $term = trim($this->q);

        return $this->donorQuery()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('mobile', 'like', '%'.Mobile::normalize($term).'%')->orWhere('email', 'like', "%{$term}%")))
            ->when($group, fn ($q) => $q->where('blood_group', $group))
            ->when($this->district, fn ($q) => $q->where('district_id', $this->district))
            ->when($this->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($this->status === 'eligible', fn ($q) => $q->where('is_active', true)->eligible())
            ->when($this->status === 'unavailable', fn ($q) => $q->where('is_available', false));
    }

    #[Computed]
    public function donors()
    {
        $sort = in_array($this->sort, self::SORTABLE, true) ? $this->sort : 'created_at';
        $dir = $this->dir === 'asc' ? 'asc' : 'desc';

        return $this->filtered()->with(['district', 'thana'])->orderBy($sort, $dir)->orderByDesc('id')->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.donors', [
            'allDistricts' => SiteCache::districts(),
            'filtering' => $this->q !== '' || $this->group !== '' || $this->district !== '' || $this->status !== '',
        ]);
    }
}
