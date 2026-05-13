<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingCarrierRequest;
use App\Http\Requests\Admin\StoreShippingServiceRequest;
use App\Http\Requests\Admin\UpdateShippingCarrierRequest;
use App\Http\Requests\Admin\UpdateShippingServiceRequest;
use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShippingCarrierController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('access', User::class);

        $carriers = ShippingCarrier::query()
            ->with(['services' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->withCount('shipments')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ShippingCarrier $carrier): array => [
                'id' => $carrier->id,
                'name' => $carrier->name,
                'code' => $carrier->code,
                'is_active' => $carrier->is_active,
                'sort_order' => $carrier->sort_order,
                'shipments_count' => $carrier->shipments_count,
                'services' => $carrier->services->map(static fn (ShippingService $service): array => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'code' => $service->code,
                    'is_active' => $service->is_active,
                    'sort_order' => $service->sort_order,
                ])->all(),
            ])
            ->all();

        return Inertia::render('admin/shipping-carriers/index', [
            'carriers' => $carriers,
            'status' => session('status'),
        ]);
    }

    public function store(StoreShippingCarrierRequest $request): RedirectResponse
    {
        ShippingCarrier::query()->create([
            'name' => $request->string('name')->toString(),
            'code' => $request->string('code')->toString() ?: null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.shipping-carriers.index')
            ->with('status', 'Carrier created successfully.');
    }

    public function update(UpdateShippingCarrierRequest $request, ShippingCarrier $shippingCarrier): RedirectResponse
    {
        $shippingCarrier->update([
            'name' => $request->string('name')->toString(),
            'code' => $request->string('code')->toString() ?: null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.shipping-carriers.index')
            ->with('status', 'Carrier updated successfully.');
    }

    public function destroy(ShippingCarrier $shippingCarrier): RedirectResponse
    {
        if ($shippingCarrier->shipments()->exists()) {
            return redirect()
                ->route('admin.shipping-carriers.index')
                ->with('status', 'Carrier is in use. Archive it by setting inactive instead.');
        }

        $shippingCarrier->delete();

        return redirect()
            ->route('admin.shipping-carriers.index')
            ->with('status', 'Carrier deleted successfully.');
    }

    public function storeService(StoreShippingServiceRequest $request, ShippingCarrier $shippingCarrier): RedirectResponse
    {
        $shippingCarrier->services()->create([
            'name' => $request->string('name')->toString(),
            'code' => $request->string('code')->toString() ?: null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.shipping-carriers.index')
            ->with('status', 'Service created successfully.');
    }

    public function updateService(
        UpdateShippingServiceRequest $request,
        ShippingCarrier $shippingCarrier,
        ShippingService $shippingService,
    ): RedirectResponse {
        abort_unless($shippingService->shipping_carrier_id === $shippingCarrier->id, 404);

        $shippingService->update([
            'name' => $request->string('name')->toString(),
            'code' => $request->string('code')->toString() ?: null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->integer('sort_order'),
        ]);

        return redirect()
            ->route('admin.shipping-carriers.index')
            ->with('status', 'Service updated successfully.');
    }

    public function destroyService(ShippingCarrier $shippingCarrier, ShippingService $shippingService): RedirectResponse
    {
        abort_unless($shippingService->shipping_carrier_id === $shippingCarrier->id, 404);

        if ($shippingService->shipments()->exists()) {
            return redirect()
                ->route('admin.shipping-carriers.index')
                ->with('status', 'Service is in use. Archive it by setting inactive instead.');
        }

        $shippingService->delete();

        return redirect()
            ->route('admin.shipping-carriers.index')
            ->with('status', 'Service deleted successfully.');
    }
}
