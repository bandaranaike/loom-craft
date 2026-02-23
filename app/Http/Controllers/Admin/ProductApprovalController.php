<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveProductRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductApprovalController extends Controller
{
    public function pending(Request $request): Response
    {
        Gate::authorize('access', User::class);

        $products = Product::query()
            ->with('vendor.user')
            ->where('status', 'pending_review')
            ->latest()
            ->get()
            ->map(function (Product $product): array {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'status' => $product->status,
                    'vendor_name' => $product->vendor?->display_name
                        ?? $product->vendor?->user?->name
                        ?? 'Unknown vendor',
                    'vendor_price' => (string) $product->vendor_price,
                    'selling_price' => (string) $product->selling_price,
                    'submitted_at' => $product->created_at?->toDateTimeString(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('admin/products/pending', [
            'products' => $products,
            'status' => session('status'),
        ]);
    }

    public function approve(
        ApproveProductRequest $request,
        Product $product,
    ): RedirectResponse {
        Gate::authorize('approve', $product);

        $product->forceFill([
            'status' => 'active',
        ])->save();

        return redirect()
            ->route('admin.products.pending')
            ->with('status', 'Product approved successfully.');
    }
}
