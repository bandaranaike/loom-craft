<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactSubmissionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyContactSubmissionRequest;
use App\Http\Requests\Admin\UpdateContactSubmissionStatusRequest;
use App\Mail\ContactSubmissionReplyMail;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ContactSubmissionController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', ContactSubmission::class);

        $submissions = ContactSubmission::query()
            ->with(['user', 'repliedBy'])
            ->latest('submitted_at')
            ->get()
            ->map(static function (ContactSubmission $submission): array {
                $status = $submission->status instanceof ContactSubmissionStatus
                    ? $submission->status->value
                    : (string) $submission->status;

                return [
                    'id' => $submission->id,
                    'name' => $submission->name,
                    'email' => $submission->email,
                    'phone' => $submission->phone,
                    'message' => $submission->message,
                    'status' => $status,
                    'submitted_at' => $submission->submitted_at?->toDateTimeString(),
                    'replied_at' => $submission->replied_at?->toDateTimeString(),
                    'latest_reply_message' => $submission->latest_reply_message,
                    'customer_name' => $submission->user?->name,
                ];
            })
            ->all();

        return Inertia::render('admin/contact-submissions/index', [
            'submissions' => $submissions,
            'statusOptions' => collect(ContactSubmissionStatus::cases())
                ->map(static fn (ContactSubmissionStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->values()
                ->all(),
            'status' => session('status'),
        ]);
    }

    public function updateStatus(
        UpdateContactSubmissionStatusRequest $request,
        ContactSubmission $contactSubmission,
    ): RedirectResponse {
        Gate::authorize('updateStatus', $contactSubmission);

        $contactSubmission->forceFill([
            'status' => $request->enum('status', ContactSubmissionStatus::class),
        ])->save();

        return redirect()
            ->route('admin.contact-submissions.index')
            ->with('status', 'Contact message status updated successfully.');
    }

    public function reply(
        ReplyContactSubmissionRequest $request,
        ContactSubmission $contactSubmission,
    ): RedirectResponse {
        Gate::authorize('reply', $contactSubmission);

        if (! $contactSubmission->email) {
            return redirect()
                ->route('admin.contact-submissions.index')
                ->with('status', 'This contact message does not have an email address for replies.');
        }

        $replyMessage = $request->string('reply_message')->toString();

        Mail::to($contactSubmission->email)->send(
            new ContactSubmissionReplyMail($contactSubmission, $replyMessage)
        );

        $contactSubmission->forceFill([
            'status' => ContactSubmissionStatus::Replied,
            'latest_reply_message' => $replyMessage,
            'replied_at' => now(),
            'replied_by' => $request->user()?->id,
        ])->save();

        return redirect()
            ->route('admin.contact-submissions.index')
            ->with('status', 'Reply email sent successfully.');
    }
}
