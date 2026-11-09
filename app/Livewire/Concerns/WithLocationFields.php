<?php

namespace App\Livewire\Concerns;

use App\Support\SiteCache;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Cascading Division → District → Thana selects.
 * Components using this declare public $division_id, $district_id, $thana_id.
 */
trait WithLocationFields
{
    public function updatedDivisionId(): void
    {
        $this->district_id = null;
        $this->thana_id = null;
    }

    public function updatedDistrictId(): void
    {
        $this->thana_id = null;
    }

    #[Computed]
    public function divisions(): Collection
    {
        return SiteCache::divisions();
    }

    #[Computed]
    public function districts(): Collection
    {
        return SiteCache::districtsFor($this->division_id ? (int) $this->division_id : null);
    }

    #[Computed]
    public function thanas(): Collection
    {
        return SiteCache::thanasFor($this->district_id ? (int) $this->district_id : null);
    }
}
