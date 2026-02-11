import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { index as vendorOrdersIndex } from '@/routes/vendor/orders';

type VendorOrderItem = {
    order_id: number;
    order_item_id: number;
    status: string;
    currency: string;
    product_name: string;
    quantity: number;
    line_total: string;
    shipping_responsibility: string;
    placed_at: string | null;
};

type VendorOrdersProps = {
    items: VendorOrderItem[];
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: vendorOrdersIndex().url,
    },
];

export default function VendorOrdersIndex() {
    const { items } = usePage<VendorOrdersProps>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Vendor Orders" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Vendor Orders
                        </p>
                        <h2 className="text-2xl font-semibold text-foreground">
                            Fulfill your LoomCraft commissions
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Review order line items assigned to your studio.
                        </p>
                    </div>
                </div>

                {items.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-sidebar-border/70 bg-background p-6 text-sm text-muted-foreground dark:border-sidebar-border">
                        No orders have been assigned yet.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {items.map((item) => (
                            <div
                                key={item.order_item_id}
                                className="rounded-xl border border-sidebar-border/70 bg-background p-6 text-sm text-foreground shadow-xs dark:border-sidebar-border"
                            >
                                <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                    <div>
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            Order #{item.order_id}
                                        </p>
                                        <p className="text-lg font-semibold text-foreground">
                                            {item.product_name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {item.quantity} pcs • {item.status}
                                        </p>
                                    </div>
                                    <div className="space-y-2 text-right">
                                        <p className="text-sm text-muted-foreground">
                                            {item.line_total} {item.currency}
                                        </p>
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            Shipping {item.shipping_responsibility}
                                        </p>
                                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                            {item.placed_at ?? 'Pending'}
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
