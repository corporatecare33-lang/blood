<?php

namespace Database\Factories;

use App\Enums\BloodGroup;
use App\Enums\Gender;
use App\Enums\Occupation;
use App\Enums\Role;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        $district = District::inRandomOrder()->first();

        return [
            'name' => fake()->name(),
            'mobile' => '01'.fake()->randomElement(['3', '5', '6', '7', '8', '9']).fake()->unique()->numerify('########'),
            'gender' => fake()->randomElement(Gender::cases()),
            'blood_group' => fake()->randomElement(BloodGroup::cases()),
            'occupation' => fake()->randomElement(Occupation::cases()),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'last_donated_at' => null,
            'division_id' => $district->division_id,
            'district_id' => $district->id,
            'password' => 'password',
            'role' => Role::Donor,
            'is_available' => true,
            'is_active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::Admin]);
    }
}
