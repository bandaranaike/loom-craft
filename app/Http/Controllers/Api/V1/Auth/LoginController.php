<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\AuthUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->with('vendor')
            ->where('email', $request->validated('email'))
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! in_array($user->role, ['admin', 'vendor'], true)) {
            abort(403, 'Only admin and vendor users can access the mobile API.');
        }

        if ($user->role === 'vendor' && ($user->vendor === null || $user->vendor->status !== 'approved')) {
            abort(403, 'Vendor approval is required before using the mobile API.');
        }

        $deviceName = $request->validated('device_name') ?: 'loomcraft-mobile';
        $token = $user->createToken($deviceName, $this->tokenAbilitiesFor($user))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new AuthUserResource($user),
        ]);
    }

    /**
     * @return list<string>
     */
    private function tokenAbilitiesFor(User $user): array
    {
        if ($user->role === 'admin') {
            return ['orders:read', 'orders:update', 'notifications:register', 'stickers:read'];
        }

        return ['orders:read', 'orders:update', 'notifications:register'];
    }
}
