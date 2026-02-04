<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorApprovalIndexData;
use App\DTOs\Vendor\VendorApprovalIndexResult;
use App\DTOs\Vendor\VendorApprovalListItem;
use App\Models\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class ListPendingVendors
{
    public function handle(VendorApprovalIndexData $data): VendorApprovalIndexResult
    {
        Gate::authorize('viewAny', Vendor::class);

        $query = Vendor::query()
            ->with('user')
            ->where('status', $data->status)
            ->when($data->search, function ($query, string $search): void {
                $query->where('display_name', 'like', '%'.$search.'%');
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
                static fn (Vendor $vendor): VendorApprovalListItem => new VendorApprovalListItem(
                    $vendor->id,
                    $vendor->display_name,
                    $vendor->location,
                    $vendor->status,
                    $vendor->created_at?->toDateTimeString(),
                    $vendor->user?->name ?? 'Unknown',
                    $vendor->user?->email ?? 'Unknown',
                )
            )
        );

        return new VendorApprovalIndexResult(
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
