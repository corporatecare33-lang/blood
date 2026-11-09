<?php

namespace App\Livewire\Admin;

use App\Support\Site;
use Livewire\Component;

/** Organisation facts shown across the public site (footer, about, terms, register form, llms.txt). */
class Settings extends Component
{
    public string $email = '';

    public string $sponsor_name = '';

    /** @var list<string> */
    public array $conditions = [];

    public function mount(): void
    {
        $this->fillFromSite();
    }

    public function addCondition(): void
    {
        $this->conditions[] = '';
    }

    public function removeCondition(int $index): void
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    public function moveCondition(int $index, int $position): void
    {
        $item = $this->conditions[$index] ?? null;
        if ($item === null) {
            return;
        }
        unset($this->conditions[$index]);
        $rest = array_values($this->conditions);
        array_splice($rest, max(0, min($position, count($rest))), 0, [$item]);
        $this->conditions = $rest;
    }

    public function restoreConditions(): void
    {
        $this->conditions = config('bandhan.conditions');
    }

    public function save(): void
    {
        $this->conditions = array_values(array_filter(array_map('trim', $this->conditions), fn ($c) => $c !== ''));

        $this->validate([
            'email' => ['required', 'email', 'max:120'],
            'sponsor_name' => ['required', 'string', 'max:60'],
            'conditions' => ['required', 'array', 'min:1', 'max:12'],
            'conditions.*' => ['required', 'string', 'max:200'],
        ], [
            'conditions.min' => 'অন্তত একটি শর্ত রাখুন।',
            'conditions.required' => 'অন্তত একটি শর্ত রাখুন।',
        ]);

        Site::save([
            'email' => $this->email,
            'sponsor_name' => $this->sponsor_name,
            'conditions' => $this->conditions,
        ]);

        $this->fillFromSite();
        $this->dispatch('notify', message: 'সাইট সেটিংস সংরক্ষণ হয়েছে — সাইটে এখনই দেখাচ্ছে।');
    }

    private function fillFromSite(): void
    {
        $this->email = Site::email();
        $this->sponsor_name = Site::sponsor();
        $this->conditions = Site::conditions();
    }

    public function render()
    {
        return view('livewire.admin.settings', [
            'conditionsChanged' => $this->conditions !== config('bandhan.conditions'),
        ]);
    }
}
