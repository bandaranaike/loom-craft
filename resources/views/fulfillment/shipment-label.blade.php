@php
    $isPdf = ($printMode ?? 'web') === 'pdf';
@endphp
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>{{ $label['document_title'] }}</title>
    <style>
        @page {
            size: 465pt 567pt; /* 620px x 756px at 96dpi */
            margin: 0;
        }

        :root {
            --ink: #111111;
            --ink-soft: #4d4d4d;
            --rule: rgba(17, 17, 17, 0.22);
            --page-bg: #efefef;
            --panel: #ffffff;
            --panel-rule: 1px solid var(--rule);
            --radius: 18px;
            --space: 12px;
            --space-sm: 10px;
        }

        * {
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        body {
            width: 465pt;
            height: 567pt;
            margin: 0;
            min-height: 100vh;
            padding: 10px;
            background: var(--page-bg);
            font-family: Manrope, Arial, sans-serif;
            color: var(--ink);
        }

        @page {
            size: 465pt 567pt;
            margin: 0;
        }


        body.pdf .label {
            width: 465pt;
            height: 567pt;
            min-height: 0;
        }

        body.pdf {
            overflow: hidden;
            min-height: auto;
            padding: 0;
            background: #ffffff;
            width: 465pt;
            height: 567pt;
            margin: 0;
        }

        .actions {
            width: min(100%, 620px);
            margin: 0 auto 10px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .actions a,
        .actions button {
            border: 1px solid #111111;
            border-radius: 999px;
            background: #ffffff;
            color: #111111;
            cursor: pointer;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            padding: 9px 14px;
            text-decoration: none;
            text-transform: uppercase;
        }

        body.pdf .actions {
            display: none;
        }

        .label {
            width: min(100%, 620px);
            margin: 0 auto;
            background: var(--panel);
            border: var(--panel-rule);
            border-radius: var(--radius);
            overflow: hidden;
        }

        body.pdf .label {
            width: 465pt;
            height: 567pt;
            min-height: 0;
            border-radius: 13.5pt;
            overflow: hidden;
        }

        body.pdf .shell {
            height: 100%;
        }

        .shell,
        .meta-grid,
        .address-card,
        .micro-row,
        .care-grid,
        .refs-grid,
        .ref-block,
        .track-box {
            display: grid;
        }

        .shell {
            gap: 0;
            padding: 0;
        }

        .topbar,
        .hero,
        .footer {
            display: grid;
            gap: 0;
            border-top: var(--panel-rule);
        }

        .footer,
        .barcode-card,
        .care-card,
        .specs-card,
        .hero,
        .topbar {
            page-break-inside: avoid;
        }

        .topbar {
            border-top: 0;
            grid-template-columns: minmax(80px, 0.26fr) minmax(532px, 1.76fr);
            align-items: stretch;
        }

        .topbar > * + *,
        .hero > * + * {
            border-left: var(--panel-rule);
        }

        .card,
        .care-item,
        .qr-box {
            background: var(--panel);
        }

        .card {
            border: 0;
            border-radius: 0;
        }

        .brand-card {
            padding: var(--space);
            gap: 10px;
            align-items: center;
        }

        .brand-logo {
            width: 62px;
            height: 62px;
            object-fit: contain;
        }

        .brand-name {
            font-size: 11px;
            font-weight: 800;
            margin-top: -4px;
        }

        .meta-card,
        .address-block,
        .specs-card,
        .care-card,
        .barcode-card > div,
        .origin-card {
            padding: var(--space);
        }

        .meta-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px 10px;
        }

        .meta-item {
            padding-bottom: 6px;
            border-bottom: var(--panel-rule);
        }

        .meta-item:nth-last-child(-n + 3) {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .meta-label {
            font-size: 8px;
            font-weight: 700;
            letter-spacing: 0.14em;
            opacity: 0.7;
        }

        .meta-value {
            margin-top: 4px;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .address-card {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 10px;
            align-items: stretch;
        }

        .section-label,
        .ref-label,
        .track-title,
        .track-subtitle,
        .care-title,
        .meta-label {
            text-transform: uppercase;
        }

        .section-label,
        .ref-label,
        .track-title {
            color: var(--ink-soft);
        }

        .section-label {
            margin: 0 0 6px;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.16em;
        }

        .recipient,
        .product-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .recipient {
            line-height: 1;
            letter-spacing: 0.03em;
            overflow-wrap: anywhere;
        }

        .address,
        .micro-line {
            font-size: 14px;
        }

        .address {
            margin: 8px 0 0;
            line-height: 1.55;
            color: #1c1c1c;
        }

        .micro-row {
            gap: 4px;
            margin-top: 2px;
        }

        .micro-line {
            line-height: 1.5;
            color: var(--ink-soft);
        }

        .address-block + .address-block {
            border-left: var(--panel-rule);
        }

        .product-title {
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        .spec-grid {
            display: grid;
            grid-template-columns: auto 1fr auto 1fr;
            gap: 6px 12px;
            margin-top: 10px;
            font-size: 13px;
        }

        .spec-grid dt {
            font-weight: 700;
            color: var(--ink-soft);
        }

        .spec-grid dd {
            margin: 0;
            font-weight: 400;
            overflow-wrap: anywhere;
        }

        .care-card,
        .specs-card {
            border-top: var(--panel-rule);
        }

        .specs-card {
            padding-bottom: 0;
        }

        .care-grid {
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 6px 4px;
        }

        .care-item {
            padding: 6px 4px;
            align-content: start;
            justify-items: center;
            gap: 4px;
            text-align: center;
        }

        .care-icon {
            width: 48px;
            height: 48px;
            object-fit: contain;
            filter: grayscale(1) brightness(0) contrast(1.2);
        }

        .care-title {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.06em;
            line-height: 1.2;
        }

        .origin-card {
            align-items: center;
            border-left: var(--panel-rule);
            margin-top: -31px;
            margin-bottom: -20px;
            vertical-align: center;
            padding-top: 14px;
            padding-left: 8px;
        }

        .origin-seal {
            width: auto;
            height: 90px;
            object-fit: contain;
        }

        .barcode-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 138px;
            gap: 10px;
            align-items: stretch;
        }

        .barcode-card > * + * {
            border-left: var(--panel-rule);
            padding-left: var(--space-sm);
        }

        img.barcode {
            height: 48px;
            object-fit: fill;
        }

        img.barcode.small {
            height: 36px;
        }

        .barcode-meta {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 6px;
            font-size: 11px;
            color: var(--ink-soft);
            flex-direction: column;
        }

        .barcode-no {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.14em;
            color: var(--ink);
            overflow-wrap: anywhere;
        }

        .refs-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
            margin-top: 10px;
        }

        .ref-block {
            gap: 4px;
        }

        .ref-label {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .ref-value {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
            color: var(--ink);
            overflow-wrap: anywhere;
        }

        .track-box {
            justify-items: center;
            align-content: center;
            gap: 6px;
        }

        .qr-box {
            width: 92px;
            height: 92px;
            display: grid;
            place-items: center;
            padding: 5px;
            box-shadow: inset 0 0 0 1px rgba(17, 17, 17, 0.18);
        }

        .qr-box img {
            width: 100%;
            height: auto;
        }

        .track-title {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-align: center;
        }

        .track-subtitle {
            margin-top: 2px;
            font-size: 9px;
            letter-spacing: 0.04em;
            text-align: center;
            color: var(--ink-soft);
            overflow-wrap: anywhere;
        }

        @media print {
            body {
                min-height: auto;
                padding: 0;
                background: #ffffff;
            }

            .actions {
                display: none;
            }

            .label {
                width: 620px;
                min-height: 756px;
            }
        }

        @media (max-width: 520px) {
            .topbar,
            .hero,
            .care-grid,
            .brand-card,
            .barcode-card {
                grid-template-columns: 1fr;
            }

            .meta-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .spec-grid {
                grid-template-columns: auto 1fr;
            }

            .refs-grid {
                grid-template-columns: 1fr;
            }

            .topbar > * + *,
            .hero > * + *,
            .care-item,
            .barcode-card > * + * {
                border-left: 0;
            }

            .topbar > * + *,
            .hero > * + *,
            .barcode-card > * + * {
                border-top: var(--panel-rule);
            }

            .care-item + .care-item {
                border-top: var(--panel-rule);
            }

            .brand-name {
                font-size: 20px;
            }
        }
    </style>
</head>
<body class="{{ $isPdf ? 'pdf' : 'web' }}">
@unless ($isPdf)
    <div class="actions">
        <a href="{{ $downloadUrl }}">Print</a>
    </div>
@endunless

<div class="label">
    <div class="shell">
        <section class="topbar">
            <div class="card brand-card">
                @if ($label['assets']['logo'])
                    <img src="{{ $label['assets']['logo'] }}" alt="LoomCraft logo" class="brand-logo">
                @endif
                <div class="brand-name">{{ $label['brand_name'] }}</div>
            </div>

            <div class="card meta-card">
                <div class="meta-grid">
                    <div class="meta-item">
                        <div class="meta-label">Order No</div>
                        <div class="meta-value">{{ $label['order_number'] }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Tracking No</div>
                        <div class="meta-value">{{ $label['tracking_number'] }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Invoice No</div>
                        <div class="meta-value">{{ $label['invoice_number'] }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Order Date</div>
                        <div class="meta-value">{{ $label['order_date'] }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Courier</div>
                        <div class="meta-value">{{ $label['carrier'] }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Service</div>
                        <div class="meta-value">{{ $label['service_level'] }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="hero">
            <div class="card address-card">
                <div class="address-block ship-to">
                    <p class="section-label">Ship To</p>
                    <h2 class="recipient">{{ mb_strtoupper($label['ship_to']['name']) }}</h2>
                    <p class="address">
                        @foreach ($label['ship_to']['lines'] as $line)
                            {{ $line }}@if (! $loop->last)
                                <br>
                            @endif
                        @endforeach
                    </p>
                    @if ($label['ship_to']['phone'])
                        <div class="micro-row">
                            <div class="micro-line">{{ $label['ship_to']['phone'] }}</div>
                        </div>
                    @endif
                </div>
                <div class="address-block return-to">
                    <p class="section-label">Return To</p>
                    <p class="address">
                        {{ $label['return_to']['name'] }}<br>
                        @foreach ($label['return_to']['lines'] as $line)
                            {{ $line }}@if (! $loop->last)
                                <br>
                            @endif
                        @endforeach
                    </p>
                </div>
            </div>
        </section>

        <section>
            <div class="card specs-card">
                <p class="section-label">Parcel</p>
                <h3 class="product-title">{{ $label['product']['name'] }}</h3>
                <dl class="spec-grid">
                    <dt>Packages</dt>
                    <dd>{{ $label['parcel']['package_count'] }}</dd>
                    <dt>AWB</dt>
                    <dd>{{ $label['tracking_number'] }}</dd>
                    <dt>Weight</dt>
                    <dd>{{ $label['parcel']['weight'] }}</dd>
                    <dt>Styles</dt>
                    <dd>{{ $label['parcel']['styles'] }}</dd>
                    <dt>Materials</dt>
                    <dd>{{ $label['parcel']['materials'] }}</dd>
                    <dt>Dims</dt>
                    <dd>{{ $label['parcel']['dimensions'] }}</dd>
                    <dt>Sizes</dt>
                    <dd>{{ $label['parcel']['sizes'] }}</dd>
                    <dt>Qty</dt>
                    <dd>{{ str_pad((string) $label['parcel']['item_count'], 2, '0', STR_PAD_LEFT) }} Piece</dd>
                </dl>
            </div>

            <div class="card care-card">
                <p class="section-label">Handling Notes</p>
                <div class="care-grid">
                    <div class="care-item">
                        @if ($label['assets']['fragile'])
                            <img src="{{ $label['assets']['fragile'] }}" alt="Fragile" class="care-icon">
                        @endif
                        <div class="care-title">Fragile</div>
                    </div>
                    <div class="care-item">
                        @if ($label['assets']['hand_made'])
                            <img src="{{ $label['assets']['hand_made'] }}" alt="Hand made" class="care-icon">
                        @endif
                        <div class="care-title">Hand Made</div>
                    </div>
                    <div class="care-item">
                        @if ($label['assets']['handle_with_care'])
                            <img src="{{ $label['assets']['handle_with_care'] }}" alt="Handle with care" class="care-icon">
                        @endif
                        <div class="care-title">Handle With Care</div>
                    </div>
                    <div class="care-item">
                        @if ($label['assets']['keep_dry'])
                            <img src="{{ $label['assets']['keep_dry'] }}" alt="Keep dry" class="care-icon">
                        @endif
                        <div class="care-title">Keep Dry</div>
                    </div>
                    <div class="care-item">
                        @if ($label['assets']['recycle'])
                            <img src="{{ $label['assets']['recycle'] }}" alt="Recycle" class="care-icon">
                        @endif
                        <div class="care-title">Recycle</div>
                    </div>
                    <div class="origin-card">
                        @if ($label['assets']['made_in_sri_lanka'])
                            <img src="{{ $label['assets']['made_in_sri_lanka'] }}" alt="Made in Sri Lanka" class="origin-seal">
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="footer">
            <div class="card barcode-card">
                <div>
                    <div class="ref-block">
                        <div class="ref-label">Order No</div>
                        <img src="{{ $label['codes']['order_barcode'] }}" alt="Order barcode" class="barcode">
                        <div class="ref-value">{{ $label['order_number'] }}</div>
                    </div>
                    <div class="ref-block">
                        <div class="ref-label">Invoice No</div>
                        <img src="{{ $label['codes']['invoice_barcode'] }}" alt="Invoice barcode" class="barcode">
                        <div class="ref-value">{{ $label['invoice_number'] }}</div>
                    </div>
                </div>
                <div class="track-box">
                    <div class="qr-box">
                        <img src="{{ $label['codes']['tracking_qr'] }}" alt="Tracking QR code">
                    </div>
                    <div class="track-title">Track</div>
                    <div class="track-subtitle">{{ $label['tracking_number'] }}</div>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>
