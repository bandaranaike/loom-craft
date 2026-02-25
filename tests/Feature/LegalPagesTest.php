<?php

use Inertia\Testing\AssertableInertia as Assert;

test('privacy policy page is publicly accessible', function () {
    $this->get(route('privacy-policy'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('privacy-policy')
        );
});

test('terms of service page is publicly accessible', function () {
    $this->get(route('terms-of-service'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('terms-of-service')
        );
});
