import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useEffect, useEffectEvent } from 'react';
import { updateShipmentStatus as vendorOrderUpdateShipmentStatus } from '@/actions/App/Http/Controllers/Vendor/OrderController';
import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
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

type ShipmentSummary = {
    id: number;
    shipment_number: string | null;
    status: string;
    tracking_number: string | null;
    carrier: string | null;
    service_level: string | null;
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
    shipment: ShipmentSummary | null;
    payment_proof: OrderProof | null;
    payment_status_options: string[];
    shipment_status_options: string[];
    can_manage_offline: boolean;
};

type VendorOrderShowProps = {
    order: VendorOrderSummary;
};

const shipmentStatusLabel = (status: string) =>
    status
        .split('_')
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join(' ');

export default function VendorOrderShow() {
    const { order } = usePage<VendorOrderShowProps>().props;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Orders', href: vendorOrdersIndex().url },
        { title: order.public_id ?? `Order #${order.id}`, href: vendorOrdersShow(order.id).url },
    ];

    const shipForm = useForm({
        shipment_status: order.shipment_status_options[0] ?? order.shipment?.status ?? 'pending',
    });

    const shipping = order.addresses.find((address) => address.type === 'shipping');
    const billing = order.addresses.find((address) => address.type === 'billing');
    const proofIsImage = order.payment_proof?.mime_type.startsWith('image/') ?? false;

    const resetShipmentFormFromOrder = useEffectEvent(() => {
        shipForm.setData('shipment_status', order.shipment_status_options[0] ?? order.shipment?.status ?? 'pending');
        shipForm.clearErrors();
    });

    useEffect(() => {
        resetShipmentFormFromOrder();
    }, [order.shipment?.status, order.shipment_status_options]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Vendor Order ${order.public_id ?? `#${order.id}`}`} />
            <div className="flex h-full min-w-0 flex-1 flex-col gap-6 overflow-x-hidden rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div className="min-w-0 space-y-2">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Vendor fulfillment view</p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">{order.public_id ?? `Order #${order.id}`}</h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                {order.customer_name ?? 'Guest customer'} • {order.customer_email ?? 'No email'}
                            </p>
                        </div>
                        <div className="min-w-0 md:text-right">
                            <p className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">{formatMoney(order.total, order.currency)}</p>
                            <p className="text-sm text-(--welcome-body-text)">
                                {order.payment_method} • {order.payment_status} • {order.status}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid min-w-0 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="min-w-0 space-y-6">
                        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Order items</p>
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
                                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    {item.vendor_slug ? <Link href={vendorShow(item.vendor_slug)}>{item.vendor_name}</Link> : item.vendor_name}
                                                </p>
                                            </div>
                                            <div className="text-sm text-(--welcome-body-text)">
                                                {item.quantity} × {formatMoney(item.unit_price, order.currency)}
                                            </div>
                                            <div className="font-semibold">{formatMoney(item.line_total, order.currency)}</div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            {shipping && (
                                <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Shipping address</p>
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
                                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Billing address</p>
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
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Order summary</p>
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

                        {order.shipment && order.shipment_status_options.length > 0 && (
                            <form
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    shipForm.patch(
                                        vendorOrderUpdateShipmentStatus({
                                            order: order.id,
                                            shipment: order.shipment!.id,
                                        }).url,
                                        {
                                            preserveScroll: true,
                                        },
                                    );
                                }}
                                className="rounded-[28px] border border-amber-200 bg-amber-50 p-6"
                            >
                                <p className="text-xs tracking-[0.3em] text-amber-800 uppercase">Shipment workflow</p>
                                <p className="mt-3 text-sm text-amber-900">Move your shipment forward as packing work is completed.</p>
                                <div className="mt-4 space-y-2">
                                    <p className="text-sm text-amber-950">Shipment {order.shipment.shipment_number ?? `#${order.shipment.id}`}</p>
                                    <p className="text-sm text-amber-900">
                                        Current status: <span className="font-semibold">{shipmentStatusLabel(order.shipment.status)}</span>
                                    </p>
                                </div>
                                <div className="mt-4 space-y-2">
                                    <select
                                        value={shipForm.data.shipment_status}
                                        onChange={(event) => shipForm.setData('shipment_status', event.target.value)}
                                        className="w-full rounded-full border border-amber-300 bg-amber-100 px-4 py-3 text-sm text-amber-950"
                                    >
                                        {order.shipment_status_options.map((status) => (
                                            <option key={status} value={status}>
                                                {shipmentStatusLabel(status)}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={shipForm.errors.shipment_status} />
                                </div>
                                <button
                                    type="submit"
                                    disabled={shipForm.processing}
                                    className="mt-4 inline-flex w-full items-center justify-center rounded-full border border-amber-900 px-4 py-3 text-xs font-semibold tracking-[0.3em] text-amber-900 uppercase transition hover:bg-amber-900 hover:text-amber-50 disabled:opacity-70"
                                >
                                    {shipForm.processing ? 'Saving...' : 'Save shipment status'}
                                </button>
                            </form>
                        )}

                        {order.payment_method === 'bank_transfer' && (
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Final bank transfer slip</p>
                                {order.payment_proof ? (
                                    <div className="mt-4 space-y-3">
                                        <a href={order.payment_proof.url} target="_blank" rel="noreferrer" className="inline-flex text-sm text-(--welcome-strong) underline">
                                            {order.payment_proof.original_name}
                                        </a>
                                        <p className="text-xs text-(--welcome-body-text)">Uploaded {order.payment_proof.uploaded_at ?? 'recently'}</p>
                                        {proofIsImage && (
                                            <img
                                                src={order.payment_proof.url}
                                                alt={order.payment_proof.original_name}
                                                className="rounded-[18px] border border-(--welcome-border) object-cover"
                                            />
                                        )}
                                    </div>
                                ) : (
                                    <p className="mt-4 text-sm text-(--welcome-body-text)">No bank transfer slip uploaded yet.</p>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
