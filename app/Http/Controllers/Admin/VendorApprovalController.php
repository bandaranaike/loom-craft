<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Vendor\ApproveVendor;
use App\Actions\Vendor\ListPendingVendors;
use App\Actions\Vendor\RejectVendor;
use App\DTOs\Vendor\VendorApprovalDecisionData;
use App\DTOs\Vendor\VendorApprovalIndexData;
use App\DTOs\Vendor\VendorRejectionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveVendorRequest;
use App\Http\Requests\Admin\RejectVendorRequest;
use App\Models\Vendor;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class VendorApprovalController extends Controller
{
    protected const PER_PAGE_COOKIE = 'vendor_pending_per_page';

    public function pending(Request $request, ListPendingVendors $action): Response
    {
        $search = $request->string('search')->toString();
        $perPageInput = $request->integer('per_page');
        $cookiePerPage = $request->cookie(self::PER_PAGE_COOKIE);
        $rawCookiePerPage = $request->cookies->get(self::PER_PAGE_COOKIE);
        $sessionPerPage = $request->session()->get(self::PER_PAGE_COOKIE);

        if ($cookiePerPage === null && is_numeric($rawCookiePerPage)) {
            $cookiePerPage = $rawCookiePerPage;
        }

        if (! is_numeric($cookiePerPage) && $rawCookiePerPage) {
            try {
                $decrypted = Crypt::decryptString($rawCookiePerPage);
                $decrypted = CookieValuePrefix::remove($decrypted);
                $cookiePerPage = is_numeric($decrypted) ? $decrypted : $cookiePerPage;
            } catch (\Throwable) {
                // Ignore invalid cookie values.
            }
        }

        if (! is_numeric($cookiePerPage) && is_string($cookiePerPage)) {
            $unserialized = @unserialize($cookiePerPage);
            if (is_string($unserialized) && is_numeric($unserialized)) {
                $cookiePerPage = $unserialized;
            }
        }
        $perPage = $this->resolvePerPage(
            $perPageInput ?? (is_numeric($cookiePerPage) ? (int) $cookiePerPage : $sessionPerPage)
        );

        $result = $action->handle(
            VendorApprovalIndexData::forPending($request->user(), $search, $perPage)
        );

        $response = Inertia::render('admin/vendors/pending', [
            ...$result->toArray(),
            'status' => session('status'),
            'search' => $search !== '' ? $search : null,
            'per_page' => $perPage,
        ]);

        $httpResponse = $response->toResponse($request);

        if ($perPageInput !== null) {
            $httpResponse->headers->setCookie(
                cookie(self::PER_PAGE_COOKIE, (string) $perPage, 525600)
            );
            $request->session()->put(self::PER_PAGE_COOKIE, $perPage);
        }

        return $httpResponse;
    }

    public function approve(
        ApproveVendorRequest $request,
        Vendor $vendor,
        ApproveVendor $action,
    ): RedirectResponse {
        $action->handle(VendorApprovalDecisionData::fromRequest($request, $vendor));

        return redirect()
            ->route('admin.vendors.pending')
            ->with('status', 'Vendor approved successfully.');
    }

    public function reject(
        RejectVendorRequest $request,
        Vendor $vendor,
        RejectVendor $action,
    ): RedirectResponse {
        $result = $action->handle(VendorRejectionData::fromRequest($request, $vendor));

        return redirect()
            ->route('admin.vendors.pending')
            ->with('status', "Vendor rejected: {$result->reason}");
    }

    protected function resolvePerPage(?int $perPage): int
    {
        $allowed = [10, 25, 50];

        if ($perPage === null) {
            return 10;
        }

        return in_array($perPage, $allowed, true) ? $perPage : 10;
    }
}
