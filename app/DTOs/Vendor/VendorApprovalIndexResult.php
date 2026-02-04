<?php

namespace App\DTOs\Vendor;

class VendorApprovalIndexResult
{
    /**
     * @param  list<VendorApprovalListItem>  $vendors
     */
    public function __construct(
        public array $vendors,
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
        ];
    }
}
