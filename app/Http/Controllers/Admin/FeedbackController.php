<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveFeedbackRequest;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function pending(Request $request): Response
    {
        Gate::authorize('viewAny', Suggestion::class);

        $feedback = Suggestion::query()
            ->with(['user.vendor'])
            ->where('status', 'pending')
            ->whereHas('user', fn ($query) => $query->where('role', 'vendor'))
            ->latest()
            ->get()
            ->map(function (Suggestion $suggestion): array {
                return [
                    'id' => $suggestion->id,
                    'title' => $suggestion->title,
                    'details' => $suggestion->details,
                    'vendor_name' => $suggestion->user?->vendor?->display_name
                        ?? $suggestion->user?->name
                        ?? 'Unknown vendor',
                    'submitted_at' => $suggestion->created_at?->toDateTimeString(),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('admin/feedback/pending', [
            'feedback' => $feedback,
            'status' => session('status'),
        ]);
    }

    public function approve(
        ApproveFeedbackRequest $request,
        Suggestion $suggestion,
    ): RedirectResponse {
        Gate::authorize('approve', $suggestion);

        $suggestion->forceFill([
            'status' => 'approved',
            'handled_by' => $request->user()?->id,
        ])->save();

        return redirect()
            ->route('admin.feedback.pending')
            ->with('status', 'Feedback approved and published on the home page.');
    }
}
