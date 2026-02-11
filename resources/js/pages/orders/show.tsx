import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { index as ordersIndex } from '@/routes/orders';
import type { BreadcrumbItem } from '@/types';

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

type OrderShowProps = {
    order: OrderSummary;
};

export default function OrderShow() {
    const { order } = usePage<OrderShowProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Orders', href: ordersIndex().url },
        { title: `Order #${order.id}`, href: '#' },
    ];

    const shipping = order.addresses.find((address) => address.type === 'shipping');
    const billing = order.addresses.find((address) => address.type === 'billing');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Order #${order.id}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="space-y-2">
                            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                Order #{order.id}
                            </p>
                            <h2 className="text-2xl font-semibold text-foreground">
                                {order.total} {order.currency}
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Status {order.status} • Payment {order.payment_status}
                            </p>
                        </div>
                        <Link
                            href={ordersIndex()}
                            className="text-xs uppercase tracking-[0.3em] text-muted-foreground underline"
                        >
                            Back to orders
                        </Link>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <div className="space-y-4">
                        <div className="rounded-xl border border-sidebar-border/70 bg-background p-6 dark:border-sidebar-border">
                            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                Items
                            </p>
                            <div className="mt-4 space-y-3">
                                {order.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="rounded-lg border border-sidebar-border/50 bg-sidebar/10 p-4 text-sm dark:border-sidebar-border"
                                    >
                                        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p className="font-semibold text-foreground">
                                                    {item.product_name}
                                                </p>
                                                <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                                    {item.vendor_name}
                                                </p>
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {item.quantity} × {item.unit_price}
                                            </div>
                                            <div className="text-base font-semibold text-foreground">
                                                {item.line_total} {order.currency}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                        <div className="grid gap-4 md:grid-cols-2">
                            {shipping && (
                                <div className="rounded-xl border border-sidebar-border/70 bg-background p-6 text-sm dark:border-sidebar-border">
                                    <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                        Shipping address
                                    </p>
                                    <p className="mt-3 font-semibold text-foreground">
                                        {shipping.full_name}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {shipping.line1}
                                        {shipping.line2 ? `, ${shipping.line2}` : ''}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {shipping.city}
                                        {shipping.region ? `, ${shipping.region}` : ''}{' '}
                                        {shipping.postal_code ?? ''}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {shipping.country_code}
                                    </p>
                                </div>
                            )}
                            {billing && (
                                <div className="rounded-xl border border-sidebar-border/70 bg-background p-6 text-sm dark:border-sidebar-border">
                                    <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                        Billing address
                                    </p>
                                    <p className="mt-3 font-semibold text-foreground">
                                        {billing.full_name}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {billing.line1}
                                        {billing.line2 ? `, ${billing.line2}` : ''}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {billing.city}
                                        {billing.region ? `, ${billing.region}` : ''}{' '}
                                        {billing.postal_code ?? ''}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {billing.country_code}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                    <aside className="rounded-xl border border-sidebar-border/70 bg-background p-6 text-sm dark:border-sidebar-border">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Summary
                        </p>
                        <div className="mt-4 space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Subtotal</span>
                                <span className="text-foreground">
                                    {order.subtotal} {order.currency}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Commission</span>
                                <span className="text-foreground">
                                    {order.commission_total} {order.currency}
                                </span>
                            </div>
                            <div className="flex items-center justify-between font-semibold">
                                <span className="text-foreground">Total</span>
                                <span className="text-foreground">
                                    {order.total} {order.currency}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Payment</span>
                                <span className="text-foreground">
                                    {order.payment_method} ({order.payment_status})
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Shipping</span>
                                <span className="text-foreground">
                                    {order.shipping_responsibility}
                                </span>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </AppLayout>
    );
}
