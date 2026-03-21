@php
    $statusCode = (string) ($exception->getStatusCode() ?? '4xx');
    $title = 'Request Cannot Be Completed';
    $code = $statusCode;
    $eyebrow = 'Client-Side Error';
    $message = 'The request could not be completed as sent. The destination, permissions, or request shape may not match what this page expects.';
    $panelTitle = 'Check The Request Context';
    $panelCopy = 'These responses usually mean the page exists, but the request itself is incomplete, invalid, or not allowed in this context.';
    $tips = [
        ['label' => 'Review The URL', 'value' => 'Make sure the link or identifier is complete and current.'],
        ['label' => 'Confirm Access', 'value' => 'Some routes require the correct account, role, or session state.'],
        ['label' => 'Retry From Flow', 'value' => 'Return to the previous step and use the normal navigation path.'],
    ];
@endphp

@include('errors.layout', get_defined_vars())
