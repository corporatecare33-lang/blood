<?php

namespace Tests\Feature;

use App\Livewire\Admin\PostEditor;
use App\Livewire\Admin\Posts;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\GeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeoSeeder::class);
        // The bundled articles (migration 2026_11_10_000010) are tested on their own below.
        Post::query()->delete();
    }

    private function makePost(array $overrides = []): Post
    {
        static $n = 0;
        $n++;

        return Post::create($overrides + [
            'title' => "রক্তদান কর্মসূচি $n",
            'slug' => "post-$n",
            'body' => "প্রথম অনুচ্ছেদ।\n\n## কর্মসূচির দিন\n\n- স্বেচ্ছাসেবী\n- রক্তদাতা",
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_empty_blog_shows_an_empty_state_and_home_hides_the_section(): void
    {
        $this->get('/blog')->assertOk()->assertSee('এখনো কোনো লেখা প্রকাশ হয়নি');
        $this->get('/')->assertOk()->assertDontSee('সর্বশেষ খবর');

        // Admins get a hint in its place, linking to the editor.
        $this->actingAs(User::factory()->admin()->create())
            ->get('/')->assertOk()->assertSee('সর্বশেষ খবর')->assertSee(route('admin.posts.create'));
    }

    public function test_published_posts_are_listed_and_readable(): void
    {
        $post = $this->makePost(['title' => 'সিলেটে রক্তদান কর্মসূচি', 'excerpt' => 'কর্মসূচির সংক্ষিপ্ত বিবরণ']);

        $this->get('/blog')->assertOk()->assertSee('সিলেটে রক্তদান কর্মসূচি')->assertSee('কর্মসূচির সংক্ষিপ্ত বিবরণ');
        $this->get('/')->assertOk()->assertSee('সর্বশেষ খবর')->assertSee('সিলেটে রক্তদান কর্মসূচি');

        $this->get('/blog/'.$post->slug)->assertOk()
            ->assertSee('<h1 id="post-title"', false)
            ->assertSee('<h2>কর্মসূচির দিন</h2>', false)
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<link rel="canonical" href="'.$post->url().'">', false);
    }

    public function test_drafts_and_scheduled_posts_are_hidden_from_visitors_but_previewable_by_admins(): void
    {
        $draft = $this->makePost(['title' => 'খসড়া লেখা', 'is_published' => false]);
        $scheduled = $this->makePost(['title' => 'আগামীর লেখা', 'published_at' => now()->addDay()]);

        $this->get('/blog')->assertOk()->assertDontSee('খসড়া লেখা')->assertDontSee('আগামীর লেখা');
        $this->get('/blog/'.$draft->slug)->assertNotFound();
        $this->get('/blog/'.$scheduled->slug)->assertNotFound();
        $this->get('/sitemap.xml')->assertDontSee($draft->url());

        $this->actingAs(User::factory()->admin()->create())
            ->get('/blog/'.$draft->slug)->assertOk()->assertSee('প্রিভিউ')->assertSee('noindex, nofollow', false);
    }

    public function test_bangla_slugs_route_and_appear_in_the_sitemap(): void
    {
        $this->assertSame('সিলেটে-রক্তদান-কর্মসূচি', Post::slugFrom('সিলেটে রক্তদান কর্মসূচি!'));
        $this->assertSame('blood-camp-2026', Post::slugFrom('Blood Camp, 2026'));

        $post = $this->makePost(['slug' => Post::slugFrom('সিলেটে রক্তদান কর্মসূচি')]);

        $this->get($post->url())->assertOk();
        $this->get('/sitemap.xml')->assertOk()->assertSee($post->url(), false);
        $this->get('/llms.txt')->assertOk()->assertSee($post->title);
    }

    public function test_markdown_body_escapes_raw_html_and_marks_external_links(): void
    {
        $post = $this->makePost(['body' => "<script>alert(1)</script>\n\n[WHO](https://www.who.int) and [tips](/tips)\n\n# Heading"]);
        $html = $post->bodyHtml();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('rel="nofollow noopener noreferrer"', $html);
        $this->assertStringContainsString('<a href="/tips">', $html);
        $this->assertStringNotContainsString('<h1', $html);
    }

    public function test_pagination_has_unique_canonicals_and_404s_past_the_end(): void
    {
        foreach (range(1, 13) as $i) {
            $this->makePost();
        }

        $this->get('/blog?page=2')->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('blog.index').'?page=2">', false)
            ->assertSee('পাতা ২');
        $this->get('/blog?page=3')->assertNotFound();
    }

    public function test_admin_can_write_publish_and_edit_a_post_with_a_cover(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/admin/blog')->assertOk();
        $this->get('/admin/blog/new')->assertOk();

        Livewire::test(PostEditor::class)
            ->set('title', 'ঢাকায় রক্তদান কর্মসূচি')
            ->assertSet('slug', 'ঢাকায়-রক্তদান-কর্মসূচি')
            ->set('body', 'কর্মসূচিতে স্বেচ্ছাসেবীরা রক্ত দিয়েছেন।')
            ->set('cover', UploadedFile::fake()->image('camp.jpg', 1800, 1000))
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $post = Post::sole();
        $this->assertTrue($post->isLive());
        $this->assertSame($admin->id, $post->user_id);
        $this->assertSame([1600, 889], [$post->cover_width, $post->cover_height]);
        Storage::disk('public')->assertExists([$post->cover_path, $post->cover_thumb_path]);

        $this->get('/admin/blog/'.$post->id.'/edit')->assertOk()->assertSee('লেখা সম্পাদনা');
        $this->get($post->url())->assertOk()->assertSee($post->coverUrl(), false);

        // Editing the title keeps the published URL.
        Livewire::test(PostEditor::class, ['postId' => $post->id])
            ->set('title', 'নতুন শিরোনাম')
            ->assertSet('slug', 'ঢাকায়-রক্তদান-কর্মসূচি')
            ->call('clearCover')
            ->call('save')
            ->assertHasNoErrors();

        $post->refresh();
        $this->assertSame('নতুন শিরোনাম', $post->title);
        $this->assertNull($post->cover_path);
        Storage::disk('public')->assertDirectoryEmpty('blog');
    }

    public function test_editor_validates_and_deduplicates_slugs(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $existing = $this->makePost(['slug' => 'blood-camp']);

        Livewire::test(PostEditor::class)->call('save')->assertHasErrors(['title', 'body']);

        // An automatic slug gets a suffix…
        Livewire::test(PostEditor::class)
            ->set('title', 'Blood Camp')->set('body', 'লেখা')->call('save')->assertHasNoErrors();
        $this->assertTrue(Post::where('slug', 'blood-camp-2')->exists());

        // …a hand-typed duplicate is an error.
        Livewire::test(PostEditor::class)
            ->set('title', 'অন্য লেখা')->set('slug', $existing->slug)->set('body', 'লেখা')->call('save')
            ->assertHasErrors('slug');
    }

    public function test_admin_list_filters_toggles_and_deletes(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $live = $this->makePost(['title' => 'প্রকাশিত লেখা']);
        $this->makePost(['title' => 'খসড়া লেখা', 'is_published' => false]);

        Livewire::test(Posts::class)
            ->assertSee('প্রকাশিত লেখা')->assertSee('খসড়া লেখা')
            ->set('status', 'draft')->assertDontSee('প্রকাশিত লেখা')->assertSee('খসড়া লেখা')
            ->call('togglePublished', $live->id)
            ->call('delete', $live->id);

        $this->assertModelMissing($live);
    }

    public function test_bundled_articles_use_repo_covers_that_deletion_never_removes(): void
    {
        (require database_path('migrations/2026_11_10_000010_add_first_blog_posts.php'))->up();
        $this->assertSame(3, Post::published()->count());
        $post = Post::where('slug', 'how-roktodoot-works')->sole();

        $this->assertTrue($post->isLive());
        $this->assertSame(url('images/blog/how-roktodoot-works.webp'), $post->coverUrl());
        $this->assertFileExists(public_path($post->cover_path));
        $this->assertFileExists(public_path($post->cover_thumb_path));
        $this->get($post->url())->assertOk()->assertSee('<h1 id="post-title"', false);

        $post->delete();
        $this->assertFileExists(public_path('images/blog/how-roktodoot-works.webp'));
    }

    public function test_donors_cannot_reach_the_blog_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/blog')->assertForbidden();
        $this->get('/admin/blog/new')->assertForbidden();
    }
}
