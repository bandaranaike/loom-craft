import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { index as ordersIndex, show as orderShow } from '@/routes/orders';
import type { BreadcrumbItem } from '@/types';

type OrderListItem = {
    id: number;
    status: string;
    currency: string;
    total: string;
    item_count: number;
    placed_at: string | null;
};

type OrdersIndexProps = {
    orders: OrderListItem[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: ordersIndex().url,
    },
];

export default function OrdersIndex() {
    const { orders } = usePage<OrdersIndexProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Order History" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Order History
                        </p>
                        <h2 className="text-2xl font-semibold text-foreground">
                            Track your LoomCraft orders
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Review status updates and revisit order details.
                        </p>
                    </div>
                </div>

                {orders.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-sidebar-border/70 bg-background p-6 text-sm text-muted-foreground dark:border-sidebar-border">
                        No orders yet.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {orders.map((order) => (
                            <Link
                                key={order.id}
                                href={orderShow(order.id)}
                                className="rounded-xl border border-sidebar-border/70 bg-background p-6 text-sm text-foreground shadow-xs transition hover:border-foreground/50 dark:border-sidebar-border"
                            >
                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div className="space-y-2">
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            Order #{order.id}
                                        </p>
                                        <p className="text-lg font-semibold">
                                            {order.total} {order.currency}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {order.item_count} items • {order.status}
                                        </p>
                                    </div>
                                    <div className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                        {order.placed_at ?? 'Pending'}
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
