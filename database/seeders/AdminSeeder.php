<?php

namespace Database\Seeders;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Occupation;
use App\Enums\Role;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Creates the first super admin from ADMIN_* values in .env. Nothing is hard-coded. */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = config('bandhan.admin');

        if (blank($admin['mobile']) || blank($admin['password'])) {
            $this->command?->warn('ADMIN_MOBILE / ADMIN_PASSWORD not set — skipping super admin.');

            return;
        }

        $dhaka = District::where('name', 'Dhaka')->firstOrFail();

        User::updateOrCreate(['mobile' => $admin['mobile']], [
            'name' => $admin['name'],
            'password' => $admin['password'],
            'role' => Role::SuperAdmin,
            'gender' => Gender::Others,
            'blood_group' => BloodGroup::OPos,
            'occupation' => Occupation::Others,
            'date_of_birth' => now()->subYears(25)->toDateString(),
            'division_id' => $dhaka->division_id,
            'district_id' => $dhaka->id,
            'is_available' => false,
        ]);
    }
}
