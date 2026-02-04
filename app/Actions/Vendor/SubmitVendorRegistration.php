<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorRegistrationData;
use App\DTOs\Vendor\VendorRegistrationResult;
use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;

class SubmitVendorRegistration
{
    public function handle(VendorRegistrationData $data): VendorRegistrationResult
    {
        Gate::authorize('create', Vendor::class);

        $vendor = Vendor::query()->create([
            'user_id' => $data->user->id,
            'display_name' => $data->displayName,
            'bio' => $data->bio,
            'location' => $data->location,
            'status' => 'pending',
        ]);

        return new VendorRegistrationResult($vendor);
    }
}
