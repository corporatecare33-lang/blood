<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\Post;
use Illuminate\Http\Response;

class SeoFileController extends Controller
{
    /** Public, indexable routes. Keep in sync with routes/web.php. */
    private const PAGES = [
        'home' => ['weekly', '1.0'],
        'requests.index' => ['hourly', '0.9'],
        'donors.search' => ['weekly', '0.8'],
        'register' => ['monthly', '0.8'],
        'requests.create' => ['monthly', '0.7'],
        'about' => ['monthly', '0.6'],
        'tips' => ['monthly', '0.6'],
        'blog.index' => ['weekly', '0.6'],
        'gallery' => ['weekly', '0.5'],
        'login' => ['yearly', '0.3'],
        'terms' => ['yearly', '0.2'],
        'privacy' => ['yearly', '0.2'],
    ];

    public function sitemap(): Response
    {
        $galleryUpdated = GalleryItem::max('updated_at');

        $posts = Post::latestPublished()->get(['slug', 'updated_at']);

        $xml = view('seo.sitemap', [
            'pages' => self::PAGES,
            'galleryUpdated' => $galleryUpdated,
            'posts' => $posts,
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8'])
            ->setPublic()->setMaxAge(3600);
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /account', 'Disallow: /forgot-password', 'Disallow: /livewire', '', 'Sitemap: '.route('sitemap')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function llms(): Response
    {
        return response(view('seo.llms')->render(), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
