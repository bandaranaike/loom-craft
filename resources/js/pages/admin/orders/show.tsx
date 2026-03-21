import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { index as adminOrdersIndex } from '@/actions/App/Http/Controllers/Admin/OrderController';
import {
    destroy as adminOrderDestroy,
    updateOffline as adminOrderUpdateOffline,
    updateStatus as adminOrderUpdateStatus,
} from '@/actions/App/Http/Controllers/Admin/OrderController';
import AppLayout from '@/layouts/app-layout';
import InputError from '@/components/input-error';
import { formatMoney } from '@/lib/currency';
import { show as vendorShow } from '@/routes/vendors';
import type { BreadcrumbItem } from '@/types';

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

type OrderProof = {
    url: string;
    original_name: string;
    mime_type: string;
    uploaded_at: string | null;
};

type AdminOrderSummary = {
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
    customer_name: string | null;
    customer_email: string | null;
    items: OrderItem[];
    addresses: OrderAddress[];
    payment_proof: OrderProof | null;
    payment_status_options: string[];
    order_status_options: string[];
    can_manage_offline: boolean;
    can_delete: boolean;
};

type AdminOrderShowProps = {
    order: AdminOrderSummary;
};

const paymentStatusLabel = (status: string) =>
    status === 'paid' ? 'Payment success' : 'Payment failed';

const orderStatusLabel = (status: string) => {
    switch (status) {
        case 'confirmed':
            return 'Confirmed';
        case 'delivered':
            return 'Delivered';
        case 'shipped':
            return 'Shipped';
        case 'cancelled':
            return 'Cancelled';
        case 'paid':
            return 'Paid';
        default:
            return 'Pending';
    }
};

const paymentProofHeading = (paymentMethod: string): string => {
    if (paymentMethod === 'bank_transfer') {
        return 'Final bank transfer slip';
    }

    if (paymentMethod === 'cod') {
        return 'Uploaded proof of payment';
    }

    return 'Payment proof';
};

const paymentProofEmptyState = (paymentMethod: string): string => {
    if (paymentMethod === 'bank_transfer') {
        return 'No bank transfer slip uploaded yet.';
    }

    if (paymentMethod === 'cod') {
        return 'No proof of payment uploaded yet.';
    }

    return 'No payment proof uploaded yet.';
};

