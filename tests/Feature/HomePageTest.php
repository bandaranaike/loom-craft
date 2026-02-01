<?php

use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;

test('home page renders', function () {
    get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('canRegister')
        );
});
