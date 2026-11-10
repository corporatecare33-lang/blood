<?php

namespace Tests\Feature;

use Database\Seeders\AboutSeeder;
use Database\Seeders\GeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([GeoSeeder::class, AboutSeeder::class]);
    }

    public static function pages(): array
    {
        return [
            ['/'], ['/about'], ['/blog'], ['/gallery'], ['/tips'], ['/terms'], ['/privacy'],
            ['/blood-requests'], ['/blood-requests/new'], ['/search-donors'],
            ['/register'], ['/login'], ['/forgot-password'],
        ];
    }

    #[DataProvider('pages')]
    public function test_page_renders_with_one_h1_and_seo_tags(string $path): void
    {
        $html = $this->get($path)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), "Expected exactly one <h1> on $path");
        $this->assertMatchesRegularExpression('/<title>[^<]+<\/title>/', $html);
        $this->assertMatchesRegularExpression('/<meta name="description" content="[^"]{50,}">/', $html);
        $this->assertStringContainsString('<link rel="canonical"', $html);
        $this->assertStringContainsString('property="og:image"', $html);
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringNotContainsStringIgnoringCase('lorem ipsum', $html);
    }

    public function test_page_titles_are_unique(): void
    {
        $titles = collect(self::pages())->map(function ($p) {
            preg_match('/<title>([^<]+)<\/title>/', $this->get($p[0])->getContent(), $m);

            return $m[1];
        });

        $this->assertSame($titles->count(), $titles->unique()->count());
    }

    public function test_custom_404(): void
    {
        $this->get('/does-not-exist')->assertNotFound()->assertSee('এই পাতাটি খুঁজে পাওয়া যায়নি')->assertSee('হোমে ফিরে যান');
    }

    public function test_seo_files(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->assertSee(route('about'));
        $this->get('/robots.txt')->assertOk()->assertSee('User-agent: *');
        $this->get('/llms.txt')->assertOk()->assertSee('# RoktoDoot');
    }

    public function test_guests_cannot_see_donor_numbers(): void
    {
        $this->get('/search-donors')->assertOk()->assertSee('ডোনার দেখতে লগইন করুন');
    }

    public function test_admin_requires_admin_role(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/donors')->assertRedirect('/admin/login');
    }
}
