<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'guest_email' => fake()->safeEmail(),
            'category' => 'damaged_item',
            'severity' => 'normal',
            'subject' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'status' => 'open',
            'opened_at' => now(),
            'first_response_due_at' => now()->addDay(),
            'sla_due_at' => now()->addDays(3),
        ];
    }
}
