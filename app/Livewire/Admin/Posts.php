<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Posts extends Component
{
    use WithPagination;

    /** all | published | scheduled | draft */
    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: '')]
    public string $q = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'q'], true)) {
            $this->resetPage();
        }
    }

    public function togglePublished(int $id): void
    {
        $post = Post::findOrFail($id);
        $publish = ! $post->is_published;

        $post->update([
            'is_published' => $publish,
            'published_at' => $publish ? ($post->published_at ?? now()) : $post->published_at,
        ]);

        $this->dispatch('notify', message: match (true) {
            ! $publish => 'লেখাটি খসড়ায় ফেরানো হয়েছে — সাইটে আর দেখাবে না।',
            $post->isScheduled() => 'লেখাটি নির্ধারিত সময়ে প্রকাশ হবে।',
            default => 'লেখাটি প্রকাশিত হয়েছে।',
        });
    }

    public function delete(int $id): void
    {
        Post::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'লেখাটি মুছে ফেলা হয়েছে।');
    }

    private function filtered(bool $withStatus = true): Builder
    {
        $term = trim($this->q);

        return Post::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%")))
            ->when($withStatus, fn ($q) => match ($this->status) {
                'published' => $q->published(),
                'scheduled' => $q->where('is_published', true)->where('published_at', '>', now()),
                'draft' => $q->where('is_published', false),
                default => $q,
            });
    }

    #[Computed]
    public function posts()
    {
        return $this->filtered()
            ->orderByRaw('is_published = 0 desc')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function render()
    {
        $base = fn () => $this->filtered(withStatus: false);

        return view('livewire.admin.posts', [
            'tabs' => [
                'all' => ['সব', $base()->count()],
                'published' => ['প্রকাশিত', $base()->published()->count()],
                'scheduled' => ['নির্ধারিত', $base()->where('is_published', true)->where('published_at', '>', now())->count()],
                'draft' => ['খসড়া', $base()->where('is_published', false)->count()],
            ],
        ]);
    }
}
