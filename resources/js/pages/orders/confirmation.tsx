import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { store as storeBankTransferSlip } from '@/actions/App/Http/Controllers/OrderBankTransferSlipController';
import OrderAddressCard from '@/components/order-address-card';
import OrderBankTransferSlipPanel from '@/components/order-bank-transfer-slip-panel';
import OrderProgress from '@/components/order-progress';
import OrderSummaryCard from '@/components/order-summary-card';
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

type OrderConfirmationProps = {
    order: OrderSummary;
    canRegister?: boolean;
};

export default function OrderConfirmation({
    order,
    canRegister = true,
}: OrderConfirmationProps) {
    const { auth } = usePage<SharedData>().props;
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
            <Head
                title={`${order.public_id ?? `Order ${order.id}`} — LoomCraft`}
            >
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pt-4 pb-16 lg:grid-cols-[1.2fr_0.8fr]">
                    <div className="space-y-6">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Order confirmed
                        </div>
                        <div>
                            <h1 className="font-['Playfair_Display',serif] text-2xl leading-tight md:text-3xl">
                                <button
                                    type="button"
                                    onClick={() =>
                                        void copyPublicOrderReference()
                                    }
                                    className="max-w-full cursor-pointer truncate text-left underline decoration-(--welcome-border) underline-offset-4"
                                    title={`Copy ${publicOrderReference}`}
                                >
                                    {truncatedPublicOrderReference}
                                </button>{' '}
                                secured
                            </h1>
                            <p className="mt-3 text-sm text-(--welcome-body-text)">
                                Status: {order.status} • Payment{' '}
                                {order.payment_status}
                            </p>
                            <p className="mt-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                {copied
                                    ? 'Reference copied'
                                    : 'Tap the reference to copy'}
                            </p>
                            {order.placed_at && (
                                <p className="mt-1 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Placed {order.placed_at}
                                </p>
                            )}
                        </div>

                        <OrderProgress progress={order.progress} />

                        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Items
                            </p>
                            <div className="mt-4 space-y-4">
                                {order.items.map((item) => (
                                    <div key={item.id} className="space-y-1">
                                        <p className="text-base font-semibold">
                                            {item.product_name}
                                        </p>
                                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            {item.vendor_slug ? (
                                                <Link
                                                    href={vendorShow(
                                                        item.vendor_slug,
                                                    )}
                                                >
                                                    {item.vendor_name}
                                                </Link>
                                            ) : (
                                                item.vendor_name
                                            )}{' '}
                                            • {item.quantity} ×{' '}
                                            {formatMoney(
                                                item.unit_price,
                                                order.currency,
                                            )}
                                        </p>
                                        <p className="text-sm text-(--welcome-strong)">
                                            Line total{' '}
                                            {formatMoney(
                                                item.line_total,
                                                order.currency,
                                            )}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="grid gap-4 md:grid-cols-2">
                            {shipping && (
                                <OrderAddressCard
                                    address={shipping}
                                    className="rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5"
                                />
                            )}
                            {billing && (
                                <OrderAddressCard
                                    address={billing}
                                    className="rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5"
                                />
                            )}
                        </div>

                        {order.payment_method === 'bank_transfer' && (
                            <OrderBankTransferSlipPanel
                                canUploadPaymentProof={
                                    order.can_upload_payment_proof
                                }
                                orderId={order.id}
                                orderPublicId={order.public_id}
                                paymentProof={order.payment_proof}
                                proofIsImage={proofIsImage}
                                processing={slipForm.processing}
                                slipError={slipForm.errors.slip}
                                onFileChange={(file) =>
                                    slipForm.setData('slip', file)
                                }
                                onSubmit={(event) => {
                                    event.preventDefault();
                                    slipForm.post(
                                        storeBankTransferSlip(
                                            order.public_id ?? `${order.id}`,
                                        ).url,
                                        {
                                            forceFormData: true,
                                            preserveScroll: true,
                                        },
                                    );
                                }}
                                className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6"
                                buttonClassName="inline-flex items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:opacity-70"
                                description={`Upload the final transfer slip for ${order.public_id ?? `order #${order.id}`}. This page already shows the exact order details so the proof stays tied to the correct payment.`}
                            />
                        )}
                    </div>

                    <OrderSummaryCard
                        copiedReference={publicOrderReference}
                        onCopyReference={() =>
                            void copyPublicOrderReference()
                        }
                        orderCurrency={order.currency}
                        orderStatus={order.status}
                        orderSubtotal={order.subtotal}
                        orderTotal={order.total}
                        paymentAmount={order.payment_amount}
                        paymentCurrency={order.payment_currency}
                        paymentMethod={order.payment_method}
                        paymentOriginalAmount={order.payment_original_amount}
                        paymentOriginalCurrency={order.payment_original_currency}
                        paymentRecordedInDifferentCurrency={
                            paymentRecordedInDifferentCurrency
                        }
                        paymentStatus={order.payment_status}
                        shippingResponsibility={
                            order.shipping_responsibility
                        }
                        truncatedReference={truncatedPublicOrderReference}
                        titleClassName="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                        bodyClassName="mt-4 space-y-3 text-sm"
                        labelClassName="tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                        referenceButtonClassName="max-w-48 cursor-pointer truncate text-right underline decoration-(--welcome-border) underline-offset-4"
                        footer={
                            <div className="space-y-3">
                                <Link
                                    href={auth.user ? ordersIndex() : '/'}
                                    className="inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                >
                                    {auth.user
                                        ? 'Review order history'
                                        : 'Continue browsing'}
                                </Link>
                            </div>
                        }
                    />
                </section>
            </PublicSiteLayout>
        </>
    );
}
