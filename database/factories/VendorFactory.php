<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Vendor>
     */
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'display_name' => fake()->company(),
            'slug' => Str::slug(fake()->unique()->company()),
            'bio' => fake()->paragraph(),
            'tagline' => fake()->sentence(5),
            'website_url' => fake()->optional()->url(),
            'contact_email' => fake()->optional()->safeEmail(),
            'contact_phone' => fake()->optional()->phoneNumber(),
            'whatsapp_number' => fake()->optional()->phoneNumber(),
            'logo_path' => null,
            'cover_image_path' => null,
            'about_title' => fake()->optional()->sentence(3),
            'craft_specialties' => fake()->optional()->randomElements(
                ['Cotton Weaving', 'Linen', 'Handloom', 'Natural Dyes'],
                2
            ),
            'years_active' => fake()->optional()->numberBetween(1, 40),
            'is_contact_public' => true,
            'is_website_public' => true,
            'location' => fake()->city(),
            'status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
        ];
    }
}
