<?php

it('declares dark mode support for markdown emails', function () {
    $layout = file_get_contents(resource_path('views/vendor/mail/html/layout.blade.php'));
    $theme = file_get_contents(resource_path('views/vendor/mail/html/themes/default.css'));

    expect($layout)
        ->toContain('name="color-scheme" content="light dark"')
        ->toContain('name="supported-color-schemes" content="light dark"')
        ->and($theme)
        ->toContain('@media (prefers-color-scheme: dark)')
        ->toContain('[data-ogsc]')
        ->toContain('[data-ogsb]')
        ->toContain('.button-primary');
});
