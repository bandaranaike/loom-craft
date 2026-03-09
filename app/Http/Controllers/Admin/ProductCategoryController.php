<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductCategoryRequest;
use App\Http\Requests\Admin\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductCategoryController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('access', User::class);

        $categories = ProductCategory::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ProductCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'discount_percentage' => $category->discount_percentage !== null
                    ? number_format((float) $category->discount_percentage, 2, '.', '')
                    : null,
                'is_active' => $category->is_active,
                'sort_order' => $category->sort_order,
                'products_count' => $category->products_count,
            ])
            ->all();

        return Inertia::render('admin/product-categories/index', [
            'categories' => $categories,
            'status' => session('status'),
        ]);
    }

    public function store(StoreProductCategoryRequest $request): RedirectResponse
    {
        Gate::authorize('access', User::class);

        $name = $request->string('name')->toString();
        $slugInput = $request->string('slug')->toString();
        $slug = $this->resolveUniqueSlug($slugInput !== '' ? $slugInput : $name);

        ProductCategory::query()->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $request->string('description')->toString() ?: null,
            'discount_percentage' => $request->filled('discount_percentage')
                ? number_format((float) $request->input('discount_percentage'), 2, '.', '')
                : null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.product-categories.index')
            ->with('status', 'Category created successfully.');
    }

    public function update(
        UpdateProductCategoryRequest $request,
        ProductCategory $productCategory,
    ): RedirectResponse {
        Gate::authorize('access', User::class);

        $name = $request->string('name')->toString();
        $slugInput = $request->string('slug')->toString();
        $baseSlug = $slugInput !== '' ? $slugInput : $name;
        $slug = $this->resolveUniqueSlug($baseSlug, $productCategory->id);

        $productCategory->forceFill([
            'name' => $name,
            'slug' => $slug,
            'description' => $request->string('description')->toString() ?: null,
            'discount_percentage' => $request->filled('discount_percentage')
                ? number_format((float) $request->input('discount_percentage'), 2, '.', '')
                : null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ])->save();

        return redirect()
            ->route('admin.product-categories.index')
            ->with('status', 'Category updated successfully.');
    }

    protected function resolveUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'category';
        $slug = $base;
        $counter = 2;

        while (
            ProductCategory::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query, int $id) => $query->where('id', '!=', $id))
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
