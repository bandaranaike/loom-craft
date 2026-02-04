<?php

namespace App\Http\Controllers\Vendor;

use App\Actions\Vendor\SubmitVendorRegistration;
use App\DTOs\Vendor\VendorRegistrationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class VendorRegistrationController extends Controller
{
    public function register(): Response
    {
        return Inertia::render('vendor/register', [
            'status' => session('status'),
        ]);
    }

    public function store(
        StoreVendorRegistrationRequest $request,
        SubmitVendorRegistration $action,
    ): RedirectResponse {
        $action->handle(VendorRegistrationData::fromRequest($request));

        return redirect()
            ->route('dashboard')
            ->with('status', 'Your vendor application has been submitted for review.');
    }
}
