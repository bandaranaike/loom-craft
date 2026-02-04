<?php

namespace App\DTOs\Vendor;

class VendorApprovalIndexResult
{
    /**
     * @param  list<VendorApprovalListItem>  $vendors
     * @param  array{
     *     total: int,
     *     per_page: int,
     *     current_page: int,
     *     last_page: int,
     *     from: int|null,
     *     to: int|null,
     *     links: list<array{url: string|null, label: string, active: bool}>
     * }  $pagination
     */
    public function __construct(
        public array $vendors,
        public array $pagination,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'vendors' => array_map(
                static fn (VendorApprovalListItem $item): array => $item->toArray(),
                $this->vendors,
            ),
            'pagination' => $this->pagination,
        ];
    }
}
