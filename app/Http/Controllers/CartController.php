<?php

namespace App\Http\Controllers;

use App\Actions\Cart\ShowCart;
use App\DTOs\Cart\CartSessionData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class CartController extends Controller
{
    public function show(Request $request, ShowCart $action): Response
    {
        $result = $action->handle(CartSessionData::fromRequest($request));

        $response = Inertia::render('cart', [
            ...$result->toArray(),
            'canRegister' => Features::enabled(Features::registration()),
        ]);

        if ($request->user() === null && $result->guestToken !== null) {
            Cookie::queue(cookie('loomcraft_guest_token', $result->guestToken, 60 * 24 * 30));
        }

        return $response;
    }
}
