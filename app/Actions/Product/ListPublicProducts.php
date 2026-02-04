<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductPublicIndexData;
use App\DTOs\Product\ProductPublicIndexResult;
use App\DTOs\Product\ProductPublicListItem;
use App\Models\Product;
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
            ])
            ->where('status', 'active')
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->when($data->search, function ($query, string $search): void {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->latest();

        $paginator = $query
            ->paginate($data->perPage)
            ->appends(array_filter([
                'search' => $data->search,
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
                    $vendor->location,
                    $image ? Storage::disk('public')->url($image->path) : null,
                );
            })
        );

        return new ProductPublicIndexResult(
            $paginator->getCollection()->all(),
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
