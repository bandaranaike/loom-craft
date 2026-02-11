import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { dashboard, home, login, register } from '@/routes';
import { show as cartShow } from '@/routes/cart';
import { store as cartItemStore } from '@/routes/cart/items';
import InputError from '@/components/input-error';
import type { SharedData } from '@/types';
import type { FormEvent } from 'react';

type ProductImage = {
    type: 'image';
    url: string;
    alt_text: string | null;
};

type ProductDetails = {
    id: number;
    name: string;
    description: string;
    vendor_price: string;
    selling_price: string;
    commission_rate: string;
    materials: string | null;
    pieces_count: number | null;
    production_time_days: number | null;
    dimensions: {
        length: number | null;
        width: number | null;
        height: number | null;
        unit: string | null;
    };
    vendor: {
        id: number;
        display_name: string;
        location: string | null;
    };
    images: ProductImage[];
    video_url: string | null;
};

type ProductShowProps = {
    product: ProductDetails;
    canRegister?: boolean;
};

const formatDimensions = (dimensions: ProductDetails['dimensions']) => {
    const parts = [
        dimensions.length,
        dimensions.width,
        dimensions.height,
    ].filter((value) => value !== null) as number[];

    if (parts.length === 0) {
        return null;
    }

    const unit = dimensions.unit ? ` ${dimensions.unit}` : '';

    return `${parts.join(' × ')}${unit}`;
};

