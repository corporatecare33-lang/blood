<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use App\Services\ImageProcessor;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostEditor extends Component
{
    use WithFileUploads;

    #[Locked]
    public ?int $postId = null;

    public string $title = '';

    public string $slug = '';

    /** False until the admin edits the slug; until then it follows the title. */
    public bool $slugTouched = false;

    public string $excerpt = '';

    public string $body = '';

    public $cover;

    public bool $removeCover = false;

    public string $cover_alt = '';

    public bool $is_published = true;

    public string $published_at = '';

    public bool $previewing = false;

    public function mount(?int $postId = null): void
    {
        if (! $postId) {
            $this->published_at = now()->format('Y-m-d\TH:i');

            return;
        }

        $post = Post::findOrFail($postId);
        $this->fill([
            'postId' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            // Never change a saved post's URL behind the admin's back.
            'slugTouched' => true,
            'excerpt' => $post->excerpt ?? '',
            'body' => $post->body,
            'cover_alt' => $post->cover_alt ?? '',
            'is_published' => $post->is_published,
            'published_at' => ($post->published_at ?? now())->format('Y-m-d\TH:i'),
        ]);
    }

    public function updatedTitle(): void
    {
        if (! $this->slugTouched) {
            $this->slug = Post::slugFrom($this->title);
        }
    }

    public function updatedSlug(): void
    {
        $this->slug = Post::slugFrom($this->slug);
        $this->slugTouched = $this->slug !== '';

        if (! $this->slugTouched) {
            $this->slug = Post::slugFrom($this->title);
        }
    }

    public function updatedCover(): void
    {
        $this->removeCover = false;
        $this->validateOnly('cover', $this->rules(), $this->messages());
    }

    public function clearCover(): void
    {
        $this->reset('cover');
        $this->removeCover = true;
    }

    public function save(ImageProcessor $images)
    {
        $this->slug = Post::slugFrom($this->slug !== '' ? $this->slug : $this->title);
        if (! $this->slugTouched) {
            $this->slug = Post::uniqueSlug($this->slug, $this->postId);
        }

        $this->validate($this->rules(), $this->messages());

        $post = $this->postId ? Post::findOrFail($this->postId) : new Post(['user_id' => auth()->id()]);

        $post->fill([
            'title' => trim($this->title),
            'slug' => $this->slug,
            'excerpt' => trim($this->excerpt) ?: null,
            'body' => $this->body,
            'cover_alt' => trim($this->cover_alt) ?: null,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at !== '' ? Carbon::parse($this->published_at) : ($this->is_published ? now() : null),
        ]);

        if ($this->cover) {
            $post->deleteCover();
            $stored = $images->storeGalleryImage($this->cover->getRealPath(), 'blog');
            $post->fill([
                'cover_path' => $stored['image_path'],
                'cover_thumb_path' => $stored['thumb_path'],
                'cover_width' => $stored['large_width'],
                'cover_height' => $stored['large_height'],
            ]);
        } elseif ($this->removeCover) {
            $post->deleteCover();
            $post->fill(['cover_path' => null, 'cover_thumb_path' => null, 'cover_width' => null, 'cover_height' => null]);
        }

        $creating = ! $post->exists;
        $post->save();

        $message = match (true) {
            ! $post->is_published => 'খসড়া সংরক্ষণ হয়েছে।',
            $post->isScheduled() => 'সংরক্ষণ হয়েছে — লেখাটি নির্ধারিত সময়ে প্রকাশ হবে।',
            default => 'সংরক্ষণ হয়েছে — লেখাটি সাইটে প্রকাশিত।',
        };

        if ($creating) {
            session()->flash('status', $message);

            return $this->redirectRoute('admin.posts.edit', ['post' => $post->id], navigate: true);
        }

        $this->reset('cover', 'removeCover');
        $this->slugTouched = true;
        $this->dispatch('notify', message: $message);
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('posts', 'slug')->ignore($this->postId)],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'max:60000'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'cover_alt' => ['nullable', 'string', 'max:160'],
            'is_published' => ['boolean'],
            'published_at' => [$this->is_published ? 'required' : 'nullable', 'date'],
        ];
    }

    protected function messages(): array
    {
        return [
            'slug.unique' => 'এই লিংক আগেই অন্য একটি লেখায় ব্যবহার হয়েছে — অন্য কিছু লিখুন।',
            'cover.max' => 'ছবির আকার ৮ MB-এর কম হতে হবে।',
        ];
    }

    public function currentPost(): ?Post
    {
        return $this->postId ? Post::find($this->postId) : null;
    }

    public function render()
    {
        return view('livewire.admin.post-editor', [
            'post' => $this->currentPost(),
            'previewHtml' => $this->previewing ? (new Post(['body' => $this->body]))->bodyHtml() : null,
        ]);
    }
}
