import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { index as adminOrdersIndex } from '@/routes/admin/orders';

type AdminOrderItem = {
    id: number;
    status: string;
    currency: string;
    total: string;
    item_count: number;
    placed_at: string | null;
    payment_method: string;
    payment_status: string;
};

type AdminOrdersProps = {
    orders: AdminOrderItem[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: adminOrdersIndex().url,
    },
];

export default function AdminOrdersIndex() {
    const { orders } = usePage<AdminOrdersProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Order Management" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Order Management
                        </p>
                        <h2 className="text-2xl font-semibold text-foreground">
                            Monitor marketplace orders
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Verify payments, track disputes, and coordinate shipping responsibility.
                        </p>
                    </div>
                </div>

                {orders.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-sidebar-border/70 bg-background p-6 text-sm text-muted-foreground dark:border-sidebar-border">
                        No orders placed yet.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {orders.map((order) => (
                            <div
                                key={order.id}
                                className="rounded-xl border border-sidebar-border/70 bg-background p-6 text-sm text-foreground shadow-xs dark:border-sidebar-border"
                            >
                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            Order #{order.id}
                                        </p>
                                        <p className="text-lg font-semibold text-foreground">
                                            {order.total} {order.currency}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {order.item_count} items • {order.status}
                                        </p>
                                    </div>
                                    <div className="space-y-2 text-right">
                                        <p className="text-sm text-muted-foreground">
                                            {order.payment_method} • {order.payment_status}
                                        </p>
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            {order.placed_at ?? 'Pending'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
