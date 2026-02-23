import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import InputError from '@/components/input-error';
import { formatMoney } from '@/lib/currency';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { show as cartShow } from '@/routes/cart';
import { create as checkoutPayPalCreate } from '@/routes/checkout/paypal';
import { store as checkoutStore } from '@/routes/checkout';
import type { SharedData } from '@/types';

type CartItem = {
    id: number;
    name: string;
    vendor_name: string;
    quantity: number;
    unit_price: string;
    line_total: string;
};

type CartSummary = {
    cart_id: number;
    currency: string;
    items: CartItem[];
    item_count: number;
    subtotal: string;
};

type CheckoutPageProps = {
    cart: CartSummary;
    currency: string;
    payment_methods: string[];
    shipping_responsibilities: string[];
    guest_name?: string | null;
    guest_email?: string | null;
    canRegister?: boolean;
    paypal_configured?: boolean;
};

const defaultPaymentLabel = (method: string) => {
    switch (method) {
        case 'paypal':
            return 'PayPal';
        case 'stripe':
            return 'Stripe (card)';
        case 'bank_transfer':
            return 'Bank transfer';
        case 'cod':
            return 'Cash on delivery';
        default:
            return method;
    }
};

export default function CheckoutPage({
    cart,
    currency,
    payment_methods,
    shipping_responsibilities,
    guest_name,
    guest_email,
    canRegister = true,
    paypal_configured = false,
}: CheckoutPageProps) {
    const { auth } = usePage<SharedData>().props;
    const [mirrorBilling, setMirrorBilling] = useState(true);
    const [paypalProcessing, setPaypalProcessing] = useState(false);

    const form = useForm({
        guest_name: guest_name ?? '',
        guest_email: guest_email ?? '',
        currency,
        shipping_responsibility: shipping_responsibilities[0] ?? 'vendor',
        payment_method: payment_methods[0] ?? 'stripe',
        shipping_full_name: auth.user?.name ?? '',
        shipping_line1: '',
        shipping_line2: '',
        shipping_city: '',
        shipping_region: '',
        shipping_postal_code: '',
        shipping_country_code: 'US',
        shipping_phone: '',
        billing_full_name: auth.user?.name ?? '',
        billing_line1: '',
        billing_line2: '',
        billing_city: '',
        billing_region: '',
        billing_postal_code: '',
        billing_country_code: 'US',
        billing_phone: '',
    });

    const normalizedPayload = () => {
        if (!mirrorBilling) {
            return form.data;
        }

        return {
            ...form.data,
            billing_full_name: form.data.shipping_full_name,
            billing_line1: form.data.shipping_line1,
            billing_line2: form.data.shipping_line2,
            billing_city: form.data.shipping_city,
            billing_region: form.data.shipping_region,
            billing_postal_code: form.data.shipping_postal_code,
            billing_country_code: form.data.shipping_country_code,
            billing_phone: form.data.shipping_phone,
        };
    };

    const csrfToken = () =>
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    const submitPayPalOrder = async (): Promise<void> => {
        setPaypalProcessing(true);
        form.clearErrors();

        try {
            const response = await fetch(checkoutPayPalCreate().url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(normalizedPayload()),
            });

            if (response.status === 422) {
                const payload = (await response.json()) as {
                    errors?: Record<string, string | string[]>;
                };

                Object.entries(payload.errors ?? {}).forEach(
                    ([field, value]) => {
                        form.setError(
                            field as keyof typeof form.errors,
                            Array.isArray(value) ? value[0] : value,
                        );
                    },
                );

                return;
            }

            if (!response.ok) {
                throw new Error('PayPal order creation failed.');
            }

            const payload = (await response.json()) as {
                approve_url?: string;
            };

            if (!payload.approve_url) {
                throw new Error('PayPal approval URL is missing.');
            }

            window.location.assign(payload.approve_url);
        } finally {
            setPaypalProcessing(false);
        }
    };

    const handleSubmit = (event: FormEvent) => {
        event.preventDefault();

        if (form.data.payment_method === 'paypal') {
            void submitPayPalOrder();

            return;
        }

        form.transform(() => normalizedPayload());

        form.post(checkoutStore().url, {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="Checkout — LoomCraft">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pt-4 pb-16 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="space-y-6">
                            <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Checkout Atelier
                            </div>
                            <div>
                                <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                    Confirm your heritage order
                                </h1>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">
                                    Provide shipping and billing details to
                                    secure your curated pieces.
                                </p>
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-6">
                                {!auth.user && (
                                    <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Guest information
                                        </p>
                                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                                            <div className="space-y-2">
                                                <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Full name
                                                </label>
                                                <input
                                                    type="text"
                                                    name="guest_name"
                                                    value={form.data.guest_name}
                                                    onChange={(event) =>
                                                        form.setData(
                                                            'guest_name',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                                />
                                                <InputError
                                                    message={
                                                        form.errors.guest_name
                                                    }
                                                />
                                            </div>
                                            <div className="space-y-2">
                                                <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Email address
                                                </label>
                                                <input
                                                    type="email"
                                                    name="guest_email"
                                                    value={
                                                        form.data.guest_email
                                                    }
                                                    onChange={(event) =>
                                                        form.setData(
                                                            'guest_email',
                                                            event.target.value,
                                                        )
                                                    }
                                                    className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                                />
                                                <InputError
                                                    message={
                                                        form.errors.guest_email
                                                    }
                                                />
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Shipping details
                                    </p>
                                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Recipient name
                                            </label>
                                            <input
                                                type="text"
                                                name="shipping_full_name"
                                                value={
                                                    form.data.shipping_full_name
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'shipping_full_name',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            />
                                            <InputError
                                                message={
                                                    form.errors
                                                        .shipping_full_name
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Phone (optional)
                                            </label>
                                            <input
                                                type="text"
                                                name="shipping_phone"
                                                value={form.data.shipping_phone}
                                                onChange={(event) =>
                                                    form.setData(
                                                        'shipping_phone',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            />
                                        </div>
                                    </div>
                                    <div className="mt-4 space-y-2">
                                        <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Address line 1
                                        </label>
                                        <input
                                            type="text"
                                            name="shipping_line1"
                                            value={form.data.shipping_line1}
                                            onChange={(event) =>
                                                form.setData(
                                                    'shipping_line1',
                                                    event.target.value,
                                                )
                                            }
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                        />
                                        <InputError
                                            message={form.errors.shipping_line1}
                                        />
                                    </div>
                                    <div className="mt-4 space-y-2">
                                        <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Address line 2 (optional)
                                        </label>
                                        <input
                                            type="text"
                                            name="shipping_line2"
                                            value={form.data.shipping_line2}
                                            onChange={(event) =>
                                                form.setData(
                                                    'shipping_line2',
                                                    event.target.value,
                                                )
                                            }
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                        />
                                    </div>
                                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                City
                                            </label>
                                            <input
                                                type="text"
                                                name="shipping_city"
                                                value={form.data.shipping_city}
                                                onChange={(event) =>
                                                    form.setData(
                                                        'shipping_city',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            />
                                            <InputError
                                                message={
                                                    form.errors.shipping_city
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Region
                                            </label>
                                            <input
                                                type="text"
                                                name="shipping_region"
                                                value={
                                                    form.data.shipping_region
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'shipping_region',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Postal code
                                            </label>
                                            <input
                                                type="text"
                                                name="shipping_postal_code"
                                                value={
                                                    form.data
                                                        .shipping_postal_code
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'shipping_postal_code',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            />
                                        </div>
                                    </div>
                                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Country code
                                            </label>
                                            <input
                                                type="text"
                                                name="shipping_country_code"
                                                value={
                                                    form.data
                                                        .shipping_country_code
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'shipping_country_code',
                                                        event.target.value.toUpperCase(),
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            />
                                            <InputError
                                                message={
                                                    form.errors
                                                        .shipping_country_code
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Shipping responsibility
                                            </label>
                                            <select
                                                name="shipping_responsibility"
                                                value={
                                                    form.data
                                                        .shipping_responsibility
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'shipping_responsibility',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            >
                                                {shipping_responsibilities.map(
                                                    (option) => (
                                                        <option
                                                            key={option}
                                                            value={option}
                                                        >
                                                            {option === 'vendor'
                                                                ? 'Vendor handled'
                                                                : 'Platform handled'}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                                    <div className="flex flex-wrap items-center justify-between gap-4">
                                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Billing details
                                        </p>
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setMirrorBilling(
                                                    (prev) => !prev,
                                                )
                                            }
                                            className="rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-on-strong)"
                                        >
                                            {mirrorBilling
                                                ? 'Use distinct billing'
                                                : 'Mirror shipping'}
                                        </button>
                                    </div>
                                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Billing name
                                            </label>
                                            <input
                                                type="text"
                                                name="billing_full_name"
                                                value={
                                                    form.data.billing_full_name
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_full_name',
                                                        event.target.value,
                                                    )
                                                }
                                                disabled={mirrorBilling}
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                            />
                                            <InputError
                                                message={
                                                    form.errors
                                                        .billing_full_name
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Billing phone
                                            </label>
                                            <input
                                                type="text"
                                                name="billing_phone"
                                                value={form.data.billing_phone}
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_phone',
                                                        event.target.value,
                                                    )
                                                }
                                                disabled={mirrorBilling}
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                            />
                                        </div>
                                    </div>
                                    <div className="mt-4 space-y-2">
                                        <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Address line 1
                                        </label>
                                        <input
                                            type="text"
                                            name="billing_line1"
                                            value={form.data.billing_line1}
                                            onChange={(event) =>
                                                form.setData(
                                                    'billing_line1',
                                                    event.target.value,
                                                )
                                            }
                                            disabled={mirrorBilling}
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                        />
                                        <InputError
                                            message={form.errors.billing_line1}
                                        />
                                    </div>
                                    <div className="mt-4 space-y-2">
                                        <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Address line 2 (optional)
                                        </label>
                                        <input
                                            type="text"
                                            name="billing_line2"
                                            value={form.data.billing_line2}
                                            onChange={(event) =>
                                                form.setData(
                                                    'billing_line2',
                                                    event.target.value,
                                                )
                                            }
                                            disabled={mirrorBilling}
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                        />
                                    </div>
                                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                City
                                            </label>
                                            <input
                                                type="text"
                                                name="billing_city"
                                                value={form.data.billing_city}
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_city',
                                                        event.target.value,
                                                    )
                                                }
                                                disabled={mirrorBilling}
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                            />
                                            <InputError
                                                message={
                                                    form.errors.billing_city
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Region
                                            </label>
                                            <input
                                                type="text"
                                                name="billing_region"
                                                value={form.data.billing_region}
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_region',
                                                        event.target.value,
                                                    )
                                                }
                                                disabled={mirrorBilling}
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Postal code
                                            </label>
                                            <input
                                                type="text"
                                                name="billing_postal_code"
                                                value={
                                                    form.data
                                                        .billing_postal_code
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_postal_code',
                                                        event.target.value,
                                                    )
                                                }
                                                disabled={mirrorBilling}
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                            />
                                        </div>
                                    </div>
                                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Country code
                                            </label>
                                            <input
                                                type="text"
                                                name="billing_country_code"
                                                value={
                                                    form.data
                                                        .billing_country_code
                                                }
                                                onChange={(event) =>
                                                    form.setData(
                                                        'billing_country_code',
                                                        event.target.value.toUpperCase(),
                                                    )
                                                }
                                                disabled={mirrorBilling}
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none disabled:opacity-70"
                                            />
                                            <InputError
                                                message={
                                                    form.errors
                                                        .billing_country_code
                                                }
                                            />
                                        </div>
                                        <div className="space-y-2">
                                            <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Payment method
                                            </label>
                                            <select
                                                name="payment_method"
                                                value={form.data.payment_method}
                                                onChange={(event) =>
                                                    form.setData(
                                                        'payment_method',
                                                        event.target.value,
                                                    )
                                                }
                                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            >
                                                {payment_methods.map(
                                                    (method) => (
                                                        <option
                                                            key={method}
                                                            value={method}
                                                        >
                                                            {defaultPaymentLabel(
                                                                method,
                                                            )}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <input
                                    type="hidden"
                                    name="currency"
                                    value={form.data.currency}
                                />

                                <button
                                    type="submit"
                                    disabled={
                                        form.processing ||
                                        paypalProcessing ||
                                        (form.data.payment_method ===
                                            'paypal' &&
                                            !paypal_configured)
                                    }
                                    className="inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {paypalProcessing
                                        ? 'Redirecting to PayPal...'
                                        : form.processing
                                          ? 'Securing order...'
                                          : form.data.payment_method ===
                                              'paypal'
                                            ? 'Continue to PayPal'
                                            : 'Place order'}
                                </button>
                                {form.data.payment_method === 'paypal' &&
                                !paypal_configured ? (
                                    <p className="text-xs text-(--welcome-danger)">
                                        PayPal is not configured yet. Add
                                        PayPal keys in `.env` and reload.
                                    </p>
                                ) : null}
                            </form>
                        </div>

                        <aside className="rounded-[32px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <div className="flex items-center justify-between">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Order Summary
                                </p>
                                <Link
                                    href={cartShow()}
                                    className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase underline"
                                >
                                    Edit cart
                                </Link>
                            </div>
                            <div className="mt-4 space-y-4">
                                {cart.items.map((item) => (
                                    <div key={item.id} className="space-y-1">
                                        <p className="text-sm font-semibold">
                                            {item.name}
                                        </p>
                                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            {item.vendor_name} • {item.quantity}{' '}
                                            × {formatMoney(item.unit_price, cart.currency)}
                                        </p>
                                        <p className="text-sm text-(--welcome-strong)">
                                            Line total{' '}
                                            {formatMoney(item.line_total, cart.currency)}
                                        </p>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-6 space-y-3 border-t border-(--welcome-border-soft) pt-4 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Items
                                    </span>
                                    <span>{cart.item_count}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Subtotal
                                    </span>
                                    <span>
                                        {formatMoney(cart.subtotal, cart.currency)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Currency
                                    </span>
                                    <span>{cart.currency}</span>
                                </div>
                            </div>
                            <p className="mt-4 text-xs text-(--welcome-body-text)">
                                Shipping fees are arranged directly with the
                                responsible vendor or the LoomCraft team after
                                confirmation.
                            </p>
                        </aside>
                    </section>
            </PublicSiteLayout>
        </>
    );
}
