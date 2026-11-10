<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\Reorders;
use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

class Videos extends Component
{
    use Reorders;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $url = '';

    public string $title = '';

    public bool $is_published = true;

    public function create(): void
    {
        $this->reset('editingId', 'url', 'title');
        $this->is_published = true;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $video = Video::findOrFail($id);
        $this->resetValidation();
        $this->fill([
            'editingId' => $video->id,
            'url' => $video->watchUrl(),
            'title' => $video->title,
            'is_published' => $video->is_published,
            'showForm' => true,
        ]);
    }

    /** Fill an empty title from YouTube's oEmbed once a valid link is pasted. Silent on failure. */
    public function updatedUrl(): void
    {
        $this->resetValidation('url');
        $id = Video::idFrom($this->url);

        if (! $id || trim($this->title) !== '') {
            return;
        }

        try {
            $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                'url' => 'https://www.youtube.com/watch?v='.$id,
                'format' => 'json',
            ]);
            if ($response->ok() && is_string($title = $response->json('title'))) {
                $this->title = mb_substr(trim($title), 0, 150);
            }
        } catch (Throwable) {
            // Offline or blocked — the admin types the title instead.
        }
    }

    public function save(): void
    {
        $this->validate([
            'url' => ['required', 'string', 'max:300', function ($attribute, $value, $fail) {
                if (! Video::idFrom($value)) {
                    $fail('সঠিক YouTube ভিডিও লিংক দিন, যেমন https://www.youtube.com/watch?v=…');
                }
            }],
            'title' => ['required', 'string', 'max:150'],
            'is_published' => ['boolean'],
        ]);

        $data = [
            'youtube_id' => Video::idFrom($this->url),
            'title' => trim($this->title),
            'is_published' => $this->is_published,
        ];

        if ($this->editingId) {
            Video::findOrFail($this->editingId)->update($data);
            $message = 'ভিডিওর তথ্য হালনাগাদ হয়েছে।';
        } else {
            Video::create($data + ['sort' => (int) Video::max('sort') + 1]);
            $message = 'ভিডিও যোগ হয়েছে।';
        }

        $this->reset('showForm', 'editingId', 'url', 'title');
        unset($this->videos);
        $this->dispatch('notify', message: $message);
    }

    public function togglePublished(int $id): void
    {
        $video = Video::findOrFail($id);
        $video->update(['is_published' => ! $video->is_published]);
        unset($this->videos);
        $this->dispatch('notify', message: $video->is_published ? 'ভিডিওটি এখন হোম পেজে দেখাচ্ছে।' : 'ভিডিওটি হোম পেজ থেকে লুকানো হয়েছে।');
    }

    public function reorder(int $id, int $position): void
    {
        $this->moveTo(Video::orderBy('sort')->latest(), $id, $position);
        unset($this->videos);
    }

    public function delete(int $id): void
    {
        Video::findOrFail($id)->delete();
        unset($this->videos);
        $this->dispatch('notify', message: 'ভিডিও মুছে ফেলা হয়েছে।');
    }

    /** Same order as the home page. */
    #[Computed]
    public function videos()
    {
        return Video::orderBy('sort')->latest()->get();
    }

    public function render()
    {
        return view('livewire.admin.videos');
    }
}
