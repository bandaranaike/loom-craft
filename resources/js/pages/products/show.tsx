import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent, JSX } from 'react';
import { useEffect, useRef, useState } from 'react';
import InputError from '@/components/input-error';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { DEFAULT_CURRENCY, formatMoney } from '@/lib/currency';
import { show as cartShow } from '@/routes/cart';
import { store as cartItemStore } from '@/routes/cart/items';
import { show as vendorShow } from '@/routes/vendors';

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
        slug: string | null;
        location: string | null;
    };
    categories: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
    colors: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
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

const colorSwatchMap: Record<string, string> = {
    red: '#dc2626',
    yellow: '#eab308',
    blue: '#2563eb',
    orange: '#ea580c',
    green: '#16a34a',
    purple: '#7c3aed',
    'yellow-orange': '#f59e0b',
    'red-orange': '#f97316',
    'red-purple': '#c026d3',
    'blue-purple': '#6366f1',
    'blue-green': '#0f766e',
    'yellow-green': '#65a30d',
    black: '#111827',
    white: '#ffffff',
    beige: '#d6c2a1',
    brown: '#8b5e3c',
    pink: '#ec4899',
    teal: '#0d9488',
    amber: '#f59e0b',
};

const resolveColorSwatch = (slug: string): string => {
    return colorSwatchMap[slug] ?? '#9ca3af';
};

