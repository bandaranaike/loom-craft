<?php

namespace Database\Seeders;

use App\Models\ProductColor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->productColors() as $sortOrder => $color) {
            ProductColor::query()->updateOrCreate(
                ['slug' => $color['slug']],
                [
                    'name' => $color['name'],
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ],
            );
        }
    }

    /**
     * @return list<array{name: string, slug: string, hex: string}>
     */
    private function productColors(): array
    {
        $path = resource_path('data/product-colors.json');
        $colors = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($colors)) {
            throw new \RuntimeException('The product color registry must decode to an array.');
        }

        foreach ($colors as $index => $color) {
            if (
                ! is_array($color)
                || ! is_string($color['name'] ?? null)
                || ! is_string($color['slug'] ?? null)
                || ! is_string($color['hex'] ?? null)
            ) {
                throw new \RuntimeException("Invalid product color definition at index {$index}.");
            }
        }

        /** @var list<array{name: string, slug: string, hex: string}> $colors */
        return array_values($colors);
    }
}
