import { Head, usePage } from '@inertiajs/react';
import { show as vendorOrderShow } from '@/routes/vendor/orders';
import AppLayout from '@/layouts/app-layout';
import { formatMoney } from '@/lib/currency';
import type { BreadcrumbItem } from '@/types';
import { index as vendorOrdersIndex } from '@/routes/vendor/orders';
import { Link } from '@inertiajs/react';

type VendorOrder = {
    id: number;
    public_id: string | null;
    status: string;
    currency: string;
    total: string;
    item_count: number;
    vendor_item_count: number;
    placed_at: string | null;
    payment_method: string;
    payment_status: string;
};

type VendorOrdersProps = {
    orders: VendorOrder[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: vendorOrdersIndex().url,
    },
];

export default function VendorOrdersIndex() {
    const { orders } = usePage<VendorOrdersProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Vendor Orders" />
            <div className="flex h-full min-w-0 flex-1 flex-col gap-4 overflow-x-hidden rounded-xl p-4">
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Vendor Orders
                        </p>
                        <h2 className="text-2xl font-semibold text-foreground">
                            Fulfill your LoomCraft orders
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Review every order that includes work from your studio.
                        </p>
                    </div>
                </div>

                {orders.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-sidebar-border/70 bg-background p-6 text-sm text-muted-foreground dark:border-sidebar-border">
                        No orders have been assigned yet.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {orders.map((order) => (
                            <Link
                                key={order.id}
                                href={vendorOrderShow(order.id)}
                                className="rounded-xl border border-sidebar-border/70 bg-background p-6 text-sm text-foreground shadow-xs transition hover:border-foreground/50 dark:border-sidebar-border"
                            >
                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div className="min-w-0">
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            {order.public_id ?? `Order #${order.id}`}
                                        </p>
                                        <p className="text-lg font-semibold text-foreground">
                                            {formatMoney(order.total, order.currency)}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {order.vendor_item_count} of {order.item_count} items belong to your studio
                                        </p>
                                    </div>
                                    <div className="space-y-2 md:text-right">
                                        <p className="text-sm text-muted-foreground">
                                            {order.payment_method} • {order.payment_status}
                                        </p>
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            Status {order.status}
                                        </p>
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            {order.placed_at ?? 'Pending'}
                                        </p>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
