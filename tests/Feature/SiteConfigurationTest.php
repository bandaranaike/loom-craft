<?php

use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('loomcraft is the default shared site', function () {
    config(['sites.default' => 'missing-site']);

    get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.key', 'loomcraft')
            ->where('site.displayName', 'LoomCraft')
            ->where('site.theme', 'loomcraft')
            ->where('site.hideLoomFeatures', false)
        );
});

test('naturesnature is shared with inertia when selected', function () {
    config(['sites.default' => 'naturesnature']);

    get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('site.key', 'naturesnature')
            ->where('site.displayName', "Nature's Nature")
            ->where('site.theme', 'naturesnature')
            ->where('site.productsLabel', 'Pantry')
            ->where('site.vendorLabel', 'Maker')
            ->where('site.hideLoomFeatures', true)
        );
});

test('naturesnature hides loom weave demo', function () {
    config(['sites.default' => 'naturesnature']);

    get(route('loom-weave-demo'))->assertNotFound();
});
