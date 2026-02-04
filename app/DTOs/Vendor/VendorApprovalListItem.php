<?php

namespace App\DTOs\Vendor;

class VendorApprovalListItem
{
    public function __construct(
        public int $id,
        public string $displayName,
        public ?string $location,
        public string $status,
        public ?string $submittedAt,
        public string $userName,
        public string $userEmail,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->displayName,
            'location' => $this->location,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
            'user_name' => $this->userName,
            'user_email' => $this->userEmail,
        ];
    }
}
