<?php

namespace App\Http\Controllers\Vendor;

use App\Actions\Product\AddProductImages;
use App\Actions\Product\CreateProduct;
use App\Actions\Product\ListVendorProducts;
use App\Actions\Product\PrepareProductCreation;
use App\Actions\Product\PrepareProductEditing;
use App\Actions\Product\RemoveProductImage;
use App\Actions\Product\UpdateProduct;
use App\DTOs\Product\ProductCreateData;
use App\DTOs\Product\ProductCreateFormData;
use App\DTOs\Product\ProductEditFormData;
use App\DTOs\Product\ProductIndexData;
use App\DTOs\Product\ProductUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\DestroyProductImageRequest;
use App\Http\Requests\Vendor\IndexProductRequest;
use App\Http\Requests\Vendor\StoreProductImagesRequest;
use App\Http\Requests\Vendor\StoreProductRequest;
use App\Http\Requests\Vendor\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductMedia;
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
            'commission_rate' => (string) config('commerce.commission_rate'),
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

    public function edit(
        Product $product,
        Request $request,
        PrepareProductEditing $action,
    ): Response {
        $result = $action->handle(ProductEditFormData::fromModel($request->user(), $product));

        return Inertia::render('vendor/products/edit', [
            ...$result->toArray(),
            'status' => session('status'),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProduct $action,
    ): RedirectResponse {
        $action->handle(ProductUpdateData::fromRequest($request, $product));

        return redirect()
            ->route('vendor.products.index')
            ->with('status', 'Product updated successfully.');
    }

    public function storeImages(
        StoreProductImagesRequest $request,
        Product $product,
        AddProductImages $action,
    ): RedirectResponse {
        $images = $request->file('images', []);
        $action->handle($product, is_array($images) ? $images : []);

        return redirect()
            ->route('vendor.products.edit', $product)
            ->with('status', 'Product images uploaded successfully.');
    }

    public function destroyImage(
        DestroyProductImageRequest $request,
        Product $product,
        ProductMedia $image,
        RemoveProductImage $action,
    ): RedirectResponse {
        $action->handle($product, $image);

        return redirect()
            ->route('vendor.products.edit', $product)
            ->with('status', 'Product image removed successfully.');
    }
}
