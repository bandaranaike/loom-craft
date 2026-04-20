<?php

namespace App\Policies;

use App\Models\ContactSubmission;
use App\Models\User;

class ContactSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, ContactSubmission $contactSubmission): bool
    {
        return $user->role === 'admin';
    }

    public function updateStatus(User $user, ContactSubmission $contactSubmission): bool
    {
        return $user->role === 'admin';
    }

    public function reply(User $user, ContactSubmission $contactSubmission): bool
    {
        return $user->role === 'admin';
    }
}