export default function ProductShow({
    product,
    canRegister = true,
}: ProductShowProps) {
    const { auth } = usePage<SharedData>().props;
    const primaryImage = product.images[0];
    const dimensionLabel = formatDimensions(product.dimensions);
    const form = useForm({
        product_id: product.id,
        quantity: 1,
        currency: 'USD',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(cartItemStore().url, {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title={`${product.name} — LoomCraft`}>
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

                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-6 lg:grid-cols-[1.05fr_0.95fr]">
                        <div className="grid gap-6">
                            <div className="rounded-[36px] border border-[#d4b28c] bg-[#f9efe2] p-6 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.6)]">
                                <div className="relative aspect-[4/3] overflow-hidden rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed]">
                                    {primaryImage ? (
                                        <img
                                            src={primaryImage.url}
                                            alt={primaryImage.alt_text ?? product.name}
                                            className="h-full w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center text-sm uppercase tracking-[0.3em] text-[#7a5a3a]">
                                            Image forthcoming
                                        </div>
                                    )}
                                </div>
                                {product.images.length > 1 && (
                                    <div className="mt-6 grid grid-cols-3 gap-4">
                                        {product.images.slice(0, 3).map((image) => (
                                            <div
                                                key={image.url}
                                                className="aspect-[4/3] overflow-hidden rounded-[20px] border border-[#e0c7a7] bg-[#fff8ed]"
                                            >
                                                <img
                                                    src={image.url}
                                                    alt={image.alt_text ?? product.name}
                                                    className="h-full w-full object-cover"
                                                />
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                            <div className="flex flex-wrap items-center gap-4 text-xs uppercase tracking-[0.25em] text-[#7a5a3a]">
                                <span>Approved LoomCraft Release</span>
                                <span>Curated by {product.vendor.display_name}</span>
                                <span>Commission {product.commission_rate}%</span>
                            </div>
                        </div>
                        <div className="space-y-6">
                            <div className="inline-flex items-center gap-3 rounded-full border border-[#d4b28c] bg-[#f9efe2] px-4 py-2 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Heritage Product
                            </div>
                            <div>
                                <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                    {product.name}
                                </h1>
                                <p className="mt-3 text-sm uppercase tracking-[0.35em] text-[#7a5a3a]">
                                    {product.vendor.display_name}
                                    {product.vendor.location
                                        ? ` • ${product.vendor.location}`
                                        : ''}
                                </p>
                            </div>
                            <div className="rounded-[32px] border border-[#e0c7a7] bg-[#fff8ed] p-6">
                                <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    Selling Price
                                </p>
                                <p className="mt-3 font-['Playfair_Display',serif] text-3xl">
                                    {product.selling_price} USD
                                </p>
                                <p className="mt-2 text-sm text-[#5a4a3a]">
                                    Vendor base price {product.vendor_price} USD + 7% commission.
                                </p>
                            </div>
                            <form onSubmit={submit} className="grid gap-4 rounded-[32px] border border-[#e0c7a7] bg-[#f9efe2] p-6">
                                <div className="flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                            Reserve this piece
                                        </p>
                                        <p className="mt-1 text-sm text-[#5a4a3a]">
                                            Choose a quantity to add to your cart.
                                        </p>
                                    </div>
                                    <Link
                                        href={cartShow()}
                                        className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a] underline"
                                    >
                                        View cart
                                    </Link>
                                </div>
                                <div className="flex flex-wrap items-center gap-4">
                                    <label className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Quantity
                                    </label>
                                    <input
                                        type="number"
                                        min={1}
                                        name="quantity"
                                        value={form.data.quantity}
                                        onChange={(event) =>
                                            form.setData(
                                                'quantity',
                                                Number(event.target.value),
                                            )
                                        }
                                        className="w-24 rounded-full border border-[#d4b28c] bg-[#fff8ed] px-4 py-2 text-sm text-[#2b241c] shadow-xs focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20"
                                    />
                                    <button
                                        type="submit"
                                        disabled={form.processing}
                                        className="inline-flex items-center justify-center rounded-full border border-[#2b241c] px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8] disabled:cursor-not-allowed disabled:opacity-70"
                                    >
                                        {form.processing ? 'Adding...' : 'Add to cart'}
                                    </button>
                                </div>
                                <InputError message={form.errors.quantity} />
                                <InputError message={form.errors.product_id} />
                            </form>
                            <p className="text-base text-[#5a4a3a]">
                                {product.description}
                            </p>
                            <div className="grid gap-4 rounded-[32px] border border-[#e0c7a7] bg-[#f9efe2] p-6">
                                <div className="flex items-center justify-between gap-4 text-sm">
                                    <span className="uppercase tracking-[0.25em] text-[#7a5a3a]">
                                        Materials
                                    </span>
                                    <span className="text-[#2b241c]">
                                        {product.materials ?? 'Documented on request'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-4 text-sm">
                                    <span className="uppercase tracking-[0.25em] text-[#7a5a3a]">
                                        Pieces
                                    </span>
                                    <span className="text-[#2b241c]">
                                        {product.pieces_count ?? 'Limited run'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-4 text-sm">
                                    <span className="uppercase tracking-[0.25em] text-[#7a5a3a]">
                                        Production
                                    </span>
                                    <span className="text-[#2b241c]">
                                        {product.production_time_days
                                            ? `${product.production_time_days} days`
                                            : 'Timeline on request'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between gap-4 text-sm">
                                    <span className="uppercase tracking-[0.25em] text-[#7a5a3a]">
                                        Dimensions
                                    </span>
                                    <span className="text-[#2b241c]">
                                        {dimensionLabel ?? 'Dimensions on request'}
                                    </span>
                                </div>
                            </div>
                            <div className="flex flex-wrap items-center gap-4">
                                <button
                                    type="button"
                                    className="rounded-full bg-[#2b241c] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#f6f1e8] transition hover:-translate-y-0.5 hover:bg-[#3a2f25]"
                                >
                                    Request Purchase
                                </button>
                                {product.video_url && (
                                    <a
                                        href={product.video_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="rounded-full border border-[#2b241c] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#2b241c] transition hover:-translate-y-0.5 hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                    >
                                        Watch Studio Video
                                    </a>
                                )}
                            </div>
                        </div>
                    </section>
                </div>

                <section className="mx-auto w-full max-w-6xl px-6 pb-20">
                    <div className="grid gap-8 rounded-[48px] border border-[#e0c7a7] bg-[#fff8ed] p-10 md:grid-cols-3">
                        <div className="space-y-3 border-b border-[#e0c7a7] pb-6 last:border-b-0 last:pb-0 md:border-b-0 md:border-r md:pb-0 md:pr-8 md:last:border-r-0 md:last:pr-0">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Provenance
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Artisan Verified
                            </h2>
                            <p className="text-sm text-[#5a4a3a]">
                                Every LoomCraft piece is reviewed for authenticity and cultural
                                lineage before it reaches patrons.
                            </p>
                        </div>
                        <div className="space-y-3 border-b border-[#e0c7a7] pb-6 last:border-b-0 last:pb-0 md:border-b-0 md:border-r md:pb-0 md:pr-8 md:last:border-r-0 md:last:pr-0">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Care Notes
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Keeper&apos;s Guide
                            </h2>
                            <p className="text-sm text-[#5a4a3a]">
                                Request the artisan&apos;s care ritual to preserve texture,
                                luminosity, and weave tension.
                            </p>
                        </div>
                        <div className="space-y-3">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Commission
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Transparent 7%
                            </h2>
                            <p className="text-sm text-[#5a4a3a]">
                                The LoomCraft commission is fixed and visible, supporting artisan
                                growth without hidden fees.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </>
    );
}
