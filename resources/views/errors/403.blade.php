@php
    $title = 'Access Restricted';
    $code = '403';
    $eyebrow = 'Permission Required';
    $message = 'This area is not available for your current account or session. The request was understood, but access is intentionally blocked.';
    $panelTitle = 'Use The Correct Access Path';
    $panelCopy = 'Some LoomCraft pages are limited to customers, approved vendors, or admins. Opening a direct link will not bypass those checks.';
    $tips = [
        ['label' => 'Check Your Role', 'value' => 'Sign in with the account that has permission for this page.'],
        ['label' => 'Session Scope', 'value' => 'Guest-only order actions work only from the original checkout session.'],
        ['label' => 'Need Help', 'value' => 'If access seems incorrect, review the workflow that led you here.'],
    ];
@endphp

@include('errors.layout', get_defined_vars())
