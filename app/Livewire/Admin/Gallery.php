<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\Reorders;
use App\Models\GalleryItem;
use App\Services\ImageProcessor;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Gallery extends Component
{
    use Reorders, WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public $photo;

    public string $title = '';

    public string $caption = '';

    public bool $is_published = true;

    public function create(): void
    {
        $this->reset('editingId', 'photo', 'title', 'caption');
        $this->is_published = true;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $item = GalleryItem::findOrFail($id);
        $this->reset('photo');
        $this->resetValidation();
        $this->fill([
            'editingId' => $item->id,
            'title' => $item->title,
            'caption' => $item->caption ?? '',
            'is_published' => $item->is_published,
            'showForm' => true,
        ]);
    }

    public function save(ImageProcessor $images): void
    {
        $this->validate([
            'photo' => [$this->editingId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'title' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:500'],
            'is_published' => ['boolean'],
        ], ['photo.max' => 'ছবির আকার ৮ MB-এর কম হতে হবে।']);

        $data = ['title' => $this->title, 'caption' => $this->caption ?: null, 'is_published' => $this->is_published];

        if ($this->editingId) {
            GalleryItem::findOrFail($this->editingId)->update($data);
            $message = 'ছবির তথ্য হালনাগাদ হয়েছে।';
        } else {
            GalleryItem::create($images->storeGalleryImage($this->photo->getRealPath()) + $data + [
                'sort' => (int) GalleryItem::max('sort') + 1,
            ]);
            $message = 'ছবি গ্যালারিতে যোগ হয়েছে।';
        }

        $this->reset('showForm', 'editingId', 'photo', 'title', 'caption');
        unset($this->items);
        $this->dispatch('notify', message: $message);
    }

    public function togglePublished(int $id): void
    {
        $item = GalleryItem::findOrFail($id);
        $item->update(['is_published' => ! $item->is_published]);
        unset($this->items);
        $this->dispatch('notify', message: $item->is_published ? 'ছবিটি এখন গ্যালারিতে দেখাচ্ছে।' : 'ছবিটি গ্যালারি থেকে লুকানো হয়েছে।');
    }

    public function reorder(int $id, int $position): void
    {
        $this->moveTo(GalleryItem::orderBy('sort')->latest(), $id, $position);
        unset($this->items);
    }

    public function delete(int $id): void
    {
        GalleryItem::findOrFail($id)->delete();
        unset($this->items);
        $this->dispatch('notify', message: 'ছবি মুছে ফেলা হয়েছে।');
    }

    /** Same order as the public gallery. */
    #[Computed]
    public function items()
    {
        return GalleryItem::orderBy('sort')->latest()->get();
    }

    public function render()
    {
        return view('livewire.admin.gallery');
    }
}
