@php
    $title = 'Temporarily Unavailable';
    $code = '503';
    $eyebrow = 'Maintenance Window';
    $message = 'LoomCraft is temporarily unavailable while the platform is being updated or a dependent service is recovering.';
    $panelTitle = 'Give It A Moment';
    $panelCopy = 'Short interruptions are expected during maintenance and infrastructure restarts. Most pages should return shortly without any action from you.';
    $tips = [
        ['label' => 'Wait Briefly', 'value' => 'Try again in a minute or two.'],
        ['label' => 'Avoid Interrupted Checkout', 'value' => 'If you were paying, verify the final order state before retrying.'],
        ['label' => 'Use A Fresh Reload', 'value' => 'Reload the page instead of resubmitting old form data.'],
    ];
    $notice = 'If this page appears during deployment, a pre-rendered maintenance response may be active.';
@endphp

@include('errors.layout', get_defined_vars())
