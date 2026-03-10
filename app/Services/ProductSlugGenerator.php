<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductSlugGenerator
{
    public function generate(string $name, ?int $ignoreProductId = null): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'product';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($slug, $ignoreProductId)) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreProductId = null): bool
    {
        return Product::query()
            ->when($ignoreProductId !== null, fn ($query) => $query->whereKeyNot($ignoreProductId))
            ->where('slug', $slug)
            ->exists();
    }
}
