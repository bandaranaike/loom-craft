<?php

namespace App\Actions\Vendor;

use App\DTOs\Vendor\VendorRegistrationData;
use App\DTOs\Vendor\VendorRegistrationResult;
use App\Models\Vendor;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class SubmitVendorRegistration
{
    public function handle(VendorRegistrationData $data): VendorRegistrationResult
    {
        Gate::authorize('create', Vendor::class);

        $vendor = Vendor::query()->create([
            'user_id' => $data->user->id,
            'display_name' => $data->displayName,
            'slug' => $this->uniqueSlug($data->displayName),
            'status' => 'pending',
        ]);

        return new VendorRegistrationResult($vendor);
    }

    protected function uniqueSlug(string $displayName): string
    {
        $base = Str::slug($displayName);
        $slug = $base !== '' ? $base : 'vendor';
        $counter = 1;

        while (Vendor::query()->where('slug', $slug)->exists()) {
            $slug = ($base !== '' ? $base : 'vendor').'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
