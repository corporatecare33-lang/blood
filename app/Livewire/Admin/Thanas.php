<?php

namespace App\Livewire\Admin;

use App\Models\Thana;
use App\Models\User;
use App\Support\SiteCache;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Thanas extends Component
{
    #[Url(except: '')]
    public string $district = '';

    #[Url(except: '')]
    public string $q = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $bn_name = '';

    public function updatedDistrict(): void
    {
        $this->q = '';
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'bn_name');
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $thana = Thana::findOrFail($id);
        $this->resetValidation();
        $this->fill(['editingId' => $thana->id, 'name' => $thana->name, 'bn_name' => $thana->bn_name ?? '', 'showForm' => true]);
    }

    public function save(): void
    {
        $this->validate([
            'district' => ['required', 'exists:districts,id'],
            'name' => ['required', 'string', 'max:80', Rule::unique('thanas', 'name')->where('district_id', $this->district)->ignore($this->editingId)],
            'bn_name' => ['nullable', 'string', 'max:80'],
        ], ['name.unique' => 'এই জেলায় এই নামে আগেই একটি থানা আছে।']);

        Thana::updateOrCreate(['id' => $this->editingId], [
            'district_id' => $this->district,
            'name' => $this->name,
            'bn_name' => $this->bn_name ?: null,
        ]);

        $this->dispatch('notify', message: $this->editingId ? 'থানা হালনাগাদ হয়েছে।' : 'থানা যোগ হয়েছে।');
        $this->reset('showForm', 'editingId', 'name', 'bn_name');
        unset($this->thanas);
    }

    public function delete(int $id): void
    {
        if (User::where('thana_id', $id)->exists()) {
            $this->dispatch('notify', message: 'এই থানায় ডোনার নিবন্ধিত আছেন, তাই মোছা যাবে না। নাম পরিবর্তন করুন।');

            return;
        }

        Thana::findOrFail($id)->delete();
        unset($this->thanas);
        $this->dispatch('notify', message: 'থানা মুছে ফেলা হয়েছে।');
    }

    #[Computed]
    public function thanas()
    {
        if (! $this->district) {
            return collect();
        }

        $term = trim($this->q);

        return Thana::where('district_id', $this->district)
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('bn_name', 'like', "%{$term}%")))
            ->addSelect(['donors' => User::selectRaw('count(*)')->whereColumn('users.thana_id', 'thanas.id')])
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.thanas', ['districts' => SiteCache::districts()]);
    }
}
