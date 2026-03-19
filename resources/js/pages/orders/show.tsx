import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { store as storeBankTransferSlip } from '@/actions/App/Http/Controllers/OrderBankTransferSlipController';
import InputError from '@/components/input-error';
import OrderProgress from '@/components/order-progress';
import { usePublicOrderReference } from '@/hooks/use-public-order-reference';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { formatMoney } from '@/lib/currency';
import { index as ordersIndex } from '@/routes/orders';
import { show as vendorShow } from '@/routes/vendors';
import type { SharedData } from '@/types';

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

type OrderSummary = {
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
    payment_amount: string | null;
    payment_currency: string | null;
    payment_original_amount: string | null;
    payment_original_currency: string | null;
    payment_proof: {
        url: string;
        original_name: string;
        mime_type: string;
        uploaded_at: string | null;
    } | null;
    progress: {
        is_cancelled: boolean;
        summary: {
            title: string;
            description: string;
        } | null;
        steps: {
            key: string;
            label: string;
            state: 'complete' | 'current' | 'upcoming';
        }[];
    } | null;
    can_upload_payment_proof: boolean;
    items: OrderItem[];
    addresses: OrderAddress[];
};

type OrderShowProps = {
    order: OrderSummary;
};

export default function OrderShow() {
    const { order, auth } = usePage<OrderShowProps & SharedData>().props;
    const slipForm = useForm<{ slip: File | null }>({
        slip: null,
    });

    const shipping = order.addresses.find((address) => address.type === 'shipping');
    const billing = order.addresses.find((address) => address.type === 'billing');
    const proofIsImage = order.payment_proof?.mime_type.startsWith('image/') ?? false;
    const {
        copied,
        copyPublicOrderReference,
        publicOrderReference,
        truncatedPublicOrderReference,
    } = usePublicOrderReference({
        id: order.id,
        publicId: order.public_id,
    });
    const paymentRecordedInDifferentCurrency =
        order.payment_currency !== null &&
        order.payment_amount !== null &&
        (order.payment_currency !== order.currency || order.payment_amount !== order.total);

    return (
        <>
            <Head title={`${order.public_id ?? `Order ${order.id}`} — LoomCraft`} />
            <PublicSiteLayout canRegister={!auth.user}>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-8 px-6 pb-16 pt-4 lg:grid-cols-[1.1fr_0.9fr]">
                    <div className="space-y-6">
                        <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                            <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Public order reference
                                    </p>
                                    <h1 className="font-['Playfair_Display',serif] text-xl text-(--welcome-strong) md:text-2xl">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                void copyPublicOrderReference()
                                            }
                                            className="max-w-full cursor-pointer truncate text-left underline decoration-(--welcome-border) underline-offset-4"
                                            title={`Copy ${publicOrderReference}`}
                                        >
                                            {truncatedPublicOrderReference}
                                        </button>
                                    </h1>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        {order.placed_at ?? 'Pending placement'} • {order.payment_method} • payment {order.payment_status}
                                    </p>
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        {copied
                                            ? 'Reference copied'
                                            : 'Tap the reference to copy'}
                                    </p>
                                </div>
                                <div className="space-y-3 text-left md:text-right">
                                    <p className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                                        {formatMoney(order.total, order.currency)}
                                    </p>
                                    {auth.user && (
                                        <Link
                                            href={ordersIndex()}
                                            className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text) underline"
                                        >
                                            Back to orders
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>

                        <OrderProgress progress={order.progress} />

                        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Items
                            </p>
                            <div className="mt-4 space-y-3">
                                {order.items.map((item) => (
                                    <div
                                        key={item.id}
                                        className="rounded-[22px] border border-(--welcome-border) bg-(--welcome-surface-1) p-4 text-sm"
                                    >
                                        <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p className="font-semibold text-(--welcome-strong)">
                                                    {item.product_name}
                                                </p>
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
                                            <div className="text-base font-semibold text-(--welcome-strong)">
                                                {formatMoney(item.line_total, order.currency)}
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            {shipping && (
                                <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 text-sm">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Shipping address
                                    </p>
                                    <p className="mt-3 font-semibold text-(--welcome-strong)">
                                        {shipping.full_name}
                                    </p>
                                    <p className="text-(--welcome-body-text)">
                                        {shipping.line1}
                                        {shipping.line2 ? `, ${shipping.line2}` : ''}
                                    </p>
                                    <p className="text-(--welcome-body-text)">
                                        {shipping.city}
                                        {shipping.region ? `, ${shipping.region}` : ''} {shipping.postal_code ?? ''}
                                    </p>
                                    <p className="text-(--welcome-body-text)">{shipping.country_code}</p>
                                </div>
                            )}
                            {billing && (
                                <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 text-sm">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Billing address
                                    </p>
                                    <p className="mt-3 font-semibold text-(--welcome-strong)">
                                        {billing.full_name}
                                    </p>
                                    <p className="text-(--welcome-body-text)">
                                        {billing.line1}
                                        {billing.line2 ? `, ${billing.line2}` : ''}
                                    </p>
                                    <p className="text-(--welcome-body-text)">
                                        {billing.city}
                                        {billing.region ? `, ${billing.region}` : ''} {billing.postal_code ?? ''}
                                    </p>
                                    <p className="text-(--welcome-body-text)">{billing.country_code}</p>
                                </div>
                            )}
                        </div>

                        {order.payment_method === 'bank_transfer' && (
                            <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 text-sm">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Final bank transfer slip
                                </p>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">
                                    Upload the final transfer slip for {order.public_id ?? `order #${order.id}`}. Keep the
                                    amount and reference visible in the document if possible.
                                </p>
                                {order.payment_proof && (
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
                                                className="rounded-[20px] border border-(--welcome-border) object-cover"
                                            />
                                        )}
                                    </div>
                                )}
                                {order.can_upload_payment_proof ? (
                                    <form
                                        onSubmit={(event) => {
                                            event.preventDefault();
                                            slipForm.post(storeBankTransferSlip(order.public_id ?? `${order.id}`).url, {
                                                forceFormData: true,
                                                preserveScroll: true,
                                            });
                                        }}
                                        className="mt-4 space-y-3"
                                    >
                                        <input
                                            type="file"
                                            accept=".pdf,image/*"
                                            onChange={(event) =>
                                                slipForm.setData('slip', event.target.files?.[0] ?? null)
                                            }
                                            className="block w-full text-sm text-(--welcome-body-text)"
                                        />
                                        <InputError message={slipForm.errors.slip} />
                                        <button
                                            type="submit"
                                            disabled={slipForm.processing}
                                            className="inline-flex items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:opacity-70"
                                        >
                                            {slipForm.processing ? 'Uploading...' : order.payment_proof ? 'Replace slip' : 'Upload slip'}
                                        </button>
                                    </form>
                                ) : (
                                    <p className="mt-4 text-sm text-(--welcome-body-text)">
                                        Proof upload is available only to the order owner or the original guest checkout session.
                                    </p>
                                )}
                            </div>
                        )}
                    </div>

                    <aside className="rounded-[32px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 text-sm shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Summary
                        </p>
                        <div className="mt-4 space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-(--welcome-body-text)">Order reference</span>
                                <button
                                    type="button"
                                    onClick={() =>
                                        void copyPublicOrderReference()
                                    }
                                    className="max-w-48 cursor-pointer truncate text-right text-(--welcome-strong) underline decoration-(--welcome-border) underline-offset-4"
                                    title={`Copy ${publicOrderReference}`}
                                >
                                    {truncatedPublicOrderReference}
                                </button>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-(--welcome-body-text)">Order subtotal</span>
                                <span className="text-(--welcome-strong)">
                                    {formatMoney(order.subtotal, order.currency)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-(--welcome-body-text)">Shipping</span>
                                <span className="text-(--welcome-strong)">{order.shipping_responsibility}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-(--welcome-body-text)">Order status</span>
                                <span className="text-(--welcome-strong)">{order.status}</span>
                            </div>
                            <div className="flex items-center justify-between font-semibold">
                                <span className="text-(--welcome-strong)">Order total</span>
                                <span className="text-(--welcome-strong)">
                                    {formatMoney(order.total, order.currency)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-(--welcome-body-text)">Payment</span>
                                <span className="text-(--welcome-strong)">
                                    {order.payment_method} ({order.payment_status})
                                </span>
                            </div>
                            {order.payment_amount && order.payment_currency && (
                                <div className="flex items-center justify-between">
                                    <span className="text-(--welcome-body-text)">Payment recorded</span>
                                    <span className="text-(--welcome-strong)">
                                        {formatMoney(order.payment_amount, order.payment_currency)}
                                    </span>
                                </div>
                            )}
                            {paymentRecordedInDifferentCurrency &&
                                order.payment_original_amount &&
                                order.payment_original_currency && (
                                    <p className="rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-3 text-xs text-(--welcome-body-text)">
                                        This payment was processed in {order.payment_currency}. The original order total remains{' '}
                                        {formatMoney(order.payment_original_amount, order.payment_original_currency)}.
                                    </p>
                                )}
                        </div>
                    </aside>
                </section>
            </PublicSiteLayout>
        </>
    );
}
