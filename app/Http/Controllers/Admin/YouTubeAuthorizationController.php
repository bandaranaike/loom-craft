<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\HandleYouTubeCallback;
use App\Actions\Admin\PrepareYouTubeAuthorization;
use App\DTOs\Admin\YouTubeCallbackData;
use App\DTOs\Admin\YouTubeConnectData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\YouTubeOAuthCallbackRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YouTubeAuthorizationController extends Controller
{
    public function connect(Request $request, PrepareYouTubeAuthorization $action): Response
    {
        $result = $action->handle(YouTubeConnectData::fromUser($request->user()));

        return Inertia::render('admin/youtube/connect', [
            ...$result->toArray(),
            'status' => session('status'),
            'refresh_token' => session('youtube_refresh_token'),
        ]);
    }

    public function callback(
        YouTubeOAuthCallbackRequest $request,
        HandleYouTubeCallback $action,
    ): RedirectResponse {
        $result = $action->handle(YouTubeCallbackData::fromRequest($request));

        return redirect()
            ->route('admin.youtube.connect')
            ->with('status', 'Refresh token received. Store it in YOUTUBE_REFRESH_TOKEN.')
            ->with('youtube_refresh_token', $result->refreshToken);
    }
}
