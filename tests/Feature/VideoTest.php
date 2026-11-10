<?php

namespace Tests\Feature;

use App\Livewire\Admin\Videos;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\GeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeoSeeder::class);
    }

    public function test_video_id_is_read_from_every_common_youtube_link(): void
    {
        foreach ([
            'dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com/watch?feature=share&v=dQw4w9WgXcQ&t=42s',
            'https://m.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ?si=abc123',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'https://www.youtube.com/live/dQw4w9WgXcQ?feature=shared',
            '  youtu.be/dQw4w9WgXcQ  ',
        ] as $link) {
            $this->assertSame('dQw4w9WgXcQ', Video::idFrom($link), $link);
        }

        foreach (['', 'https://vimeo.com/123456', 'https://www.youtube.com/@channel', 'https://evil.com/watch?v=dQw4w9WgXcQ', 'not a link'] as $link) {
            $this->assertNull(Video::idFrom($link), $link);
        }
    }

    public function test_home_shows_only_published_videos_with_a_click_to_play_embed(): void
    {
        $this->get('/')->assertOk()->assertDontSee('ভিডিও গ্যালারি');

        Video::create(['title' => 'রক্তদান কর্মসূচির ভিডিও', 'youtube_id' => 'dQw4w9WgXcQ', 'sort' => 1]);
        Video::create(['title' => 'লুকানো ভিডিও', 'youtube_id' => 'aaaaaaaaaaa', 'sort' => 2, 'is_published' => false]);

        $this->get('/')->assertOk()
            ->assertSee('ভিডিও গ্যালারি')
            ->assertSee('রক্তদান কর্মসূচির ভিডিও')
            ->assertSee('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false)
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertDontSee('লুকানো ভিডিও');
    }

    public function test_empty_gallery_is_hidden_from_visitors_but_admins_see_where_it_goes(): void
    {
        $this->get('/')->assertOk()->assertDontSee('ভিডিও গ্যালারি');

        $this->actingAs(User::factory()->admin()->create())
            ->get('/')->assertOk()->assertSee('ভিডিও গ্যালারি')->assertSee(route('admin.videos'));
    }

    public function test_admin_adds_a_video_from_a_link_and_the_title_comes_from_youtube(): void
    {
        Http::fake(['www.youtube.com/oembed*' => Http::response(['title' => 'Blood Donation Awareness'])]);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Videos::class)
            ->call('create')
            ->set('url', 'https://youtu.be/dQw4w9WgXcQ?si=x')
            ->assertSet('title', 'Blood Donation Awareness')
            ->call('save')
            ->assertHasNoErrors();

        $video = Video::sole();
        $this->assertSame('dQw4w9WgXcQ', $video->youtube_id);
        $this->get('/')->assertSee('Blood Donation Awareness');

        // Change the link and hide it.
        Livewire::test(Videos::class)
            ->call('edit', $video->id)
            ->assertSet('url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->set('url', 'https://www.youtube.com/shorts/bbbbbbbbbbb')
            ->call('save')
            ->assertHasNoErrors()
            ->call('togglePublished', $video->id);

        $video->refresh();
        $this->assertSame('bbbbbbbbbbb', $video->youtube_id);
        $this->assertSame('Blood Donation Awareness', $video->title);
        $this->assertFalse($video->is_published);
        // The hidden video is gone; this admin sees the empty-gallery placeholder in its place.
        $this->get('/')->assertDontSee('Blood Donation Awareness')->assertSee(route('admin.videos'));
    }

    public function test_invalid_links_are_rejected_and_videos_can_be_reordered_and_deleted(): void
    {
        Http::fake(['*' => Http::response([], 404)]);
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Videos::class)
            ->set('url', 'https://vimeo.com/123456')
            ->set('title', 'ভুল লিংক')
            ->call('save')
            ->assertHasErrors('url');
        $this->assertSame(0, Video::count());

        $a = Video::create(['title' => 'প্রথম', 'youtube_id' => 'aaaaaaaaaaa', 'sort' => 1]);
        $b = Video::create(['title' => 'দ্বিতীয়', 'youtube_id' => 'bbbbbbbbbbb', 'sort' => 2]);

        Livewire::test(Videos::class)->call('reorder', $b->id, 0)->call('delete', $a->id);

        $this->assertSame(1, $b->fresh()->sort);
        $this->assertNull(Video::find($a->id));
    }
}
