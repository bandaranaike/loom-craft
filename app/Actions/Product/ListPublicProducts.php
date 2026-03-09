<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductPublicIndexData;
use App\DTOs\Product\ProductPublicIndexResult;
use App\DTOs\Product\ProductPublicListItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\ValueObjects\Money;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ListPublicProducts
{
    public function handle(ProductPublicIndexData $data): ProductPublicIndexResult
    {
        Gate::authorize('viewPublicAny', Product::class);

        $query = Product::query()
            ->with([
                'vendor',
                'media' => fn ($query) => $query->orderBy('sort_order'),
                'categories' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name'),
            ])
            ->where('status', 'active')
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->when($data->search, function ($query, string $search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when($data->category, function ($query, string $categorySlug): void {
                $query->whereHas('categories', function ($categoryQuery) use ($categorySlug): void {
                    $categoryQuery
                        ->where('slug', $categorySlug)
                        ->where('is_active', true);
                });
            })
            ->latest();

        $paginator = $query
            ->paginate($data->perPage)
            ->appends(array_filter([
                'search' => $data->search,
                'category' => $data->category,
                'per_page' => $data->perPage,
            ]));

        $paginator->setCollection(
            $paginator->getCollection()->map(function (Product $product): ProductPublicListItem {
                $image = $product->media->firstWhere('type', 'image');
                $vendor = $product->vendor;

                if ($vendor === null) {
                    throw new \RuntimeException('Product vendor is missing.');
                }

                return new ProductPublicListItem(
                    $product->id,
                    $product->name,
                    Money::fromString((string) $product->selling_price)->amount,
                    $vendor->display_name,
                    $vendor->slug,
                    $vendor->location,
                    $image ? Storage::disk('public')->url($image->path) : null,
                    $product->categories
                        ->map(static fn (ProductCategory $category): array => [
                            'id' => $category->id,
                            'name' => $category->name,
                            'slug' => $category->slug,
                        ])
                        ->values()
                        ->all(),
                );
            })
        );
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->whereHas('products', function ($productQuery): void {
                $productQuery
                    ->where('status', 'active')
                    ->whereHas('vendor', fn ($vendorQuery) => $vendorQuery->where('status', 'approved'));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ProductCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->all();

        return new ProductPublicIndexResult(
            $paginator->getCollection()->all(),
            $categories,
            $this->paginationData($paginator),
        );
    }

    /**
     * @return array{
     *     total: int,
     *     per_page: int,
     *     current_page: int,
     *     last_page: int,
     *     from: int|null,
     *     to: int|null,
     *     links: list<array{url: string|null, label: string, active: bool}>
     * }
     */
    protected function paginationData(LengthAwarePaginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'links' => $paginator->linkCollection()
                ->map(static fn (array $link): array => [
                    'url' => $link['url'],
                    'label' => $link['label'],
                    'active' => $link['active'],
                ])
                ->values()
                ->all(),
        ];
    }
}