export default function ProductShow({
    product,
    canRegister = true,
}: ProductShowProps) {
    const [selectedImageUrl, setSelectedImageUrl] = useState<string | null>(
        product.images[0]?.url ?? null,
    );
    const selectedImage =
        product.images.find((image) => image.url === selectedImageUrl) ??
        product.images[0] ??
        null;
    const selectedImageRef = useRef<HTMLImageElement | null>(null);
    const selectedImageContainerRef = useRef<HTMLDivElement | null>(null);
    const [thumbnailDockState, setThumbnailDockState] = useState<{
        isDocked: boolean;
        leftX: number;
    }>({
        isDocked: false,
        leftX: 0,
    });
    const dimensionLabel = formatDimensions(product.dimensions);
    const form = useForm({
        product_id: product.id,
        quantity: 1,
        currency: DEFAULT_CURRENCY,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(cartItemStore().url, {
            preserveScroll: true,
        });
    };

    const updateThumbnailDockState = (): void => {
        if (!selectedImageRef.current || !selectedImageContainerRef.current) {
            setThumbnailDockState({
                isDocked: false,
                leftX: 0,
            });

            return;
        }

        const containerRect =
            selectedImageContainerRef.current.getBoundingClientRect();
        const viewportBottom = window.innerHeight;
        const imageIsBelowViewportBottom =
            containerRect.bottom > viewportBottom;
        const imageIsInViewport =
            containerRect.top < viewportBottom && containerRect.bottom > 0;
        const shouldDock = imageIsBelowViewportBottom && imageIsInViewport;
        const nextLeftX = Math.round(containerRect.left);

        setThumbnailDockState((currentState) => {
            if (
                currentState.isDocked === shouldDock &&
                currentState.leftX === nextLeftX
            ) {
                return currentState;
            }

            return {
                isDocked: shouldDock,
                leftX: nextLeftX,
            };
        });
    };

    useEffect(() => {
        const handleScroll = (): void => {
            updateThumbnailDockState();
        };
        const handleResize = (): void => {
            updateThumbnailDockState();
        };

        window.addEventListener('scroll', handleScroll, { passive: true });
        window.addEventListener('resize', handleResize);

        const animationFrameId = window.requestAnimationFrame(() => {
            updateThumbnailDockState();
        });

        return () => {
            window.cancelAnimationFrame(animationFrameId);
            window.removeEventListener('scroll', handleScroll);
            window.removeEventListener('resize', handleResize);
        };
    }, [selectedImage?.url]);

    const renderThumbnails = (): JSX.Element => {
        return (
            <div className="flex flex-wrap items-center gap-3">
                {product.images.map((image) => {
                    const isSelected = image.url === selectedImage?.url;

                    return (
                        <button
                            key={image.url}
                            type="button"
                            onClick={() => setSelectedImageUrl(image.url)}
                            className={`h-16 w-16 cursor-pointer overflow-hidden rounded-lg transition-opacity ${
                                isSelected
                                    ? 'opacity-100'
                                    : 'opacity-60 hover:opacity-100'
                            }`}
                            aria-label={`Show image of ${product.name}`}
                            aria-pressed={isSelected}
                        >
                            <img
                                src={image.url}
                                alt={image.alt_text ?? product.name}
                                className="h-full w-full object-cover"
                            />
                        </button>
                    );
                })}
            </div>
        );
    };

    const shouldDockThumbnails =
        product.images.length > 1 && thumbnailDockState.isDocked;

    return (
        <>
            <Head title={`${product.name} — LoomCraft`}>
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pt-6 pb-16 lg:grid-cols-[1.05fr_0.95fr]">
                    <div className="grid gap-6">
                        <div className="grid gap-4">
                            <div ref={selectedImageContainerRef}>
                                {selectedImage ? (
                                    <img
                                        ref={selectedImageRef}
                                        src={selectedImage.url}
                                        alt={
                                            selectedImage.alt_text ??
                                            product.name
                                        }
                                        onLoad={updateThumbnailDockState}
                                        className="h-auto w-full rounded-4xl"
                                    />
                                ) : (
                                    <div className="flex min-h-72 w-full items-center justify-center text-sm tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Image forthcoming
                                    </div>
                                )}
                            </div>
                            {product.images.length > 1 &&
                                !shouldDockThumbnails &&
                                renderThumbnails()}
                        </div>
                        {shouldDockThumbnails && (
                            <div
                                className="pointer-events-none fixed bottom-4 z-20"
                                style={{
                                    left: `${thumbnailDockState.leftX}px`,
                                }}
                            >
                                <div className="pointer-events-auto">
                                    {renderThumbnails()}
                                </div>
                            </div>
                        )}
                        <div className="flex flex-wrap items-center gap-4 text-xs tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                            <span>Approved LoomCraft Release</span>
                            <span>
                                Curated by{' '}
                                {product.vendor.slug ? (
                                    <Link href={vendorShow(product.vendor.slug)}>
                                        {product.vendor.display_name}
                                    </Link>
                                ) : (
                                    product.vendor.display_name
                                )}
                            </span>
                        </div>
                    </div>
                    <div className="space-y-6">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Heritage Product
                        </div>
                        <div>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                {product.name}
                            </h1>
                            <p className="mt-3 text-sm tracking-[0.35em] text-(--welcome-muted-text) uppercase">
                                {product.vendor.slug ? (
                                    <Link href={vendorShow(product.vendor.slug)}>
                                        {product.vendor.display_name}
                                    </Link>
                                ) : (
                                    product.vendor.display_name
                                )}
                                {product.vendor.location
                                    ? ` • ${product.vendor.location}`
                                    : ''}
                            </p>
                            {product.categories.length > 0 && (
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {product.categories.map((category) => (
                                        <span
                                            key={category.id}
                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-[10px] uppercase tracking-[0.2em] text-(--welcome-muted-text)"
                                        >
                                            {category.name}
                                        </span>
                                    ))}
                                </div>
                            )}
                            {product.colors.length > 0 && (
                                <div className="mt-2 flex flex-wrap gap-1.5">
                                    {product.colors.map((color) => (
                                        <span
                                            key={color.id}
                                            title={color.name}
                                            className="block h-6 w-6 rounded-sm border border-black/10"
                                            style={{
                                                backgroundColor: resolveColorSwatch(color.slug),
                                            }}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>
                        <div className="rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Selling Price
                            </p>
                            <p className="mt-3 font-['Playfair_Display',serif] text-3xl">
                                {formatMoney(product.selling_price, DEFAULT_CURRENCY)}
                            </p>
                            <p className="mt-2 text-sm text-(--welcome-body-text)">
                                Crafted by verified artisans and prepared for collector-grade
                                delivery.
                            </p>
                        </div>
                        <form
                            onSubmit={submit}
                            className="grid gap-4 rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-6"
                        >
                            <div className="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Reserve this piece
                                    </p>
                                    <p className="mt-1 text-sm text-(--welcome-body-text)">
                                        Choose a quantity to add to your cart.
                                    </p>
                                </div>
                                <Link
                                    href={cartShow()}
                                    className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase underline"
                                >
                                    View cart
                                </Link>
                            </div>
                            <div className="flex flex-wrap items-center gap-4">
                                <label className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
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
                                    className="w-24 rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                />
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="inline-flex items-center justify-center rounded-full border border-(--welcome-strong) px-5 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {form.processing
                                        ? 'Adding...'
                                        : 'Add to cart'}
                                </button>
                            </div>
                            <InputError message={form.errors.quantity} />
                            <InputError message={form.errors.product_id} />
                        </form>
                        <p className="text-base text-(--welcome-body-text)">
                            {product.description}
                        </p>
                        <div className="grid gap-4 rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-6">
                            <div className="flex items-center justify-between gap-4 text-sm">
                                <span className="tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                                    Materials
                                </span>
                                <span className="text-(--welcome-strong)">
                                    {product.materials ??
                                        'Documented on request'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between gap-4 text-sm">
                                <span className="tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                                    Pieces
                                </span>
                                <span className="text-(--welcome-strong)">
                                    {product.pieces_count ?? 'Limited run'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between gap-4 text-sm">
                                <span className="tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                                    Production
                                </span>
                                <span className="text-(--welcome-strong)">
                                    {product.production_time_days
                                        ? `${product.production_time_days} days`
                                        : 'Timeline on request'}
                                </span>
                            </div>
                            <div className="flex items-center justify-between gap-4 text-sm">
                                <span className="tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                                    Dimensions
                                </span>
                                <span className="text-(--welcome-strong)">
                                    {dimensionLabel ?? 'Dimensions on request'}
                                </span>
                            </div>
                        </div>
                        <div className="flex flex-wrap items-center gap-4">
                            <button
                                type="button"
                                className="rounded-full bg-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover)"
                            >
                                Request Purchase
                            </button>
                            {product.video_url && (
                                <a
                                    href={product.video_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="rounded-full border border-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                >
                                    Watch Studio Video
                                </a>
                            )}
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-20">
                    <div className="grid gap-8 rounded-[48px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-10 md:grid-cols-3">
                        <div className="space-y-3 border-b border-(--welcome-border-soft) pb-6 last:border-b-0 last:pb-0 md:border-r md:border-b-0 md:pr-8 md:pb-0 md:last:border-r-0 md:last:pr-0">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Provenance
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Artisan Verified
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                Every LoomCraft piece is reviewed for
                                authenticity and cultural lineage before it
                                reaches patrons.
                            </p>
                        </div>
                        <div className="space-y-3 border-b border-(--welcome-border-soft) pb-6 last:border-b-0 last:pb-0 md:border-r md:border-b-0 md:pr-8 md:pb-0 md:last:border-r-0 md:last:pr-0">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Care Notes
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Keeper&apos;s Guide
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                Request the artisan&apos;s care ritual to
                                preserve texture, luminosity, and weave tension.
                            </p>
                        </div>
                        <div className="space-y-3">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Atelier Standard
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Curated Excellence
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                Each listing is reviewed for motif quality,
                                finishing precision, and presentation readiness
                                before release.
                            </p>
                        </div>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
