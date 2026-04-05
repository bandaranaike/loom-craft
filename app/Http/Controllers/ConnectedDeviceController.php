<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\PersonalAccessToken;

class ConnectedDeviceController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeConnectedDevices($request);

        return Inertia::render('connected-devices/index', [
            'status' => session('status'),
            'tokens' => $request->user()
                ->tokens()
                ->latest()
                ->get()
                ->map(fn (PersonalAccessToken $token): array => $this->tokenPayload($token))
                ->values()
                ->all(),
        ]);
    }

    public function destroy(Request $request, string $token): RedirectResponse
    {
        $this->authorizeConnectedDevices($request);

        $request->user()
            ->tokens()
            ->whereKey($token)
            ->firstOrFail()
            ->delete();

        return redirect()
            ->route('connected-devices.index')
            ->with('status', 'Connected device revoked.');
    }

    private function authorizeConnectedDevices(Request $request): void
    {
        abort_unless(in_array((string) $request->user()?->role, ['admin', 'vendor'], true), 403);
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     abilities: list<string>,
     *     created_at: string|null,
     *     last_used_at: string|null,
     *     expires_at: string|null
     * }
     */
    private function tokenPayload(PersonalAccessToken $token): array
    {
        /** @var list<string> $abilities */
        $abilities = is_array($token->abilities) ? array_values($token->abilities) : [];

        return [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $abilities,
            'created_at' => $token->created_at?->toIso8601String(),
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'expires_at' => $token->expires_at?->toIso8601String(),
        ];
    }
}
