@php
    $statusCode = (string) ($exception->getStatusCode() ?? '5xx');
    $title = 'Server Error';
    $code = $statusCode;
    $eyebrow = 'Service Interruption';
    $message = 'The application encountered a server-side problem while trying to finish your request. This usually needs a retry or operator review.';
    $panelTitle = 'Pause, Then Retry';
    $panelCopy = 'Unexpected server errors can happen during deployments, service interruptions, or when dependent systems fail temporarily.';
    $tips = [
        ['label' => 'Retry Once', 'value' => 'A single refresh is reasonable after a short pause.'],
        ['label' => 'Protect Sensitive Actions', 'value' => 'Avoid duplicate checkout or update submissions until the page recovers.'],
        ['label' => 'Escalate If Persistent', 'value' => 'If the same error repeats, logs and service health should be checked.'],
    ];
    $notice = 'The error page is rendered independently from the main app shell so it remains available during broader failures.';
@endphp

@include('errors.layout', get_defined_vars())
