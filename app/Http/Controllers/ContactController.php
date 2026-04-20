<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactSubmissionRequest;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('contact-us', [
            'status' => session('status'),
            'formDefaults' => [
                'name' => (string) ($user?->name ?? ''),
                'email' => (string) ($user?->email ?? ''),
                'phone' => (string) ($user?->phone ?? ''),
                'message' => '',
            ],
        ]);
    }

    public function store(StoreContactSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ContactSubmission::query()->create([
            'user_id' => $request->user()?->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'],
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('contact.show')
            ->with('status', 'Your message has been received. A LoomCraft admin will review it shortly.');
    }
}
