<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use App\Models\Thana;
use App\Support\SiteCache;
use Illuminate\Database\Seeder;

/** Seeds Bangladesh divisions, districts and thanas from database/data/bd-geo.json. */
class GeoSeeder extends Seeder
{
    public function run(): void
    {
        $geo = json_decode(file_get_contents(database_path('data/bd-geo.json')), true, flags: JSON_THROW_ON_ERROR);

        foreach ($geo['divisions'] as $row) {
            Division::updateOrCreate(['id' => $row['id']], $row);
        }

        foreach ($geo['districts'] as $row) {
            District::updateOrCreate(['id' => $row['id']], $row);
        }

        $now = now();
        foreach (array_chunk($geo['thanas'], 200) as $chunk) {
            Thana::upsert(
                array_map(fn ($t) => $t + ['created_at' => $now, 'updated_at' => $now], $chunk),
                ['id'],
                ['district_id', 'name', 'bn_name', 'updated_at'],
            );
        }

        SiteCache::forgetLocations();
    }
}
