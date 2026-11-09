<?php

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;

/** Drag-to-reorder for wire:sort lists: moves one row and renumbers `sort` as 1…n. */
trait Reorders
{
    protected function moveTo(Builder $ordered, int $id, int $position): void
    {
        $ids = $ordered->pluck('id')->reject(fn ($rowId) => $rowId === $id)->values();
        $ids->splice(max(0, min($position, $ids->count())), 0, [$id]);

        $model = $ordered->getModel();
        foreach ($ids as $i => $rowId) {
            // Saved one by one so each model's saved() hook clears its cache.
            $row = $model->newQuery()->find($rowId);
            if ($row && $row->sort !== $i + 1) {
                $row->update(['sort' => $i + 1]);
            }
        }
    }
}
