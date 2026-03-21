<?php

use Illuminate\Support\Facades\Route;

it('renders the custom 404 error page', function () {
    $this->get('/this-page-does-not-exist')
        ->assertNotFound()
        ->assertSee('LoomCraft')
        ->assertSee('Page Not Found')
        ->assertSee('Return Home');
});

it('renders the custom 403 error page', function () {
    Route::get('/_test-error-403', fn () => abort(403));

    $this->get('/_test-error-403')
        ->assertForbidden()
        ->assertSee('Access Restricted')
        ->assertSee('Permission Required');
});

it('renders the custom 500 error page', function () {
    Route::get('/_test-error-500', fn () => abort(500));

    $this->get('/_test-error-500')
        ->assertStatus(500)
        ->assertSee('Something Went Wrong')
        ->assertSee('Internal Error');
});
