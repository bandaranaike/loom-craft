import { Form, Head, Link } from '@inertiajs/react';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { show as checkoutShow } from '@/routes/checkout';
import { index as productsIndex } from '@/routes/products';
import { destroy as cartItemDestroy, update as cartItemUpdate } from '@/routes/cart/items';

type CartItem = {
    id: number;
    product_id: number;
    name: string;
    vendor_name: string;
    image_url: string | null;
    quantity: number;
    unit_price: string;
    line_total: string;
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
                            <div className="inline-flex items-center gap-3 rounded-full border border-[#d4b28c] bg-[#f9efe2] px-4 py-2 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Cart Atelier
                            </div>
                            <div>
                                <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                    Your curated selection
                                </h1>
                                <p className="mt-3 text-sm text-[#5a4a3a]">
                                    Every piece remains reserved while you finalize your
                                    checkout.
                                </p>
                            </div>

                            {cart.items.length === 0 ? (
                                <div className="rounded-[32px] border border-[#e0c7a7] bg-[#fff8ed] p-8 text-center">
                                    <p className="text-sm uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Cart is empty
                                    </p>
                                    <p className="mt-4 text-base text-[#5a4a3a]">
                                        Browse the LoomCraft collection to begin building
                                        your order.
                                    </p>
                                    <Link
                                        href={productsIndex()}
                                        className="mt-6 inline-flex items-center justify-center rounded-full border border-[#2b241c] px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                    >
                                        Explore Collection
                                    </Link>
                                </div>
                            ) : (
                                <div className="space-y-5">
                                    {cart.items.map((item) => (
                                        <div
                                            key={item.id}
                                            className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5"
                                        >
                                            <div className="flex flex-col gap-5 sm:flex-row sm:items-center">
                                                <div className="h-24 w-32 shrink-0 overflow-hidden rounded-[20px] border border-[#e0c7a7] bg-[#f9efe2]">
                                                    {item.image_url ? (
                                                        <img
                                                            src={item.image_url}
                                                            alt={item.name}
                                                            className="h-full w-full object-cover"
                                                        />
                                                    ) : (
                                                        <div className="flex h-full w-full items-center justify-center text-xs uppercase tracking-[0.2em] text-[#7a5a3a]">
                                                            LoomCraft
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="flex-1 space-y-2">
                                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                        {item.vendor_name}
                                                    </p>
                                                    <p className="font-['Playfair_Display',serif] text-xl">
                                                        {item.name}
                                                    </p>
                                                    <p className="text-sm text-[#5a4a3a]">
                                                        Unit price {item.unit_price} {cart.currency}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="mt-4 flex flex-col gap-4 border-t border-[#e0c7a7] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                                <Form
                                                    {...cartItemUpdate.form(item.id)}
                                                    className="flex flex-wrap items-center gap-3"
                                                >
                                                    <label className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                        Quantity
                                                    </label>
                                                    <input
                                                        type="number"
                                                        name="quantity"
                                                        min={0}
                                                        defaultValue={item.quantity}
                                                        className="w-24 rounded-full border border-[#d4b28c] bg-[#fff8ed] px-4 py-2 text-sm text-[#2b241c] shadow-xs focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20"
                                                    />
                                                    <button
                                                        type="submit"
                                                        className="rounded-full border border-[#2b241c] px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                                    >
                                                        Update
                                                    </button>
                                                </Form>
                                                <div className="flex items-center gap-4">
                                                    <span className="text-sm uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                        Line total
                                                    </span>
                                                    <span className="text-lg font-semibold">
                                                        {item.line_total} {cart.currency}
                                                    </span>
                                                    <Form {...cartItemDestroy.form(item.id)}>
                                                        <button
                                                            type="submit"
                                                            className="rounded-full border border-[#7a5a3a] px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a] transition hover:bg-[#7a5a3a] hover:text-[#f6f1e8]"
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
                        <aside className="rounded-[32px] border border-[#d4b28c] bg-[#f9efe2] p-6 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.6)]">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Order Summary
                            </p>
                            <div className="mt-6 space-y-4">
                                <div className="flex items-center justify-between text-sm">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Items
                                    </span>
                                    <span className="text-[#2b241c]">
                                        {cart.item_count}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-sm">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Subtotal
                                    </span>
                                    <span className="text-[#2b241c]">
                                        {cart.subtotal} {cart.currency}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-sm">
                                    <span className="uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Currency
                                    </span>
                                    <span className="text-[#2b241c]">
                                        {cart.currency}
                                    </span>
                                </div>
                            </div>
                            <Link
                                href={checkoutShow()}
                                className={`mt-6 inline-flex w-full items-center justify-center rounded-full border border-[#2b241c] px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] transition ${
                                    cart.items.length === 0
                                        ? 'cursor-not-allowed border-[#d4b28c] text-[#7a5a3a]/60'
                                        : 'text-[#2b241c] hover:bg-[#2b241c] hover:text-[#f6f1e8]'
                                }`}
                            >
                                Proceed to checkout
                            </Link>
                            <p className="mt-4 text-xs text-[#5a4a3a]">
                                Shipping is handled after the order is confirmed. Taxes are
                                calculated by your chosen vendor or the platform.
                            </p>
                        </aside>
                    </section>
            </PublicSiteLayout>
        </>
    );
}
