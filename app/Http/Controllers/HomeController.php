<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Suggestion;
use App\ValueObjects\Money;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $latestProducts = Product::query()
            ->with([
                'vendor',
                'media' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->where('status', 'active')
            ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
            ->latest()
            ->limit(4)
            ->get()
            ->map(function (Product $product): array {
                $image = $product->media->firstWhere('type', 'image');

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'selling_price' => Money::fromString((string) $product->selling_price)->amount,
                    'vendor_name' => $product->vendor?->display_name ?? 'Unknown vendor',
                    'image_url' => $image ? asset('storage/'.$image->path) : null,
                ];
            })
            ->values()
            ->all();

        $approvedFeedback = Suggestion::query()
            ->with(['user.vendor'])
            ->where('status', 'approved')
            ->whereHas('user', fn ($query) => $query->where('role', 'vendor'))
            ->latest()
            ->limit(3)
            ->get()
            ->map(function (Suggestion $suggestion): array {
                return [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'details' => $suggestion->details,
                    'vendor_name' => $suggestion->user?->vendor?->display_name
                        ?? $suggestion->user?->name
                        ?? 'Verified vendor',
                    'approved_at' => $suggestion->updated_at?->toDateString(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'atelier_ledger' => [
                'active_products' => Product::query()
                    ->where('status', 'active')
                    ->whereHas('vendor', fn ($query) => $query->where('status', 'approved'))
                    ->count(),
                'approved_feedback' => Suggestion::query()
                    ->where('status', 'approved')
                    ->whereHas('user', fn ($query) => $query->where('role', 'vendor'))
                    ->count(),
            ],
            'vendor_feedback' => $approvedFeedback,
            'latest_products' => $latestProducts,
        ]);
    }
}
