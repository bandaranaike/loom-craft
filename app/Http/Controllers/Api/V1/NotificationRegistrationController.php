<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterNotificationTokenRequest;
use App\Models\MobileNotificationToken;
use Illuminate\Http\JsonResponse;

class NotificationRegistrationController extends Controller
{
    public function __invoke(RegisterNotificationTokenRequest $request): JsonResponse
    {
        $token = MobileNotificationToken::query()->updateOrCreate(
            ['fcm_token' => $request->validated('fcm_token')],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->validated('platform') ?: 'android',
                'last_used_at' => now(),
            ],
        );

        return response()->json([
            'message' => 'Notification token registered.',
            'data' => [
                'id' => $token->id,
                'platform' => $token->platform,
                'last_used_at' => $token->last_used_at?->toISOString(),
            ],
        ]);
    }
}
