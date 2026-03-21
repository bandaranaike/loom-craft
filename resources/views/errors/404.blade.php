@php
    $title = 'Page Not Found';
    $code = '404';
    $eyebrow = 'Lost In The Weave';
    $message = 'The page you asked for is not available here. It may have moved, been removed, or the link may be incomplete.';
    $panelTitle = 'Trace The Right Path';
    $panelCopy = 'If you were looking for a product, order, or vendor page, the reference may be outdated. Start again from a reliable entry point.';
    $tips = [
        ['label' => 'Check The Link', 'value' => 'Confirm the URL and try again carefully.'],
        ['label' => 'Browse From Home', 'value' => 'Use the main catalog or dashboard to reach the correct destination.'],
        ['label' => 'Missing Product', 'value' => 'Some public pages may no longer be available if the item changed state.'],
    ];
@endphp

@include('errors.layout', get_defined_vars())
