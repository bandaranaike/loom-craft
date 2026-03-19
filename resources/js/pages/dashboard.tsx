import { Head, Link, usePage } from '@inertiajs/react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/app-layout';
import { formatMoney } from '@/lib/currency';
import { dashboard } from '@/routes';
import { index as ordersIndex, show as ordersShow } from '@/routes/orders';
import { pending as adminVendorPending } from '@/routes/admin/vendors';
import { edit as vendorProfileEdit } from '@/routes/vendor/profile';
import { register as vendorRegister } from '@/routes/vendor';
import { show as vendorShow } from '@/routes/vendors';
import type { SharedData } from '@/types';
import type { BreadcrumbItem } from '@/types';
import { useMemo, useState } from 'react';

type Props = {
    status?: string;
    order_histories: OrderHistory[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

type OrderItem = {
    id: number;
    product_name: string;
    vendor_name: string;
    vendor_slug: string | null;
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

type OrderHistory = {
    id: number;
    public_id: string | null;
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

export default function Dashboard() {
    const { status, auth, order_histories } = usePage<SharedData & Props>().props;
    const isAdmin = auth?.user?.role === 'admin';
    const [selectedOrderId, setSelectedOrderId] = useState<number | null>(null);
    const selectedOrder = useMemo(
        () => order_histories.find((order) => order.id === selectedOrderId) ?? null,
        [order_histories, selectedOrderId],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {status && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100">
                        {status}
                    </div>
                )}
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex flex-col gap-2">
                            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                Order Library
                            </p>
                            <h2 className="text-2xl font-semibold text-foreground">
                                View your orders and history
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Click any order card to see full details in a popup.
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <Link
                                href={ordersIndex()}
                                className="inline-flex items-center justify-center rounded-full border border-foreground/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-foreground transition hover:bg-foreground hover:text-background"
                            >
                                Full Order History
                            </Link>
                            <Link
                                href={auth?.vendor ? vendorProfileEdit() : vendorRegister()}
                                className="inline-flex items-center justify-center rounded-full border border-foreground/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-foreground transition hover:bg-foreground hover:text-background"
                            >
                                {auth?.vendor ? 'Vendor Profile' : 'Become Vendor'}
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="grid gap-4 lg:grid-cols-2">
                    {order_histories.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-sidebar-border/80 p-10 text-center text-sm text-muted-foreground dark:border-sidebar-border lg:col-span-2">
                            No orders yet. Place your first LoomCraft order to build history.
                        </div>
                    ) : (
                        order_histories.map((order) => (
                            <button
                                key={order.id}
                                type="button"
                                onClick={() => setSelectedOrderId(order.id)}
                                className="flex h-full cursor-pointer flex-col gap-3 rounded-xl border border-sidebar-border/70 bg-background p-6 text-left shadow-xs transition hover:border-foreground/40 dark:border-sidebar-border"
                            >
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                        {order.public_id ?? `Order #${order.id}`}
                                    </p>
                                    <span className="rounded-full border border-sidebar-border/60 px-3 py-1 text-xs uppercase tracking-[0.2em] text-muted-foreground dark:border-sidebar-border">
                                        {order.status}
                                    </span>
                                </div>
                                <p className="text-xl font-semibold text-foreground">
                                    {formatMoney(order.total, order.currency)}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    {order.items.length} items • {order.payment_method} (
                                    {order.payment_status})
                                </p>
                                <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                    {order.placed_at ?? 'Pending'}
                                </p>
                            </button>
                        ))
                    )}
                </div>

                {isAdmin && (
                    <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/20 p-6 dark:border-sidebar-border">
                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                    Admin Review
                                </p>
                                <h2 className="text-2xl font-semibold text-foreground">
                                    Pending vendor approvals
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Review and approve artisan applications.
                                </p>
                            </div>
                            <Link
                                href={adminVendorPending()}
                                className="inline-flex items-center justify-center rounded-full border border-foreground/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-foreground transition hover:bg-foreground hover:text-background"
                            >
                                Review Vendors
                            </Link>
                        </div>
                    </div>
                )}

                <Dialog
                    open={selectedOrder !== null}
                    onOpenChange={(open) => {
                        if (!open) {
                            setSelectedOrderId(null);
                        }
                    }}
                >
                    <DialogContent className="max-h-[92vh] overflow-y-auto sm:max-w-4xl">
                        {selectedOrder && (
                            <>
                                <DialogHeader>
                                    <DialogTitle className="text-xl">
                                        {selectedOrder.public_id ?? `Order #${selectedOrder.id}`}
                                    </DialogTitle>
                                    <DialogDescription>
                                        {formatMoney(selectedOrder.total, selectedOrder.currency)} •{' '}
                                        {selectedOrder.status} • {selectedOrder.payment_method} (
                                        {selectedOrder.payment_status})
                                    </DialogDescription>
                                </DialogHeader>
                                <div className="space-y-6 text-sm">
                                    <div className="grid gap-3 rounded-xl border border-sidebar-border/70 bg-sidebar/10 p-4 dark:border-sidebar-border md:grid-cols-3">
                                        <div>
                                            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                                Placed
                                            </p>
                                            <p className="mt-1 text-foreground">
                                                {selectedOrder.placed_at ?? 'Pending'}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                                Shipping
                                            </p>
                                            <p className="mt-1 text-foreground">
                                                {selectedOrder.shipping_responsibility}
                                            </p>
                                        </div>
                                        <div>
                                            <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                                Subtotal
                                            </p>
                                            <p className="mt-1 text-foreground">
                                                {formatMoney(
                                                    selectedOrder.subtotal,
                                                    selectedOrder.currency,
                                                )}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            Items
                                        </p>
                                        {selectedOrder.items.map((item) => (
                                            <div
                                                key={item.id}
                                                className="rounded-xl border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                                            >
                                                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                    <div>
                                                        <p className="font-semibold text-foreground">
                                                            {item.product_name}
                                                        </p>
                                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                                            {item.vendor_slug ? (
                                                                <Link href={vendorShow(item.vendor_slug)}>
                                                                    {item.vendor_name}
                                                                </Link>
                                                            ) : (
                                                                item.vendor_name
                                                            )}
                                                        </p>
                                                    </div>
                                                    <p className="text-muted-foreground">
                                                        {item.quantity} ×{' '}
                                                        {formatMoney(
                                                            item.unit_price,
                                                            selectedOrder.currency,
                                                        )}
                                                    </p>
                                                    <p className="font-semibold text-foreground">
                                                        {formatMoney(
                                                            item.line_total,
                                                            selectedOrder.currency,
                                                        )}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {selectedOrder.addresses.length > 0 && (
                                        <div className="grid gap-3 md:grid-cols-2">
                                            {selectedOrder.addresses.map((address) => (
                                                <div
                                                    key={`${address.type}-${address.line1}`}
                                                    className="rounded-xl border border-sidebar-border/70 bg-background p-4 dark:border-sidebar-border"
                                                >
                                                    <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                                        {address.type} address
                                                    </p>
                                                    <p className="mt-2 font-semibold text-foreground">
                                                        {address.full_name}
                                                    </p>
                                                    <p className="text-muted-foreground">
                                                        {address.line1}
                                                        {address.line2
                                                            ? `, ${address.line2}`
                                                            : ''}
                                                    </p>
                                                    <p className="text-muted-foreground">
                                                        {address.city}
                                                        {address.region
                                                            ? `, ${address.region}`
                                                            : ''}{' '}
                                                        {address.postal_code ?? ''}
                                                    </p>
                                                    <p className="text-muted-foreground">
                                                        {address.country_code}
                                                    </p>
                                                    {address.phone && (
                                                        <p className="text-muted-foreground">
                                                            {address.phone}
                                                        </p>
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    )}

                                    <div className="flex justify-end">
                                        <Link
                                            href={ordersShow(selectedOrder.public_id ?? `${selectedOrder.id}`)}
                                            className="inline-flex items-center justify-center rounded-full border border-foreground/70 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-foreground transition hover:bg-foreground hover:text-background"
                                        >
                                            Open Full Page
                                        </Link>
                                    </div>
                                </div>
                            </>
                        )}
                    </DialogContent>
                </Dialog>
            </div>
        </AppLayout>
    );
}
