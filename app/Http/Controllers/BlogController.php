<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Bn;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): View
    {
        $posts = Post::latestPublished()->paginate(self::PER_PAGE)->withPath(route('blog.index'));
        $page = $posts->currentPage();

        abort_if($page > 1 && $posts->isEmpty(), 404);

        $suffix = $page > 1 ? ' — পাতা '.Bn::num($page) : '';

        Seo::make()
            ->title('ব্লগ'.$suffix)
            ->appBar('ব্লগ')
            ->description('রক্তদূতের খবর, রক্তদান কর্মসূচির প্রতিবেদন আর রক্তদান নিয়ে সচেতনতামূলক লেখা'.($page > 1 ? ' — পাতা '.Bn::num($page).'।' : '।'))
            ->canonical($page > 1 ? $posts->url($page) : route('blog.index'))
            ->breadcrumbs(['ব্লগ' => route('blog.index')])
            ->schema([
                '@type' => 'Blog',
                '@id' => route('blog.index').'#blog',
                'name' => 'রক্তদূত ব্লগ',
                'url' => route('blog.index'),
                'inLanguage' => 'bn',
                'publisher' => ['@id' => url('/').'#organization'],
            ]);

        return view('pages.blog.index', ['posts' => $posts]);
    }

    public function show(Request $request, string $slug): View
    {
        // Admins can open drafts and scheduled posts to preview them.
        $preview = (bool) $request->user()?->isAdmin();
        $post = Post::when(! $preview, fn ($q) => $q->published())->where('slug', $slug)->firstOrFail();

        $seo = Seo::make()
            ->title($post->title)
            ->appBar('ব্লগ')
            ->description($post->summary(155))
            ->canonical($post->url())
            ->article($post->published_at ?? $post->created_at, $post->updated_at)
            ->breadcrumbs(['ব্লগ' => route('blog.index'), $post->title => $post->url()])
            ->schema(array_filter([
                '@type' => 'BlogPosting',
                '@id' => $post->url().'#article',
                'headline' => $post->title,
                'description' => $post->summary(155),
                'url' => $post->url(),
                'mainEntityOfPage' => ['@id' => $post->url().'#webpage'],
                'image' => $post->coverUrl(),
                'datePublished' => ($post->published_at ?? $post->created_at)->toAtomString(),
                'dateModified' => $post->updated_at->toAtomString(),
                'inLanguage' => 'bn',
                'author' => ['@id' => url('/').'#organization'],
                'publisher' => ['@id' => url('/').'#organization'],
                'isPartOf' => ['@id' => route('blog.index').'#blog'],
            ]));

        if ($post->hasCover()) {
            $seo->image($post->coverUrl(), $post->cover_width, $post->cover_height, $post->coverAlt());
        }

        if (! $post->isLive()) {
            $seo->noindex();
        }

        return view('pages.blog.show', [
            'post' => $post,
            'more' => Post::latestPublished()->whereKeyNot($post->id)->limit(3)->get(),
        ]);
    }
}
