<?php

namespace Database\Factories;

use App\Models\Vendor;
use App\Models\VendorContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VendorContactSubmission>
 */
class VendorContactSubmissionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\VendorContactSubmission>
     */
    protected $model = VendorContactSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => 'pending',
            'handled_by' => null,
            'handled_at' => null,
            'submitted_at' => now(),
        ];
    }
}
