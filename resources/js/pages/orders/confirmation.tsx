import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, home, login, register } from '@/routes';
import { index as ordersIndex } from '@/routes/orders';
import type { SharedData } from '@/types';

type OrderItem = {
    id: number;
    product_name: string;
    vendor_name: string;
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
    status: string;
    currency: string;
    subtotal: string;
    commission_total: string;
    total: string;
    shipping_responsibility: string;
    placed_at: string | null;
    payment_method: string;
    payment_status: string;
    items: OrderItem[];
    addresses: OrderAddress[];
};

type OrderConfirmationProps = {
    order: OrderSummary;
    canRegister?: boolean;
};

const addressLabel = (type: OrderAddress['type']) =>
    type === 'shipping' ? 'Shipping address' : 'Billing address';

export default function OrderConfirmation({
    order,
    canRegister = true,
}: OrderConfirmationProps) {
    const { auth } = usePage<SharedData>().props;
    const shipping = order.addresses.find((address) => address.type === 'shipping');
    const billing = order.addresses.find((address) => address.type === 'billing');

    return (
        <>
            <Head title={`Order #${order.id} — LoomCraft`}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="min-h-screen bg-[#F6F1E8] text-[#2b241c]">
                <div className="relative overflow-hidden">
                    <div className="pointer-events-none absolute -left-40 top-0 h-[420px] w-[420px] rounded-full bg-[radial-gradient(circle_at_top,_#c77b45,_transparent_65%)] opacity-40" />
                    <div className="pointer-events-none absolute -right-32 top-20 h-[360px] w-[360px] rounded-full bg-[radial-gradient(circle,_#a14d2a,_transparent_68%)] opacity-30" />
                    <div className="pointer-events-none absolute bottom-0 left-1/2 h-[320px] w-[720px] -translate-x-1/2 rounded-[100%] bg-[radial-gradient(ellipse_at_center,_#e0c7a7,_transparent_70%)] opacity-60" />

                    <header className="relative z-10 mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-6 pb-6 pt-8">
                        <Link href={home()} className="flex items-center gap-3">
                            <div className="grid h-12 w-12 place-items-center rounded-full border border-[#2b241c] bg-[#f2e4d4] text-lg font-semibold tracking-[0.08em]">
                                LC
                            </div>
                            <div>
                                <p className="text-sm uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    LoomCraft
                                </p>
                                <p className="font-['Playfair_Display',serif] text-xl">
                                    Woven Heritage House
                                </p>
                            </div>
                        </Link>
                        <nav className="flex flex-wrap items-center gap-3 text-sm">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="rounded-full border border-[#2b241c] px-4 py-2 font-medium transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                >
                                    Enter Atelier
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="rounded-full border border-transparent px-4 py-2 font-medium text-[#2b241c]/70 transition hover:border-[#2b241c] hover:text-[#2b241c]"
                                    >
                                        Log in
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="rounded-full border border-[#2b241c] px-4 py-2 font-medium transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                        >
                                            Become a Patron
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </header>

                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="space-y-6">
                            <div className="inline-flex items-center gap-3 rounded-full border border-[#d4b28c] bg-[#f9efe2] px-4 py-2 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Order confirmed
                            </div>
                            <div>
                                <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                    Order #{order.id} secured
                                </h1>
                                <p className="mt-3 text-sm text-[#5a4a3a]">
                                    Status: {order.status} • Payment {order.payment_status}
                                </p>
                                {order.placed_at && (
                                    <p className="mt-1 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Placed {order.placed_at}
                                    </p>
                                )}
                            </div>

                            <div className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-6">
                                <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    Items
                                </p>
                                <div className="mt-4 space-y-4">
                                    {order.items.map((item) => (
                                        <div key={item.id} className="space-y-1">
                                            <p className="text-base font-semibold">
                                                {item.product_name}
                                            </p>
                                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                {item.vendor_name} • {item.quantity} × {item.unit_price}
                                            </p>
                                            <p className="text-sm text-[#2b241c]">
                                                Line total {item.line_total} {order.currency}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                {shipping && (
                                    <div className="rounded-[24px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                        <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                            {addressLabel(shipping.type)}
                                        </p>
                                        <p className="mt-3 text-sm font-semibold">
                                            {shipping.full_name}
                                        </p>
                                        <p className="text-sm text-[#5a4a3a]">
                                            {shipping.line1}
                                            {shipping.line2 ? `, ${shipping.line2}` : ''}
                                        </p>
                                        <p className="text-sm text-[#5a4a3a]">
                                            {shipping.city}
                                            {shipping.region ? `, ${shipping.region}` : ''}{' '}
                                            {shipping.postal_code ?? ''}
                                        </p>
                                        <p className="text-sm text-[#5a4a3a]">
                                            {shipping.country_code}
                                        </p>
                                    </div>
                                )}
                                {billing && (
                                    <div className="rounded-[24px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                        <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                            {addressLabel(billing.type)}
                                        </p>
                                        <p className="mt-3 text-sm font-semibold">
                                            {billing.full_name}
                                        </p>
                                        <p className="text-sm text-[#5a4a3a]">
                                            {billing.line1}
                                            {billing.line2 ? `, ${billing.line2}` : ''}
                                        </p>
                                        <p className="text-sm text-[#5a4a3a]">
                                            {billing.city}
                                            {billing.region ? `, ${billing.region}` : ''}{' '}
                                            {billing.postal_code ?? ''}
                                        </p>
                                        <p className="text-sm text-[#5a4a3a]">
                                            {billing.country_code}
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                        <aside className="rounded-[32px] border border-[#d4b28c] bg-[#f9efe2] p-6 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.6)]">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Summary
                            </p>
                            <div className="mt-4 space-y-3 text-sm">
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Subtotal
                                    </span>
                                    <span>
                                        {order.subtotal} {order.currency}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Commission
                                    </span>
                                    <span>
                                        {order.commission_total} {order.currency}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Total
                                    </span>
                                    <span className="text-base font-semibold">
                                        {order.total} {order.currency}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Payment
                                    </span>
                                    <span>{order.payment_method}</span>
                                </div>
                                <div className="flex items-center justify-between">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Shipping
                                    </span>
                                    <span>{order.shipping_responsibility}</span>
                                </div>
                            </div>
                            {auth.user && (
                                <Link
                                    href={ordersIndex()}
                                    className="mt-6 inline-flex w-full items-center justify-center rounded-full border border-[#2b241c] px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                >
                                    View order history
                                </Link>
                            )}
                            {!auth.user && (
                                <p className="mt-6 text-xs text-[#5a4a3a]">
                                    Keep your order number handy. A confirmation email is
                                    on its way.
                                </p>
                            )}
                        </aside>
                    </section>
                </div>
            </div>
        </>
    );
}
