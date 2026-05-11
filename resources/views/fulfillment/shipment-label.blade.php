<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $label['document_title'] }}</title>
    <style>
        :root {
            --ink: #111111;
            --ink-soft: #4d4d4d;
            --rule: rgba(17, 17, 17, 0.22);
            --page-bg: #efefef;
            --panel: #ffffff;
            --panel-rule: 1px solid var(--rule);
            --radius: 18px;
            --space: 12px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 10px;
            background: var(--page-bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        .label {
            width: min(100%, 620px);
            margin: 0 auto;
            overflow: hidden;
            border: var(--panel-rule);
            border-radius: var(--radius);
            background: var(--panel);
        }

        .topbar,
        .address-card,
        .barcode-card,
        .refs-grid,
        .care-grid {
            display: grid;
        }

        .topbar {
            grid-template-columns: minmax(110px, 0.34fr) minmax(0, 1.66fr);
        }

        .topbar > * + *,
        .address-block + .address-block,
        .barcode-card > * + * {
            border-left: var(--panel-rule);
        }

        .brand-card,
        .meta-card,
        .address-block,
        .specs-card,
        .care-card,
        .barcode-panel,
        .track-box {
            padding: var(--space);
        }

        .brand-logo {
            width: 64px;
            height: auto;
            filter: grayscale(1) brightness(0);
        }

        .brand-name,
        .meta-label,
        .section-label,
        .ref-label,
        .track-title,
        .care-title,
        .actions a,
        .actions button {
            text-transform: uppercase;
        }

        .brand-name {
            margin-top: 4px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px 10px;
        }

        .meta-item {
            border-bottom: var(--panel-rule);
            padding-bottom: 7px;
        }

        .meta-label,
        .section-label,
        .ref-label {
            color: var(--ink-soft);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.12em;
        }

        .meta-value {
            margin-top: 4px;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .address-card {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            border-top: var(--panel-rule);
        }

        .recipient,
        .product-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.2;
        }

        .address,
        .micro-line {
            color: #1c1c1c;
            font-size: 14px;
            line-height: 1.5;
        }

        .address {
            margin: 8px 0 0;
        }

        .specs-card,
        .care-card,
        .barcode-card {
            border-top: var(--panel-rule);
        }

        .spec-grid {
            display: grid;
            grid-template-columns: auto 1fr auto 1fr;
            gap: 7px 12px;
            margin-top: 10px;
            font-size: 13px;
        }

        .spec-grid dt {
            color: var(--ink-soft);
            font-weight: 800;
        }

        .spec-grid dd {
            margin: 0;
            overflow-wrap: anywhere;
        }

        .care-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 6px;
        }

        .care-item {
            min-height: 54px;
            display: grid;
            place-items: center;
            border: var(--panel-rule);
            padding: 6px;
            text-align: center;
        }

        .care-icon {
            font-size: 24px;
            font-weight: 800;
        }

        .care-title {
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.06em;
        }

        .barcode-card {
            grid-template-columns: minmax(0, 1fr) 140px;
        }

        .barcode,
        .barcode.small {
            width: 100%;
            background: repeating-linear-gradient(90deg, #000 0 3px, transparent 3px 7px, #000 7px 11px, transparent 11px 16px);
        }

        .barcode {
            height: 58px;
        }

        .barcode.small {
            height: 34px;
        }

        .barcode-no {
            margin-top: 8px;
            color: var(--ink);
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.12em;
            overflow-wrap: anywhere;
        }

        .refs-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 12px;
        }

        .ref-value {
            margin-top: 4px;
            font-size: 12px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .track-box {
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 8px;
        }

        .qr-box {
            width: 94px;
            height: 94px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 4px;
            border: var(--panel-rule);
            padding: 8px;
        }

        .qr-box span:nth-child(odd) {
            background: #111;
        }

        .track-title {
            color: var(--ink-soft);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.1em;
        }

        .track-subtitle {
            font-size: 9px;
            overflow-wrap: anywhere;
            text-align: center;
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
            border: 1px solid #111;
            border-radius: 999px;
            background: #fff;
            color: #111;
            cursor: pointer;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            padding: 9px 14px;
            text-decoration: none;
        }

        @media (max-width: 560px) {
            .topbar,
            .address-card,
            .barcode-card,
            .refs-grid,
            .care-grid {
                grid-template-columns: 1fr;
            }

            .topbar > * + *,
            .address-block + .address-block,
            .barcode-card > * + * {
                border-left: 0;
                border-top: var(--panel-rule);
            }

            .spec-grid {
                grid-template-columns: auto 1fr;
            }
        }

        @media print {
            body {
                min-height: auto;
                padding: 0;
                background: #fff;
            }

            .actions {
                display: none;
            }

            .label {
                width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
<div class="actions">
    <button type="button" onclick="window.print()">Print</button>
</div>

<main class="label">
    <section class="topbar">
        <div class="brand-card">
            <img src="{{ asset('brand/logo-dark.png') }}" alt="LoomCraft logo" class="brand-logo">
            <div class="brand-name">LoomCraft</div>
        </div>

        <div class="meta-card">
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

    <section class="address-card">
        <div class="address-block">
            <p class="section-label">Ship To</p>
            <h1 class="recipient">{{ $label['ship_to']['name'] }}</h1>
            <p class="address">
                @foreach ($label['ship_to']['lines'] as $line)
                    {{ $line }}@if (! $loop->last)<br>@endif
                @endforeach
            </p>
            @if ($label['ship_to']['phone'])
                <div class="micro-line">{{ $label['ship_to']['phone'] }}</div>
            @endif
        </div>
        <div class="address-block">
            <p class="section-label">Return To</p>
            <h2 class="recipient">{{ $label['return_to']['name'] }}</h2>
            <p class="address">
                @foreach ($label['return_to']['lines'] as $line)
                    {{ $line }}@if (! $loop->last)<br>@endif
                @endforeach
            </p>
        </div>
    </section>

    <section class="specs-card">
        <p class="section-label">Parcel</p>
        <h2 class="product-title">{{ $label['product']['name'] }}</h2>
        <dl class="spec-grid">
            <dt>Shipment</dt>
            <dd>{{ $label['shipment_number'] }}</dd>
            <dt>AWB</dt>
            <dd>{{ $label['tracking_number'] }}</dd>
            <dt>Product Code</dt>
            <dd>{{ $label['product']['code'] ?? 'Pending' }}</dd>
            <dt>Weight</dt>
            <dd>{{ $label['parcel']['weight'] }}</dd>
            <dt>Vendor</dt>
            <dd>{{ $label['product']['vendor'] ?? 'LoomCraft' }}</dd>
            <dt>Dims</dt>
            <dd>{{ $label['parcel']['dimensions'] }}</dd>
            <dt>Product Size</dt>
            <dd>{{ $label['product']['dimensions'] ?? 'Pending' }}</dd>
            <dt>Qty</dt>
            <dd>{{ $label['product']['quantity'] }} piece(s), {{ $label['product']['item_count'] }} line(s)</dd>
        </dl>
    </section>

    <section class="care-card">
        <p class="section-label">Handling Notes</p>
        <div class="care-grid">
            <div class="care-item"><div class="care-icon">!</div><div class="care-title">Fragile</div></div>
            <div class="care-item"><div class="care-icon">H</div><div class="care-title">Hand Made</div></div>
            <div class="care-item"><div class="care-icon">^</div><div class="care-title">This Side Up</div></div>
            <div class="care-item"><div class="care-icon">D</div><div class="care-title">Keep Dry</div></div>
            <div class="care-item"><div class="care-icon">LK</div><div class="care-title">Made In Sri Lanka</div></div>
        </div>
    </section>

    <section class="barcode-card">
        <div class="barcode-panel">
            <p class="section-label">Shipment</p>
            <div class="barcode" aria-hidden="true"></div>
            <div class="barcode-no">{{ $label['tracking_number'] }}</div>
            <div class="refs-grid">
                <div>
                    <div class="ref-label">Order No</div>
                    <div class="barcode small" aria-hidden="true"></div>
                    <div class="ref-value">{{ $label['order_number'] }}</div>
                </div>
                <div>
                    <div class="ref-label">Invoice No</div>
                    <div class="barcode small" aria-hidden="true"></div>
                    <div class="ref-value">{{ $label['invoice_number'] }}</div>
                </div>
            </div>
        </div>
        <div class="track-box">
            <div class="qr-box" aria-hidden="true">
                @for ($index = 0; $index < 25; $index++)
                    <span></span>
                @endfor
            </div>
            <div class="track-title">Track</div>
            <div class="track-subtitle">{{ $label['tracking_number'] }}</div>
        </div>
    </section>
</main>
</body>
</html>
