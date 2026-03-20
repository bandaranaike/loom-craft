import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    updateOffline as vendorOrderUpdateOffline,
    updateStatus as vendorOrderUpdateStatus,
} from '@/actions/App/Http/Controllers/Vendor/OrderController';
import AppLayout from '@/layouts/app-layout';
import InputError from '@/components/input-error';
import { formatMoney } from '@/lib/currency';
import { show as vendorOrdersShow, index as vendorOrdersIndex } from '@/routes/vendor/orders';
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
    is_vendor_owned: boolean;
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

type VendorOrderSummary = {
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
    can_manage_offline: boolean;
    can_mark_shipped: boolean;
};

type VendorOrderShowProps = {
    order: VendorOrderSummary;
};

const paymentStatusLabel = (status: string) =>
    status === 'paid' ? 'Payment success' : 'Payment failed';

export default function VendorOrderShow() {
    const { order } = usePage<VendorOrderShowProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Orders', href: vendorOrdersIndex().url },
        { title: order.public_id ?? `Order #${order.id}`, href: vendorOrdersShow(order.id).url },
    ];

    const paymentForm = useForm({
        payment_status: order.payment_status === 'failed' ? 'failed' : 'paid',
    });
    const shipForm = useForm({
        order_status: 'shipped',
    });

    const shipping = order.addresses.find((address) => address.type === 'shipping');
    const billing = order.addresses.find((address) => address.type === 'billing');
    const proofIsImage = order.payment_proof?.mime_type.startsWith('image/') ?? false;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Vendor Order ${order.public_id ?? `#${order.id}`}`} />
            <div className="flex h-full min-w-0 flex-1 flex-col gap-6 overflow-x-hidden rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="min-w-0 space-y-2">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Vendor fulfillment view
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                                {order.public_id ?? `Order #${order.id}`}
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                {order.customer_name ?? 'Guest customer'} • {order.customer_email ?? 'No email'}
                            </p>
                        </div>
                        <div className="min-w-0 md:text-right">
                            <p className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                {formatMoney(order.total, order.currency)}
                            </p>
                            <p className="text-sm text-(--welcome-body-text)">
                                {order.payment_method} • {order.payment_status} • {order.status}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid min-w-0 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="min-w-0 space-y-6">
                        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Order items
                            </p>
                            <p className="mt-2 text-sm text-(--welcome-body-text)">
                                Your products stay vivid. Other vendors&apos; products remain visible with muted styling for context.
                            </p>
                            <div className="mt-4 space-y-4">
                                {order.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className={
                                            item.is_vendor_owned
                                                ? 'rounded-[20px] border border-amber-300 bg-amber-50 p-4 shadow-[0_20px_40px_-35px_rgba(146,64,14,0.6)]'
                                                : 'rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) p-4 opacity-65 grayscale'
                                        }
                                    >
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

                        {order.can_mark_shipped && (
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    shipForm.patch(vendorOrderUpdateStatus(order.id).url, {
                                        preserveScroll: true,
                                    });
                                }}
                                className="rounded-[28px] border border-amber-200 bg-amber-50 p-6"
                            >
                                <p className="text-xs uppercase tracking-[0.3em] text-amber-800">
                                    Shipping update
                                </p>
                                <p className="mt-3 text-sm text-amber-900">
                                    Mark this order as shipped once your studio&apos;s fulfillment is ready to leave.
                                </p>
                                <InputError message={shipForm.errors.order_status} />
                                <button
                                    type="submit"
                                    disabled={shipForm.processing}
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-full border border-amber-900 px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-amber-900 transition hover:bg-amber-900 hover:text-amber-50 disabled:opacity-70"
                                >
                                    {shipForm.processing ? 'Saving...' : 'Mark as shipped'}
                                </button>
                            </form>
                        )}

                        {order.can_manage_offline && (
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    paymentForm.patch(vendorOrderUpdateOffline(order.id).url, {
                                        preserveScroll: true,
                                    });
                                }}
                                className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6"
                            >
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Offline payment review
                                </p>
                                <div className="mt-4 space-y-4">
                                    <div className="space-y-2">
                                        <label className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Payment status
                                        </label>
                                        <select
                                            value={paymentForm.data.payment_status}
                                            onChange={(event) => paymentForm.setData('payment_status', event.target.value)}
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-3 text-sm"
                                        >
                                            {order.payment_status_options.map((status) => (
                                                <option key={status} value={status}>
                                                    {paymentStatusLabel(status)}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError message={paymentForm.errors.payment_status} />
                                    </div>
                                    <button
                                        type="submit"
                                        disabled={paymentForm.processing}
                                        className="inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:opacity-70"
                                    >
                                        {paymentForm.processing ? 'Saving...' : 'Save payment review'}
                                    </button>
                                </div>
                            </form>
                        )}

                        {order.payment_method === 'bank_transfer' && (
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Final bank transfer slip
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
                                        No bank transfer slip uploaded yet.
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
