<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductColorRequest;
use App\Http\Requests\Admin\UpdateProductColorRequest;
use App\Models\ProductColor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductColorController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('access', User::class);

        $colors = ProductColor::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ProductColor $color): array => [
                'id' => $color->id,
                'name' => $color->name,
                'slug' => $color->slug,
                'is_active' => $color->is_active,
                'sort_order' => $color->sort_order,
                'products_count' => $color->products_count,
            ])
            ->all();

        return Inertia::render('admin/product-colors/index', [
            'colors' => $colors,
            'status' => session('status'),
        ]);
    }

    public function store(StoreProductColorRequest $request): RedirectResponse
    {
        Gate::authorize('access', User::class);

        $name = $request->string('name')->toString();
        $slugInput = $request->string('slug')->toString();
        $slug = $this->resolveUniqueSlug($slugInput !== '' ? $slugInput : $name);

        ProductColor::query()->create([
            'name' => $name,
            'slug' => $slug,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.product-colors.index')
            ->with('status', 'Color created successfully.');
    }

    public function update(
        UpdateProductColorRequest $request,
        ProductColor $productColor,
    ): RedirectResponse {
        Gate::authorize('access', User::class);

        $name = $request->string('name')->toString();
        $slugInput = $request->string('slug')->toString();
        $baseSlug = $slugInput !== '' ? $slugInput : $name;
        $slug = $this->resolveUniqueSlug($baseSlug, $productColor->id);

        $productColor->forceFill([
            'name' => $name,
            'slug' => $slug,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ])->save();

        return redirect()
            ->route('admin.product-colors.index')
            ->with('status', 'Color updated successfully.');
    }

    public function destroy(ProductColor $productColor): RedirectResponse
    {
        Gate::authorize('access', User::class);

        if ($productColor->products()->exists()) {
            return redirect()
                ->route('admin.product-colors.index')
                ->with('status', 'Color is in use. Archive it by setting inactive instead.');
        }

        $productColor->delete();

        return redirect()
            ->route('admin.product-colors.index')
            ->with('status', 'Color deleted successfully.');
    }

    protected function resolveUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);
        $base = $base !== '' ? $base : 'color';
        $slug = $base;
        $counter = 2;

        while (
            ProductColor::query()
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
