<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title }} | LoomCraft</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600,700" rel="stylesheet" />
        <style>
            :root {
                color-scheme: light;
                --welcome-bg: #f5ecdf;
                --welcome-bg-strong: #efe1cb;
                --welcome-surface-1: rgba(255, 251, 244, 0.96);
                --welcome-surface-2: rgba(255, 248, 238, 0.88);
                --welcome-surface-3: rgba(255, 250, 243, 0.78);
                --welcome-strong: #352014;
                --welcome-body: rgba(53, 32, 20, 0.82);
                --welcome-muted: rgba(92, 62, 39, 0.66);
                --welcome-border: rgba(115, 77, 41, 0.18);
                --welcome-border-soft: rgba(115, 77, 41, 0.12);
                --welcome-border-strong: rgba(115, 77, 41, 0.3);
                --welcome-shadow: rgba(71, 43, 21, 0.18);
                --welcome-shadow-strong: rgba(71, 43, 21, 0.24);
                --welcome-accent: #9d5f2c;
                --welcome-accent-strong: #6c3f1a;
                --welcome-on-strong: #fffaf4;
                --welcome-strong-hover: #4a2e1c;
                --welcome-danger: #9f3a2c;
                --welcome-danger-soft: rgba(159, 58, 44, 0.12);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background:
                    radial-gradient(circle at top left, rgba(255, 255, 255, 0.78), transparent 34%),
                    radial-gradient(circle at bottom right, rgba(157, 95, 44, 0.08), transparent 36%),
                    linear-gradient(180deg, var(--welcome-bg) 0%, var(--welcome-bg-strong) 100%);
                color: var(--welcome-strong);
                font-family: 'Work Sans', sans-serif;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            .shell {
                position: relative;
                min-height: 100vh;
                overflow: hidden;
            }

            .shell::before,
            .shell::after {
                content: '';
                position: absolute;
                border-radius: 999px;
                opacity: 0.55;
                pointer-events: none;
            }

            .shell::before {
                top: -9rem;
                right: -8rem;
                width: 21rem;
                height: 21rem;
                background: radial-gradient(circle, rgba(157, 95, 44, 0.16), transparent 70%);
            }

            .shell::after {
                bottom: -11rem;
                left: -7rem;
                width: 23rem;
                height: 23rem;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.72), transparent 72%);
            }

            .frame {
                position: relative;
                z-index: 1;
                max-width: 1160px;
                margin: 0 auto;
                min-height: 100vh;
                padding: 18px 24px 48px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .brand {
                align-self: flex-start;
                margin-bottom: 26px;
                line-height: 0;
            }

            .brand img {
                width: min(100%, 320px);
                height: auto;
                display: block;
            }

            .grid {
                display: grid;
                gap: 28px;
                align-items: start;
            }

            .hero {
                padding-right: 0;
            }

            .panel {
                position: relative;
                border: 1px solid var(--welcome-border);
                border-radius: 36px;
                padding: 32px;
                background: var(--welcome-surface-1);
                box-shadow: 0 30px 80px -45px var(--welcome-shadow);
            }

            .eyebrow,
            .meta-label {
                letter-spacing: 0.3em;
                text-transform: uppercase;
                font-size: 0.72rem;
                color: var(--welcome-muted);
            }

            .status {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 14px;
                margin-bottom: 22px;
            }

            .status-code {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 88px;
                padding: 10px 18px;
                border-radius: 999px;
                background: rgba(157, 95, 44, 0.12);
                color: var(--welcome-accent-strong);
                font-weight: 700;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                font-size: 0.82rem;
            }

            h1 {
                margin: 0;
                max-width: 14ch;
                font-family: 'Playfair Display', serif;
                font-size: clamp(2.25rem, 5vw, 3rem);
                font-weight: 500;
                line-height: 1.12;
                letter-spacing: -0.02em;
            }

            .lead {
                max-width: 32rem;
                margin-top: 14px;
                color: var(--welcome-body);
                font-size: 0.98rem;
                line-height: 1.7;
            }

            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 50px;
                padding: 0 22px;
                border-radius: 999px;
                border: 1px solid var(--welcome-border-strong);
                font-size: 0.75rem;
                font-weight: 500;
                letter-spacing: 0.28em;
                text-transform: uppercase;
                transition: transform 160ms ease, background 160ms ease, color 160ms ease, border-color 160ms ease;
            }

            .button:hover {
                transform: translateY(-1px);
            }

            .button-primary {
                background: var(--welcome-strong);
                color: var(--welcome-on-strong);
                border-color: var(--welcome-strong);
            }

            .button-primary:hover {
                background: var(--welcome-strong-hover);
                border-color: var(--welcome-strong-hover);
            }

            .button-secondary {
                background: transparent;
                color: var(--welcome-strong);
            }

            .button-secondary:hover {
                background: rgba(53, 32, 20, 0.06);
            }

            .panel-title {
                margin: 0;
                font-family: 'Playfair Display', serif;
                font-size: 1.55rem;
                font-weight: 500;
                line-height: 1.18;
            }

            .panel-copy {
                margin-top: 12px;
                color: var(--welcome-body);
                line-height: 1.7;
                font-size: 0.95rem;
            }

            .details {
                display: grid;
                gap: 12px;
                margin-top: 18px;
            }

            .detail {
                padding: 14px 16px;
                border-radius: 24px;
                border: 1px solid var(--welcome-border-soft);
                background: var(--welcome-surface-3);
            }

            .detail strong {
                display: block;
                margin-top: 6px;
                color: var(--welcome-strong);
                font-size: 0.95rem;
                font-weight: 400;
                line-height: 1.55;
            }

            .notice {
                margin-top: 22px;
                padding: 16px 18px;
                border-radius: 22px;
                border: 1px solid rgba(159, 58, 44, 0.16);
                background: var(--welcome-danger-soft);
                color: var(--welcome-danger);
                font-size: 0.92rem;
                line-height: 1.6;
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 20px;
            }

            @media (min-width: 980px) {
                .grid {
                    grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
                }

                .frame {
                    padding-top: 16px;
                    padding-bottom: 60px;
                }
            }

        </style>
    </head>
    <body>
        <main class="shell">
            <div class="frame">
                <div class="brand">
                    <img src="{{ asset('brand/logo.png') }}" alt="LoomCraft">
                </div>

                <div class="grid">
                    <section class="hero">
                        <div class="status">
                            <span class="status-code">{{ $code }}</span>
                            <span class="eyebrow">{{ $eyebrow }}</span>
                        </div>

                        <h1>{{ $title }}</h1>

                        <p class="lead">{{ $message }}</p>
                    </section>

                    <aside class="panel">
                        <p class="meta-label">Patron Access</p>
                        <h2 class="panel-title">{{ $panelTitle }}</h2>
                        <p class="panel-copy">{{ $panelCopy }}</p>

                        <div class="details">
                            @foreach ($tips as $tip)
                                <div class="detail">
                                    <span class="meta-label">{{ $tip['label'] }}</span>
                                    <strong>{{ $tip['value'] }}</strong>
                                </div>
                            @endforeach
                        </div>

                        @isset($notice)
                            <div class="notice">{{ $notice }}</div>
                        @endisset

                        <div class="actions">
                            <a href="/" class="button button-primary">Return Home</a>
                            <a href="javascript:history.back()" class="button button-secondary">Go Back</a>
                        </div>
                    </aside>
                </div>
            </div>
        </main>
    </body>
</html>
