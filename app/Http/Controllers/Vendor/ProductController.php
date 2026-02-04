<?php

namespace App\Http\Controllers\Vendor;

use App\Actions\Product\CreateProduct;
use App\Actions\Product\ListVendorProducts;
use App\Actions\Product\PrepareProductCreation;
use App\DTOs\Product\ProductCreateData;
use App\DTOs\Product\ProductCreateFormData;
use App\DTOs\Product\ProductIndexData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\IndexProductRequest;
use App\Http\Requests\Vendor\StoreProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(
        IndexProductRequest $request,
        ListVendorProducts $action,
    ): Response {
        $result = $action->handle(ProductIndexData::fromRequest($request));

        return Inertia::render('vendor/products/index', [
            ...$result->toArray(),
            'status' => session('status'),
            'search' => $request->string('search')->toString() ?: null,
            'per_page' => $request->integer('per_page') ?: 10,
        ]);
    }

    public function create(Request $request, PrepareProductCreation $action): Response
    {
        $result = $action->handle(ProductCreateFormData::fromUser($request->user()));

        return Inertia::render('vendor/products/create', [
            ...$result->toArray(),
            'status' => session('status'),
        ]);
    }

    public function store(
        StoreProductRequest $request,
        CreateProduct $action,
    ): RedirectResponse {
        $action->handle(ProductCreateData::fromRequest($request));

        return redirect()
            ->route('vendor.products.create')
            ->with('status', 'Product submitted for review.');
    }
}
