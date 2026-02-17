<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreFeedbackRequest;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function create(): Response
    {
        Gate::authorize('create', Suggestion::class);

        return Inertia::render('vendor/feedback/create', [
            'status' => session('status'),
        ]);
    }

    public function store(StoreFeedbackRequest $request): RedirectResponse
    {
        Gate::authorize('create', Suggestion::class);

        Suggestion::query()->updateOrCreate([
            'user_id' => $request->user()?->id,
        ], [
            'title' => $request->string('title')->toString(),
            'details' => $request->string('details')->toString(),
            'status' => 'pending',
            'handled_by' => null,
        ]);

        return back()
            ->with('status', 'Feedback submitted for admin approval.');
    }
}
