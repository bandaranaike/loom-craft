<?php

use Inertia\Testing\AssertableInertia as Assert;

test('manage plans page is publicly accessible', function () {
    $this->get(route('plans.manage'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('manage-plans')
        );
});

test('autopay cancellation link sends customers to manage plans', function () {
    $this->get(route('autopay.cancel'))
        ->assertRedirect(route('plans.manage'));
});
