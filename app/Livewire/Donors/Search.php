<?php

namespace App\Livewire\Donors;

use App\Enums\BloodGroup;
use App\Livewire\Concerns\WithLocationFields;
use App\Models\District;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Search extends Component
{
    use WithLocationFields, WithPagination;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'group', except: '')]
    public string $group = '';

    #[Url(as: 'division', except: null)]
    public $division_id = null;

    #[Url(as: 'district', except: null)]
    public $district_id = null;

    #[Url(as: 'thana', except: null)]
    public $thana_id = null;

    #[Url(as: 'compatible', except: false)]
    public bool $compatible = false;

    #[Url(as: 'all', except: false)]
    public bool $includeUnavailable = false;

    public function mount(): void
    {
        // The home-page finder sends ?district= only; fill in its division so the selects line up.
        if ($this->district_id && ! $this->division_id) {
            $this->division_id = District::find($this->district_id)?->division_id;
        }
    }

    public function updated(): void
    {
        $this->resetPage();
    }

    public function updatedGroup(): void
    {
        if ($this->group === '') {
            $this->compatible = false;
        }
    }

    public function clearFilters(): void
    {
        $this->reset('q', 'group', 'division_id', 'district_id', 'thana_id', 'compatible', 'includeUnavailable');
        $this->resetPage();
    }

    /** Removes one of the pills listed by activeFilters(). */
    public function removeFilter(string $filter): void
    {
        match ($filter) {
            'q' => $this->reset('q'),
            'group' => $this->reset('group', 'compatible'),
            'compatible' => $this->reset('compatible'),
            'area' => $this->reset('division_id', 'district_id', 'thana_id'),
            'unavailable' => $this->reset('includeUnavailable'),
            default => null,
        };
        $this->resetPage();
    }

    #[Computed]
    public function activeFilterCount(): int
    {
        return collect([$this->group, $this->division_id, $this->district_id, $this->thana_id, $this->compatible, $this->includeUnavailable])->filter()->count();
    }

    /** Removable pills above the results: key => label. */
    #[Computed]
    public function activeFilters(): array
    {
        $area = $this->thanas->firstWhere('id', $this->thana_id)
            ?? $this->districts->firstWhere('id', $this->district_id)
            ?? $this->divisions->firstWhere('id', $this->division_id);

        return array_filter([
            'q' => trim($this->q) !== '' ? '“'.trim($this->q).'”' : null,
            'group' => $this->selectedGroup ? $this->selectedGroup->value.' গ্রুপ' : null,
            'compatible' => $this->selectedGroup && $this->compatible ? 'মানানসই গ্রুপসহ' : null,
            'area' => $area ? ($area->bn_name ?? $area->name) : null,
            'unavailable' => $this->includeUnavailable ? 'বিরতিতে থাকা ডোনারসহ' : null,
        ]);
    }

    #[Computed]
    public function selectedGroup(): ?BloodGroup
    {
        return BloodGroup::fromSlug($this->group);
    }

    #[Computed]
    public function donors()
    {
        $group = $this->selectedGroup;

        return User::donors()
            ->with(['district', 'thana'])
            ->when($group, function ($q) use ($group) {
                $groups = $this->compatible ? $group->canReceiveFrom() : [$group];
                $q->whereIn('blood_group', array_map(fn ($g) => $g->value, $groups));
            })
            ->when(trim($this->q) !== '', function ($query) {
                $term = '%'.trim($this->q).'%';
                $query->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhereHas('thana', fn ($t) => $t->where('name', 'like', $term)->orWhere('bn_name', 'like', $term))
                    ->orWhereHas('district', fn ($d) => $d->where('name', 'like', $term)->orWhere('bn_name', 'like', $term)));
            })
            ->when($this->division_id, fn ($q) => $q->where('division_id', $this->division_id))
            ->when($this->district_id, fn ($q) => $q->where('district_id', $this->district_id))
            ->when($this->thana_id, fn ($q) => $q->where('thana_id', $this->thana_id))
            ->when(! $this->includeUnavailable, fn ($q) => $q->eligible())
            ->orderByRaw('last_donated_at IS NOT NULL, last_donated_at ASC')
            ->orderBy('name')
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.donors.search');
    }
}
