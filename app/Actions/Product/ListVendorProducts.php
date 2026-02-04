<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductIndexData;
use App\DTOs\Product\ProductIndexResult;
use App\DTOs\Product\ProductListItem;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class ListVendorProducts
{
    public function handle(ProductIndexData $data): ProductIndexResult
    {
        Gate::authorize('viewAny', Product::class);

        $vendor = $data->user->vendor;

        if ($vendor === null) {
            throw new \RuntimeException('Vendor profile is required to view products.');
        }

        $query = Product::query()
            ->where('vendor_id', $vendor->id)
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
            $paginator->getCollection()->map(
                static fn (Product $product): ProductListItem => new ProductListItem(
                    $product->id,
                    $product->name,
                    $product->status,
                    (string) $product->vendor_price,
                    (string) $product->selling_price,
                    $product->created_at?->toDateTimeString(),
                )
            )
        );

        return new ProductIndexResult(
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
