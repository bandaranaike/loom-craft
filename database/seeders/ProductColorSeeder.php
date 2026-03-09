<?php

namespace Database\Seeders;

use App\Models\ProductColor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            'primary' => ['Red', 'Yellow', 'Blue'],
            'secondary' => ['Orange', 'Green', 'Purple'],
            'tertiary' => [
                'Yellow-Orange',
                'Red-Orange',
                'Red-Purple',
                'Blue-Purple',
                'Blue-Green',
                'Yellow-Green',
            ],
            'catalog' => [
                'Black',
                'White',
                'Gray',
                'Beige',
                'Brown',
                'Pink',
                'Teal',
                'Amber',
                'Gold',
            ],
        ];

        $sortOrder = 0;

        foreach ($groups as $colors) {
            foreach ($colors as $name) {
                ProductColor::query()->updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'name' => $name,
                        'is_active' => true,
                        'sort_order' => $sortOrder,
                    ],
                );

                $sortOrder++;
            }
        }
    }
}