export default function AdminOrderShow() {
    const { order } = usePage<AdminOrderShowProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Orders', href: adminOrdersIndex().url },
        { title: `Order #${order.id}`, href: '#' },
    ];

    const form = useForm({
        payment_status: order.payment_status === 'failed' ? 'failed' : 'paid',
    });
    const statusForm = useForm({
        order_status: order.status,
    });
    const deleteForm = useForm({});

    const shipping = order.addresses.find((address) => address.type === 'shipping');
    const billing = order.addresses.find((address) => address.type === 'billing');
    const proofIsImage = order.payment_proof?.mime_type.startsWith('image/') ?? false;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Admin Order #${order.id}`} />
            <div className="flex h-full min-w-0 flex-1 flex-col gap-6 overflow-x-hidden rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="min-w-0 space-y-2">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Admin review
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                                Order #{order.id}
                            </h2>
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Public reference {order.public_id ?? 'Pending'}
                            </p>
                            <p className="text-sm text-(--welcome-body-text)">
                                {order.customer_name ?? 'Guest customer'} • {order.customer_email ?? 'No email'}
                            </p>
                        </div>
                        <div className="min-w-0 md:text-right">
                            <p className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                {formatMoney(order.total, order.currency)}
                            </p>
                            <p className="text-sm text-(--welcome-body-text)">
                                {order.payment_method} • {order.payment_status}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid min-w-0 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="min-w-0 space-y-6">
                        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Items
                            </p>
                            <div className="mt-4 space-y-4">
                                {order.items.map((item) => (
                                    <div key={item.id} className="rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) p-4">
                                        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                            <div className="min-w-0">
                                                <p className="font-semibold">{item.product_name}</p>
                                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    {item.vendor_slug ? (
                                                        <Link href={vendorShow(item.vendor_slug)}>
                                                            {item.vendor_name}
                                                        </Link>
                                                    ) : (
                                                        item.vendor_name
                                                    )}
                                                </p>
                                            </div>
                                            <div className="text-sm text-(--welcome-body-text)">
                                                {item.quantity} × {formatMoney(item.unit_price, order.currency)}
                                            </div>
                                            <div className="font-semibold">
                                                {formatMoney(item.line_total, order.currency)}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            {shipping && (
                                <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Shipping address
                                    </p>
                                    <p className="mt-3 text-sm font-semibold">{shipping.full_name}</p>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        {shipping.line1}
                                        {shipping.line2 ? `, ${shipping.line2}` : ''}
                                    </p>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        {shipping.city}
                                        {shipping.region ? `, ${shipping.region}` : ''} {shipping.postal_code ?? ''}
                                    </p>
                                    <p className="text-sm text-(--welcome-body-text)">{shipping.country_code}</p>
                                </div>
                            )}
                            {billing && (
                                <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Billing address
                                    </p>
                                    <p className="mt-3 text-sm font-semibold">{billing.full_name}</p>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        {billing.line1}
                                        {billing.line2 ? `, ${billing.line2}` : ''}
                                    </p>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        {billing.city}
                                        {billing.region ? `, ${billing.region}` : ''} {billing.postal_code ?? ''}
                                    </p>
                                    <p className="text-sm text-(--welcome-body-text)">{billing.country_code}</p>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="min-w-0 space-y-6">
                        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Order summary
                            </p>
                            <div className="mt-4 space-y-3 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="text-(--welcome-body-text)">Subtotal</span>
                                    <span>{formatMoney(order.subtotal, order.currency)}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-(--welcome-body-text)">Shipping</span>
                                    <span>{order.shipping_responsibility}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="text-(--welcome-body-text)">Placed</span>
                                    <span>{order.placed_at ?? 'Pending'}</span>
                                </div>
                                <div className="flex items-center justify-between font-semibold">
                                    <span>Total</span>
                                    <span>{formatMoney(order.total, order.currency)}</span>
                                </div>
                            </div>
                        </div>

                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                statusForm.patch(adminOrderUpdateStatus(order.id).url, {
                                    preserveScroll: true,
                                });
                            }}
                            className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6"
                        >
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Order status
                            </p>
                            <div className="mt-4 space-y-4">
                                <div className="space-y-2">
                                    <label className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Order status
                                    </label>
                                    <select
                                        value={statusForm.data.order_status}
                                        onChange={(event) => statusForm.setData('order_status', event.target.value)}
                                        className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-3 text-sm"
                                    >
                                        {order.order_status_options.map((status) => (
                                            <option key={status} value={status}>
                                                {orderStatusLabel(status)}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={statusForm.errors.order_status} />
                                </div>
                                <button
                                    type="submit"
                                    disabled={statusForm.processing}
                                    className="inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:opacity-70"
                                >
                                    {statusForm.processing ? 'Saving...' : 'Save order status'}
                                </button>
                            </div>
                        </form>

                        {order.can_manage_offline && (
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    form.patch(adminOrderUpdateOffline(order.id).url, {
                                        preserveScroll: true,
                                    });
                                }}
                                className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6"
                            >
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Offline review
                                </p>
                                <div className="mt-4 space-y-4">
                                    <div className="space-y-2">
                                        <label className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Payment status
                                        </label>
                                        <select
                                            value={form.data.payment_status}
                                            onChange={(event) => form.setData('payment_status', event.target.value)}
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-3 text-sm"
                                        >
                                            {order.payment_status_options.map((status) => (
                                                <option key={status} value={status}>
                                                    {paymentStatusLabel(status)}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={form.errors.payment_status} />
                                    </div>
                                    <button
                                        type="submit"
                                        disabled={form.processing}
                                        className="inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:opacity-70"
                                    >
                                        {form.processing ? 'Saving...' : 'Save payment review'}
                                    </button>
                                </div>
                            </form>
                        )}

                        {order.can_delete && (
                            <div className="rounded-[28px] border border-rose-200 bg-rose-50 p-6">
                                <p className="text-xs uppercase tracking-[0.3em] text-rose-700">
                                    Soft delete
                                </p>
                                <p className="mt-3 text-sm text-rose-900">
                                    This removes the order from standard admin, vendor, and customer views without permanently erasing the row.
                                </p>
                                <button
                                    type="button"
                                    onClick={() => {
                                        if (! window.confirm('Soft delete this order?')) {
                                            return;
                                        }

                                        deleteForm.delete(adminOrderDestroy(order.id).url, {
                                            preserveScroll: true,
                                        });
                                    }}
                                    disabled={deleteForm.processing}
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-full border border-rose-700 px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-rose-700 transition hover:bg-rose-700 hover:text-rose-50 disabled:opacity-70"
                                >
                                    {deleteForm.processing ? 'Deleting...' : 'Soft delete order'}
                                </button>
                            </div>
                        )}

                        {order.can_manage_offline && (
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    {paymentProofHeading(order.payment_method)}
                                </p>
                                {order.payment_proof ? (
                                    <div className="mt-4 space-y-3">
                                        <a
                                            href={order.payment_proof.url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="inline-flex text-sm text-(--welcome-strong) underline"
                                        >
                                            {order.payment_proof.original_name}
                                        </a>
                                        <p className="text-xs text-(--welcome-body-text)">
                                            Uploaded {order.payment_proof.uploaded_at ?? 'recently'}
                                        </p>
                                        {proofIsImage && (
                                            <img
                                                src={order.payment_proof.url}
                                                alt={order.payment_proof.original_name}
                                                className="rounded-[18px] border border-(--welcome-border) object-cover"
                                            />
                                        )}
                                    </div>
                                ) : (
                                    <p className="mt-4 text-sm text-(--welcome-body-text)">
                                        {paymentProofEmptyState(order.payment_method)}
                                    </p>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
