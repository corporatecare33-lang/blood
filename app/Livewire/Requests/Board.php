<?php

namespace App\Livewire\Requests;

use App\Enums\BloodGroup;
use App\Models\BloodRequest;
use App\Support\SiteCache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Board extends Component
{
    use WithPagination;

    #[Url(as: 'group', except: '')]
    public string $group = '';

    #[Url(as: 'district', except: '')]
    public string $district = '';

    public function updated(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function requests()
    {
        $group = BloodGroup::fromSlug($this->group);

        return BloodRequest::active()
            ->with('district')
            ->when($group, fn ($q) => $q->where('blood_group', $group))
            ->when($this->district, fn ($q) => $q->where('district_id', $this->district))
            ->orderBy('needed_at')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.requests.board', ['districts' => SiteCache::districts()]);
    }
}
