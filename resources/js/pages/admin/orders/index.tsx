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
            <Head title="Order Management">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Order Management
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                            Monitor marketplace orders
                        </h2>
                        <p className="text-sm text-(--welcome-body-text)">
                            Verify payments, track disputes, and coordinate shipping responsibility.
                        </p>
                    </div>
                </div>

                {orders.length === 0 ? (
                    <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-6 text-sm text-(--welcome-muted-text)">
                        No orders placed yet.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {orders.map((order) => (
                            <div
                                key={order.id}
                                className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 text-sm text-(--welcome-strong) shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]"
                            >
                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Order #{order.id}
                                        </p>
                                        <p className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                            {order.total} {order.currency}
                                        </p>
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {order.item_count} items • {order.status}
                                        </p>
                                    </div>
                                    <div className="space-y-2 text-right">
                                        <p className="text-sm text-(--welcome-body-text)">
                                            {order.payment_method} • {order.payment_status}
                                        </p>
                                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
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
