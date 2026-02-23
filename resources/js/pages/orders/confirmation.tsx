import { Head, Link, usePage } from '@inertiajs/react';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { formatMoney } from '@/lib/currency';
import { index as ordersIndex } from '@/routes/orders';
import type { SharedData } from '@/types';

type OrderItem = {
    id: number;
    product_name: string;
    vendor_name: string;
    quantity: number;
    unit_price: string;
    line_total: string;
};

type OrderAddress = {
    type: 'shipping' | 'billing';
    full_name: string;
    line1: string;
    line2: string | null;
    city: string;
    region: string | null;
    postal_code: string | null;
    country_code: string;
    phone: string | null;
};

type OrderSummary = {
    id: number;
    status: string;
    currency: string;
    subtotal: string;
    commission_total: string;
    total: string;
    shipping_responsibility: string;
    placed_at: string | null;
    payment_method: string;
    payment_status: string;
    items: OrderItem[];
    addresses: OrderAddress[];
};

type OrderConfirmationProps = {
    order: OrderSummary;
    canRegister?: boolean;
};

const addressLabel = (type: OrderAddress['type']) =>
    type === 'shipping' ? 'Shipping address' : 'Billing address';

export default function OrderConfirmation({
    order,
    canRegister = true,
}: OrderConfirmationProps) {
    const { auth } = usePage<SharedData>().props;
    const shipping = order.addresses.find((address) => address.type === 'shipping');
    const billing = order.addresses.find((address) => address.type === 'billing');

    return (
        <>
            <Head title={`Order #${order.id} — LoomCraft`}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="space-y-6">
                            <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Order confirmed
                            </div>
                            <div>
                                <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                    Order #{order.id} secured
                                </h1>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">
                                    Status: {order.status} • Payment {order.payment_status}
                                </p>
                                {order.placed_at && (
                                    <p className="mt-1 text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Placed {order.placed_at}
                                    </p>
                                )}
                            </div>

                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Items
                                </p>
                                <div className="mt-4 space-y-4">
                                    {order.items.map((item) => (
                                        <div key={item.id} className="space-y-1">
                                            <p className="text-base font-semibold">
                                                {item.product_name}
                                            </p>
                                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                {item.vendor_name} • {item.quantity} ×{' '}
                                                {formatMoney(item.unit_price, order.currency)}
                                            </p>
                                            <p className="text-sm text-(--welcome-strong)">
                                                Line total{' '}
                                                {formatMoney(item.line_total, order.currency)}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                {shipping && (
                                    <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            {addressLabel(shipping.type)}
                                        </p>
                                        <p className="mt-3 text-sm font-semibold">
                                            {shipping.full_name}
                                        </p>
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {shipping.line1}
                                            {shipping.line2 ? `, ${shipping.line2}` : ''}
                                        </p>
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {shipping.city}
                                            {shipping.region ? `, ${shipping.region}` : ''}{' '}
                                            {shipping.postal_code ?? ''}
                                        </p>
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {shipping.country_code}
                                        </p>
                                    </div>
                                )}
                                {billing && (
                                    <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            {addressLabel(billing.type)}
                                        </p>
                                        <p className="mt-3 text-sm font-semibold">
                                            {billing.full_name}
                                        </p>
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {billing.line1}
                                            {billing.line2 ? `, ${billing.line2}` : ''}
                                        </p>
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {billing.city}
                                            {billing.region ? `, ${billing.region}` : ''}{' '}
                                            {billing.postal_code ?? ''}
                                        </p>
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {billing.country_code}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                        <aside className="rounded-[32px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Summary
                            </p>
                            <div className="mt-4 space-y-3 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Subtotal
                                    </span>
                                    <span>
                                        {formatMoney(order.subtotal, order.currency)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Commission
                                    </span>
                                    <span>
                                        {formatMoney(order.commission_total, order.currency)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Total
                                    </span>
                                    <span className="text-base font-semibold">
                                        {formatMoney(order.total, order.currency)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Payment
                                    </span>
                                    <span>{order.payment_method}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Shipping
                                    </span>
                                    <span>{order.shipping_responsibility}</span>
                                </div>
                            </div>
                            {auth.user && (
                                <Link
                                    href={ordersIndex()}
                                    className="mt-6 inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                >
                                    View order history
                                </Link>
                            )}
                            {!auth.user && (
                                <p className="mt-6 text-xs text-(--welcome-body-text)">
                                    Keep your order number handy. A confirmation email is
                                    on its way.
                                </p>
                            )}
                        </aside>
                    </section>
            </PublicSiteLayout>
        </>
    );
}
