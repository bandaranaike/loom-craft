<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class VendorProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $vendor = $request->user()->vendor;
        abort_unless($vendor !== null && $request->user()->can('update', $vendor), 403);

        return Inertia::render('vendor/profile/edit', [
            'vendor' => [
                'display_name' => $vendor->display_name,
                'slug' => $vendor->slug,
                'bio' => $vendor->bio,
                'tagline' => $vendor->tagline,
                'website_url' => $vendor->website_url,
                'contact_email' => $vendor->contact_email,
                'contact_phone' => $vendor->contact_phone,
                'whatsapp_number' => $vendor->whatsapp_number,
                'about_title' => $vendor->about_title,
                'craft_specialties' => $vendor->craft_specialties ?? [],
                'years_active' => $vendor->years_active,
                'location' => $vendor->location,
                'status' => $vendor->status,
                'is_contact_public' => $vendor->is_contact_public,
                'is_website_public' => $vendor->is_website_public,
                'logo_url' => $vendor->logo_path
                    ? Storage::disk('public')->url($vendor->logo_path)
                    : null,
                'cover_image_url' => $vendor->cover_image_path
                    ? Storage::disk('public')->url($vendor->cover_image_path)
                    : null,
                'locations' => $vendor->locations()
                    ->orderByDesc('is_primary')
                    ->orderBy('location_name')
                    ->get()
                    ->map(fn ($location): array => [
                        'id' => $location->id,
                        'location_name' => $location->location_name,
                        'address_line_1' => $location->address_line_1,
                        'address_line_2' => $location->address_line_2,
                        'city' => $location->city,
                        'region' => $location->region,
                        'postal_code' => $location->postal_code,
                        'country' => $location->country,
                        'phone' => $location->phone,
                        'hours' => $location->hours,
                        'map_url' => $location->map_url,
                        'is_primary' => $location->is_primary,
                    ])
                    ->values()
                    ->all(),
            ],
            'status' => session('status'),
        ]);
    }

    public function update(UpdateVendorProfileRequest $request): RedirectResponse
    {
        $vendor = $request->user()->vendor;
        $yearsActive = $request->input('years_active');
        $locations = collect($request->validated('locations', []));

        DB::transaction(function () use ($locations, $request, $vendor, $yearsActive): void {
            $vendor->forceFill([
                'display_name' => $request->string('display_name')->toString(),
                'slug' => $request->string('slug')->toString(),
                'bio' => $request->string('bio')->toString() ?: null,
                'tagline' => $request->string('tagline')->toString() ?: null,
                'website_url' => $request->string('website_url')->toString() ?: null,
                'contact_email' => $request->string('contact_email')->toString() ?: null,
                'contact_phone' => $request->string('contact_phone')->toString() ?: null,
                'whatsapp_number' => $request->string('whatsapp_number')->toString() ?: null,
                'about_title' => $request->string('about_title')->toString() ?: null,
                'craft_specialties' => $request->validated('craft_specialties'),
                'years_active' => $yearsActive === null || $yearsActive === ''
                    ? null
                    : $request->integer('years_active'),
                'location' => $request->string('location')->toString() ?: null,
                'is_contact_public' => $request->boolean('is_contact_public'),
                'is_website_public' => $request->boolean('is_website_public'),
            ]);

            if ($request->hasFile('logo')) {
                if ($vendor->logo_path !== null) {
                    Storage::disk('public')->delete($vendor->logo_path);
                }

                $vendor->logo_path = Storage::disk('public')->putFile(
                    'vendors/logos',
                    $request->file('logo'),
                );
            }

            if ($request->hasFile('cover_image')) {
                if ($vendor->cover_image_path !== null) {
                    Storage::disk('public')->delete($vendor->cover_image_path);
                }

                $vendor->cover_image_path = Storage::disk('public')->putFile(
                    'vendors/covers',
                    $request->file('cover_image'),
                );
            }

            $vendor->save();

            $this->syncLocations($vendor, $locations);
        });

        return redirect()
            ->route('vendor.profile.edit')
            ->with('status', 'Vendor profile updated successfully.');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $locations
     */
    private function syncLocations($vendor, Collection $locations): void
    {
        $keepIds = $locations
            ->pluck('id')
            ->filter()
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if ($keepIds === []) {
            $vendor->locations()->delete();
        } else {
            $vendor->locations()->whereNotIn('id', $keepIds)->delete();
        }

        $primaryIndex = $locations->search(
            static fn (array $location): bool => $location['is_primary'] === true,
        );

        if ($primaryIndex === false && $locations->isNotEmpty()) {
            $primaryIndex = 0;
        }

        $locations->values()->each(function (array $location, int $index) use ($primaryIndex, $vendor): void {
            $attributes = [
                'location_name' => $location['location_name'],
                'address_line_1' => $location['address_line_1'],
                'address_line_2' => $location['address_line_2'],
                'city' => $location['city'],
                'region' => $location['region'],
                'postal_code' => $location['postal_code'],
                'country' => $location['country'],
                'phone' => $location['phone'],
                'hours' => $location['hours'],
                'map_url' => $location['map_url'],
                'is_primary' => $index === $primaryIndex,
            ];

            if (! empty($location['id'])) {
                $vendor->locations()->whereKey($location['id'])->update($attributes);

                return;
            }

            $vendor->locations()->create($attributes);
        });
    }
}
