<?php

test('app shell defaults to light appearance when no preference cookie exists', function () {
    $response = $this->get(route('home'));

    $html = $response->getContent();

    $response->assertOk()->assertSee("const appearance = 'light';", false);

    expect($html)->not->toContain(
        sprintf('<html lang="%s" class="dark">', str_replace('_', '-', app()->getLocale())),
    );
});

test('app shell respects an explicit dark appearance cookie', function () {
    $response = $this
        ->withUnencryptedCookie('appearance', 'dark')
        ->get(route('home'));

    $html = $response->getContent();

    $response->assertOk()->assertSee("const appearance = 'dark';", false);

    expect($html)->toContain(
        sprintf('<html lang="%s" class="dark">', str_replace('_', '-', app()->getLocale())),
    );
});
