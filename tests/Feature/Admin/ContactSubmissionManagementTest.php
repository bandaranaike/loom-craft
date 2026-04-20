<?php

use App\Enums\ContactSubmissionStatus;
use App\Mail\ContactSubmissionReplyMail;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

test('admins can view all contact submissions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ContactSubmission::factory()->count(2)->create();

    $response = $this->actingAs($admin)->get(route('admin.contact-submissions.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/contact-submissions/index')
            ->has('submissions', 2)
            ->has('statusOptions', 4)
        );
});

test('admins can update contact submission statuses', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $submission = ContactSubmission::factory()->create([
        'status' => ContactSubmissionStatus::New,
    ]);

    $response = $this->actingAs($admin)->patch(
        route('admin.contact-submissions.status.update', $submission),
        ['status' => ContactSubmissionStatus::InProgress->value],
    );

    $response->assertRedirect(route('admin.contact-submissions.index'));

    $this->assertDatabaseHas('contact_submissions', [
        'id' => $submission->id,
        'status' => ContactSubmissionStatus::InProgress->value,
    ]);
});

test('admins can reply to contact submissions by email', function () {
    Mail::fake();

    $admin = User::factory()->create(['role' => 'admin']);
    $submission = ContactSubmission::factory()->create([
        'email' => 'customer@example.com',
        'status' => ContactSubmissionStatus::InProgress,
    ]);

    $response = $this->actingAs($admin)->post(
        route('admin.contact-submissions.reply', $submission),
        ['reply_message' => 'We have reviewed your message and will follow up today.'],
    );

    $response->assertRedirect(route('admin.contact-submissions.index'));

    Mail::assertSent(ContactSubmissionReplyMail::class, function (ContactSubmissionReplyMail $mail) use ($submission) {
        return $mail->hasTo('customer@example.com')
            && $mail->contactSubmission->is($submission);
    });

    $this->assertDatabaseHas('contact_submissions', [
        'id' => $submission->id,
        'status' => ContactSubmissionStatus::Replied->value,
        'replied_by' => $admin->id,
        'latest_reply_message' => 'We have reviewed your message and will follow up today.',
    ]);
});

test('non admins cannot manage contact submissions', function () {
    $user = User::factory()->create(['role' => 'customer']);
    $submission = ContactSubmission::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.contact-submissions.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->patch(route('admin.contact-submissions.status.update', $submission), [
            'status' => ContactSubmissionStatus::Closed->value,
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->post(route('admin.contact-submissions.reply', $submission), [
            'reply_message' => 'Blocked',
        ])
        ->assertForbidden();
});
