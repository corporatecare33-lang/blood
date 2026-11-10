<?php

namespace Tests\Feature;

use App\Livewire\Admin\Gallery;
use App\Models\GalleryItem;
use App\Models\User;
use App\Support\SiteCache;
use Database\Seeders\GeoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeoSeeder::class);
    }

    public function test_shipped_photos_exist_and_are_served_from_public_images(): void
    {
        $items = GalleryItem::orderBy('sort')->get();
        $this->assertCount(10, $items);

        foreach ($items as $item) {
            $this->assertFileExists(public_path($item->image_path));
            $this->assertFileExists(public_path($item->thumb_path));
            $this->assertSame(url($item->thumb_path), $item->thumbUrl());
        }

        $this->get('/gallery')->assertOk()->assertSee($items->first()->thumbUrl(), false);
    }

    public function test_home_shows_the_first_six_published_photos_linking_to_the_gallery(): void
    {
        $items = GalleryItem::orderBy('sort')->get();

        $this->get('/')->assertOk()
            ->assertSee('ছবির গ্যালারি')
            ->assertSee($items[0]->thumbUrl(), false)
            ->assertSee($items[5]->thumbUrl(), false)
            ->assertDontSee($items[6]->thumbUrl(), false)
            ->assertSee(route('gallery'), false);

        $items[0]->update(['is_published' => false]);

        $this->get('/')->assertDontSee($items[0]->thumbUrl(), false)->assertSee($items[6]->thumbUrl(), false);
    }

    public function test_empty_photo_gallery_is_hidden_from_visitors_but_admins_see_where_it_goes(): void
    {
        GalleryItem::query()->update(['is_published' => false]);
        SiteCache::forgetGallery();

        $this->get('/')->assertOk()->assertDontSee('ছবির গ্যালারি');

        $this->actingAs(User::factory()->admin()->create())
            ->get('/')->assertOk()->assertSee('ছবির গ্যালারি')->assertSee(route('admin.gallery'));
    }

    public function test_deleting_a_shipped_photo_keeps_its_files(): void
    {
        $item = GalleryItem::orderBy('sort')->first();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Gallery::class)->call('delete', $item->id);

        $this->assertNull(GalleryItem::find($item->id));
        $this->assertFileExists(public_path($item->image_path));
        $this->assertFileExists(public_path($item->thumb_path));
    }
}
