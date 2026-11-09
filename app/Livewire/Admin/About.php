<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\Reorders;
use App\Models\AboutSection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class About extends Component
{
    use Reorders;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $body = '';

    public function create(): void
    {
        $this->reset('editingId', 'title', 'body');
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $section = AboutSection::findOrFail($id);
        $this->resetValidation();
        $this->fill(['editingId' => $section->id, 'title' => $section->title, 'body' => $section->body, 'showForm' => true]);
    }

    public function save(): void
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        if ($this->editingId) {
            AboutSection::findOrFail($this->editingId)->update($data);
        } else {
            AboutSection::create($data + ['sort' => (int) AboutSection::max('sort') + 1]);
        }

        $this->reset('showForm', 'editingId', 'title', 'body');
        unset($this->sections);
        $this->dispatch('notify', message: 'আমাদের সম্পর্কে পাতা হালনাগাদ হয়েছে।');
    }

    public function reorder(int $id, int $position): void
    {
        $this->moveTo(AboutSection::orderBy('sort')->orderBy('id'), $id, $position);
        unset($this->sections);
    }

    public function delete(int $id): void
    {
        AboutSection::findOrFail($id)->delete();
        unset($this->sections);
        $this->dispatch('notify', message: 'অংশটি মুছে ফেলা হয়েছে।');
    }

    #[Computed]
    public function sections()
    {
        return AboutSection::orderBy('sort')->orderBy('id')->get();
    }

    public function render()
    {
        return view('livewire.admin.about');
    }
}
