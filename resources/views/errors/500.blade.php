@php
    $title = 'Something Went Wrong';
    $code = '500';
    $eyebrow = 'Internal Error';
    $message = 'The request reached LoomCraft, but the application could not complete it. This is a server-side failure rather than a problem with your browser.';
    $panelTitle = 'Try A Clean Retry';
    $panelCopy = 'If this happened during checkout, order review, or product management, go back one step and retry after the page settles.';
    $tips = [
        ['label' => 'Refresh Carefully', 'value' => 'Reload once before repeating any sensitive action like payment.'],
        ['label' => 'Avoid Duplicate Actions', 'value' => 'Do not repeatedly resubmit the same form while the error persists.'],
        ['label' => 'Operational Follow-Up', 'value' => 'If the problem continues, the application logs should be checked next.'],
    ];
    $notice = 'This page is intentionally lightweight so it still renders when the main application layout is unavailable.';
@endphp

@include('errors.layout', get_defined_vars())
