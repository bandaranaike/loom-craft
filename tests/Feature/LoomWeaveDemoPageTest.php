<?php

use Inertia\Testing\AssertableInertia as Assert;

test('loom weave demo page is publicly accessible', function () {
    $this->get(route('loom-weave-demo'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('loom-weave-demo')
        );
});
