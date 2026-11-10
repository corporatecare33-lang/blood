<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * First gallery photos, supplied by the owner (2026-11-10). Files ship in public/images/gallery
 * (1600px + 720px WebP) so they survive redeploys. Titles describe what each photo shows —
 * no places, dates or events are claimed. Runs once: admins may edit, hide or delete them afterwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $sort = (int) DB::table('gallery_items')->max('sort');

        foreach ($this->photos() as $slug => $title) {
            $path = "images/gallery/$slug.webp";
            if (DB::table('gallery_items')->where('image_path', $path)->exists()) {
                continue;
            }

            DB::table('gallery_items')->insert([
                'title' => $title,
                'image_path' => $path,
                'thumb_path' => "images/gallery/$slug-720.webp",
                'width' => 720,
                'height' => 405,
                'sort' => ++$sort,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('gallery_items')
            ->whereIn('image_path', array_map(fn ($slug) => "images/gallery/$slug.webp", array_keys($this->photos())))
            ->delete();
    }

    private function photos(): array
    {
        return [
            'community-blood-donation-camp' => 'রক্তদান কর্মসূচিতে ডোনাররা',
            'student-volunteers-on-campus' => 'ক্যাম্পাসে ডোনারদের স্বাগত জানাচ্ছেন শিক্ষার্থী স্বেচ্ছাসেবকরা',
            'donor-with-nurse' => 'রক্তদানের সময় পাশে স্বাস্থ্যকর্মী',
            'volunteers-welcome-visitors' => 'স্বেচ্ছাসেবকদের বুথে আগ্রহীদের স্বাগত',
            'blood-pressure-check' => 'রক্তদানের আগে রক্তচাপ পরীক্ষা',
            'donor-talks-with-doctor' => 'রক্তদানের আগে চিকিৎসকের সাথে কথা',
            'refreshments-after-donation' => 'রক্তদানের পর ডোনারদের হালকা খাবার',
            'donor-resting-after-donation' => 'রক্তদানের পর বিশ্রাম',
            'volunteers-with-hearts' => 'হাতে হাতে ভালোবাসার বার্তা',
            'blood-bags-in-storage' => 'ব্লাড ব্যাংকে রক্তের ব্যাগ সংরক্ষণ',
        ];
    }
};
