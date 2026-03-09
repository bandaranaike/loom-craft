import { Form, Head, Link } from '@inertiajs/react';
import { formatMoney } from '@/lib/currency';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { show as checkoutShow } from '@/routes/checkout';
import { index as productsIndex } from '@/routes/products';
import { show as vendorShow } from '@/routes/vendors';
import { destroy as cartItemDestroy, update as cartItemUpdate } from '@/routes/cart/items';

type CartItem = {
    id: number;
    product_id: number;
    name: string;
    vendor_name: string;
    vendor_slug: string | null;
    image_url: string | null;
    quantity: number;
    original_unit_price: string;
    unit_price: string;
    original_line_total: string;
    line_total: string;
    effective_discount_percentage: string;
    has_discount: boolean;
};

type CartSummary = {
    cart_id: number;
    currency: string;
    items: CartItem[];
    item_count: number;
    subtotal: string;
};

type CartPageProps = {
    cart: CartSummary;
    canRegister?: boolean;
};

export default function CartPage({ cart, canRegister = true }: CartPageProps) {
    return (
        <>
            <Head title="Cart — LoomCraft">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="space-y-6">
                            <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Cart Atelier
                            </div>
                            <div>
                                <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                    Your curated selection
                                </h1>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">
                                    Every piece remains reserved while you finalize your
                                    checkout.
                                </p>
                            </div>

                            {cart.items.length === 0 ? (
                                <div className="rounded-[32px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-8 text-center">
                                    <p className="text-sm uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Cart is empty
                                    </p>
                                    <p className="mt-4 text-base text-(--welcome-body-text)">
                                        Browse the LoomCraft collection to begin building
                                        your order.
                                    </p>
                                    <Link
                                        href={productsIndex()}
                                        className="mt-6 inline-flex items-center justify-center rounded-full border border-(--welcome-strong) px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                    >
                                        Explore Collection
                                    </Link>
                                </div>
                            ) : (
                                <div className="space-y-5">
                                    {cart.items.map((item) => (
                                        <div
                                            key={item.id}
                                            className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5"
                                        >
                                            <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
                                                <div className="h-24 w-32 shrink-0 overflow-hidden rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-1)">
                                                    {item.image_url ? (
                                                        <img
                                                            src={item.image_url}
                                                            alt={item.name}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="flex h-full w-full items-center justify-center text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                                            LoomCraft
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1 space-y-2">
                                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                        {item.vendor_slug ? (
                                                            <Link href={vendorShow(item.vendor_slug)}>
                                                                {item.vendor_name}
                                                            </Link>
                                                        ) : (
                                                            item.vendor_name
                                                        )}
                                                    </p>
                                                    <p className="font-['Playfair_Display',serif] text-xl">
                                                        {item.name}
                                                    </p>
                                                    <div className="text-sm text-(--welcome-body-text)">
                                                        <p>
                                                            Unit price{' '}
                                                            {formatMoney(item.unit_price, cart.currency)}
                                                        </p>
                                                        {item.has_discount && (
                                                            <p className="text-xs text-(--welcome-muted-text) line-through decoration-1 decoration-(--welcome-muted-text)">
                                                                {formatMoney(item.original_unit_price, cart.currency)} • {item.effective_discount_percentage}% off
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="mt-4 flex flex-col gap-4 border-t border-(--welcome-border-soft) pt-4 sm:flex-row sm:items-center sm:justify-between">
                                                <Form
                                                    {...cartItemUpdate.form(item.id)}
                                                    className="flex flex-wrap items-center gap-3"
                                                >
                                                    <label className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                        Quantity
                                                    </label>
                                                    <input
                                                        type="number"
                                                        name="quantity"
                                                        min={0}
                                                        defaultValue={item.quantity}
                                                        className="w-24 rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                                    />
                                                    <button
                                                        type="submit"
                                                        className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                                    >
                                                        Update
                                                    </button>
                                                </Form>
                                                <div className="flex items-center gap-4">
                                                    <span className="text-sm uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                        Line total
                                                    </span>
                                                    <span className="text-lg font-semibold">
                                                        {formatMoney(item.line_total, cart.currency)}
                                                    </span>
                                                    {item.has_discount && (
                                                        <span className="text-xs text-(--welcome-muted-text) line-through decoration-1 decoration-(--welcome-muted-text)">
                                                            {formatMoney(item.original_line_total, cart.currency)}
                                                        </span>
                                                    )}
                                                    <Form {...cartItemDestroy.form(item.id)}>
                                                        <button
                                                            type="submit"
                                                            className="rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-on-strong)"
                                                        >
                                                            Remove
                                                        </button>
                                                    </Form>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                        <aside className="rounded-[32px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Order Summary
                            </p>
                            <div className="mt-6 space-y-4">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Items
                                    </span>
                                    <span className="text-(--welcome-strong)">
                                        {cart.item_count}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-sm">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Subtotal
                                    </span>
                                    <span className="text-(--welcome-strong)">
                                        {formatMoney(cart.subtotal, cart.currency)}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-sm">
                                    <span className="uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Currency
                                    </span>
                                    <span className="text-(--welcome-strong)">
                                        {cart.currency}
                                    </span>
                                </div>
                            </div>
                            <Link
                                href={checkoutShow()}
                                className={`mt-6 inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] transition ${
                                    cart.items.length === 0
                                        ? 'cursor-not-allowed border-(--welcome-border) text-(--welcome-muted-60)'
                                        : 'text-(--welcome-strong) hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)'
                                }`}
                            >
                                Proceed to checkout
                            </Link>
                            <p className="mt-4 text-xs text-(--welcome-body-text)">
                                Shipping is handled after the order is confirmed. Taxes are
                                calculated by your chosen vendor or the platform.
                            </p>
                        </aside>
                    </section>
            </PublicSiteLayout>
        </>
    );
}
