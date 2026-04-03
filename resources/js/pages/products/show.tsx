import { Head, Link, useForm, usePage } from '@inertiajs/react';
import type { FormEvent, JSX, KeyboardEvent, TouchEvent } from 'react';
import { useEffect, useMemo, useRef, useState } from 'react';
import DismissibleStockDelayAlert from '@/components/dismissible-stock-delay-alert';
import InputError from '@/components/input-error';
import ProductColorSwatches from '@/components/product-color-swatches';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import VendorInquiryForm from '@/components/vendor-inquiry-form';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { DEFAULT_CURRENCY, formatMoney } from '@/lib/currency';
import { resolveProductStockAvailability } from '@/lib/product-stock-availability';
import { show as cartShow } from '@/routes/cart';
import { store as cartItemStore } from '@/routes/cart/items';
import { store as productReviewStore } from '@/routes/products/reviews';
import { show as vendorShow } from '@/routes/vendors';

type ProductImage = {
    id: number;
    type: 'image';
    url: string;
    alt_text: string | null;
};

type ProductDetails = {
    id: number;
    slug: string;
    product_code: string;
    name: string;
    description: string;
    vendor_price: string;
    original_price: string;
    selling_price: string;
    effective_discount_percentage: string;
    has_discount: boolean;
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
        contact_email: string | null;
        contact_phone: string | null;
        whatsapp_number: string | null;
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
    cartCurrency: string;
    canRegister?: boolean;
    status?: string | null;
    review_summary: {
        average_rating: string | null;
        total_reviews: number;
    };
    reviews: Array<{
        id: number;
        rating: number;
        review: string;
        reviewer_name: string;
        created_at: string | null;
        created_at_human: string | null;
    }>;
    review_form: {
        can_submit: boolean;
        has_delivered_purchase: boolean;
        has_reviewed: boolean;
        requires_authentication: boolean;
        message: string | null;
    };
    reviewStatus?: string | null;
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

const renderRatingStars = (
    rating: number,
    filledClassName = 'text-amber-500',
    emptyClassName = 'text-(--welcome-border)',
) => {
    return Array.from({ length: 5 }, (_, index) => {
        const starNumber = index + 1;
        const isFilled = starNumber <= rating;

        return (
            <span
                key={starNumber}
                aria-hidden="true"
                className={isFilled ? filledClassName : emptyClassName}
            >
                ★
            </span>
        );
    });
};

export default function ProductShow({
    product,
    cartCurrency,
    canRegister = true,
    status = null,
    review_summary,
    reviews,
    review_form,
    reviewStatus = null,
}: ProductShowProps) {
    const { errors } = usePage<{ errors: Record<string, string> }>().props;
    const [activeImageIndex, setActiveImageIndex] = useState(0);
    const [isInquiryOpen, setIsInquiryOpen] = useState(false);
    const [isThumbnailDocked, setIsThumbnailDocked] = useState(false);
    const galleryFrameRef = useRef<HTMLDivElement | null>(null);
    const touchStartXRef = useRef<number | null>(null);
    const previousFrameBottomRef = useRef<number | null>(null);
    const previousScrollYRef = useRef(0);
    const dimensionLabel = formatDimensions(product.dimensions);
    const hasMultipleImages = product.images.length > 1;
    const selectedImage = product.images[activeImageIndex] ?? null;
    const form = useForm({
        product_id: product.id,
        quantity: 1,
        currency: cartCurrency,
    });
    const reviewFormState = useForm({
        rating: 5,
        review: '',
    });
    const hasInquiryErrors = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
    ].some((field) => Boolean(errors[field]));
    const stockAvailability = useMemo(
        () =>
            resolveProductStockAvailability(
                form.data.quantity,
                product.pieces_count,
                product.production_time_days,
            ),
        [
            form.data.quantity,
            product.pieces_count,
            product.production_time_days,
        ],
    );

    const isInquiryDialogOpen = status
        ? false
        : hasInquiryErrors
          ? true
          : isInquiryOpen;

    useEffect(() => {
        if (!hasMultipleImages) {
            return;
        }

        const updateThumbnailDock = (): void => {
            const galleryFrame = galleryFrameRef.current;

            if (galleryFrame === null) {
                return;
            }

            const isMobileView = window.innerWidth < 768;
            if (isMobileView) {
                setIsThumbnailDocked(false);
                return;
            }

            const frameRect = galleryFrame.getBoundingClientRect();
            const viewportBottom = window.innerHeight;
            const tolerance = 8;
            const isGalleryVisible =
                frameRect.top < viewportBottom && frameRect.bottom > 0;
            const currentScrollY = window.scrollY;
            const isScrollingDown = currentScrollY > previousScrollYRef.current;
            const previousBottom = previousFrameBottomRef.current;

            previousScrollYRef.current = currentScrollY;
            previousFrameBottomRef.current = frameRect.bottom;

            if (!isGalleryVisible || frameRect.bottom <= 0) {
                setIsThumbnailDocked(false);

                return;
            }

            if (
                isScrollingDown &&
                previousBottom !== null &&
                previousBottom > viewportBottom + tolerance &&
                frameRect.bottom <= viewportBottom + tolerance
            ) {
                setIsThumbnailDocked(true);

                return;
            }

            if (
                !isScrollingDown &&
                frameRect.bottom > viewportBottom + tolerance
            ) {
                setIsThumbnailDocked(false);
            }
        };

        const frame = window.requestAnimationFrame(updateThumbnailDock);

        window.addEventListener('scroll', updateThumbnailDock, {
            passive: true,
        });
        window.addEventListener('resize', updateThumbnailDock);

        return () => {
            window.cancelAnimationFrame(frame);
            window.removeEventListener('scroll', updateThumbnailDock);
            window.removeEventListener('resize', updateThumbnailDock);
        };
    }, [hasMultipleImages]);

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.post(cartItemStore().url, {
            preserveScroll: true,
        });
    };

    const submitReview = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        reviewFormState.post(productReviewStore(product.slug).url, {
            preserveScroll: true,
            onSuccess: () => {
                reviewFormState.reset('review');
                reviewFormState.setData('rating', 5);
            },
        });
    };

    const selectImageAt = (index: number): void => {
        if (product.images.length === 0) {
            return;
        }

        const nextIndex =
            (index + product.images.length) % product.images.length;

        setActiveImageIndex(nextIndex);
    };

    const goToPreviousImage = (): void => {
        selectImageAt(activeImageIndex - 1);
    };

    const goToNextImage = (): void => {
        selectImageAt(activeImageIndex + 1);
    };

    const handleImageTouchStart = (event: TouchEvent<HTMLDivElement>): void => {
        touchStartXRef.current = event.touches[0]?.clientX ?? null;
    };

    const handleImageTouchEnd = (event: TouchEvent<HTMLDivElement>): void => {
        const touchStartX = touchStartXRef.current;
        const touchEndX = event.changedTouches[0]?.clientX ?? null;

        touchStartXRef.current = null;

        if (touchStartX === null || touchEndX === null) {
            return;
        }

        const deltaX = touchEndX - touchStartX;
        const minimumSwipeDistance = 40;

        if (Math.abs(deltaX) < minimumSwipeDistance) {
            return;
        }

        if (deltaX > 0) {
            goToPreviousImage();
        } else {
            goToNextImage();
        }
    };

    const handleImageKeyDown = (event: KeyboardEvent<HTMLDivElement>): void => {
        if (!hasMultipleImages) {
            return;
        }

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goToPreviousImage();
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            goToNextImage();
        }
    };

    const renderThumbnails = (className = ''): JSX.Element => {
        return (
            <div
                className={`flex flex-wrap items-center gap-3 ${className}`.trim()}
            >
                {product.images.map((image, index) => {
                    const isSelected = index === activeImageIndex;

                    return (
                        <button
                            key={image.id}
                            type="button"
                            onClick={() => selectImageAt(index)}
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
                            <div
                                ref={galleryFrameRef}
                                className="group relative w-full touch-pan-y overflow-hidden rounded-4xl"
                                onTouchStart={handleImageTouchStart}
                                onTouchEnd={handleImageTouchEnd}
                                onKeyDown={handleImageKeyDown}
                                tabIndex={hasMultipleImages ? 0 : -1}
                                aria-label={`Product image gallery for ${product.name}`}
                            >
                                {selectedImage ? (
                                    <>
                                        <img
                                            src={selectedImage.url}
                                            alt={
                                                selectedImage.alt_text ??
                                                product.name
                                            }
                                            className="h-auto w-full rounded-4xl object-contain"
                                        />
                                        {hasMultipleImages && (
                                            <>
                                                <button
                                                    type="button"
                                                    onClick={goToPreviousImage}
                                                    className="absolute top-1/2 left-3 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-black/30 text-lg font-semibold text-white opacity-0 backdrop-blur-sm transition group-focus-within:opacity-100 group-hover:opacity-100 hover:bg-black/50 md:inline-flex"
                                                    aria-label="Show previous image"
                                                >
                                                    ‹
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={goToNextImage}
                                                    className="absolute top-1/2 right-3 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/40 bg-black/30 text-lg font-semibold text-white opacity-0 backdrop-blur-sm transition group-focus-within:opacity-100 group-hover:opacity-100 hover:bg-black/50 md:inline-flex"
                                                    aria-label="Show next image"
                                                >
                                                    ›
                                                </button>
                                                {!isThumbnailDocked && (
                                                    <div className="fixed bottom-0 z-20 hidden justify-start p-4 md:flex">
                                                        {renderThumbnails()}
                                                    </div>
                                                )}
                                            </>
                                        )}
                                    </>
                                ) : (
                                    <div className="flex min-h-72 w-full items-center justify-center text-sm tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Image forthcoming
                                    </div>
                                )}
                            </div>
                            {hasMultipleImages && (
                                <div className="relative min-h-16">
                                    <div
                                        className={
                                            !isThumbnailDocked
                                                ? 'md:pointer-events-none md:opacity-0'
                                                : 'transition-opacity duration-200'
                                        }
                                    >
                                        {renderThumbnails()}
                                    </div>
                                </div>
                            )}
                        </div>
                        <div className="flex flex-wrap items-center gap-4 text-xs tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                            <span>Approved LoomCraft Release</span>
                            <span>
                                Curated by{' '}
                                {product.vendor.slug ? (
                                    <Link
                                        href={vendorShow(product.vendor.slug)}
                                    >
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
                            <p className="mb-3 text-xs tracking-[0.35em] text-(--welcome-muted-text) uppercase">
                                Product code: {product.product_code}
                            </p>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                {product.name}
                            </h1>
                            <p className="mt-3 text-sm tracking-[0.35em] text-(--welcome-muted-text) uppercase">
                                {product.vendor.slug ? (
                                    <Link
                                        href={vendorShow(product.vendor.slug)}
                                    >
                                        {product.vendor.display_name}
                                    </Link>
                                ) : (
                                    product.vendor.display_name
                                )}
                                {product.vendor.location
                                    ? ` • ${product.vendor.location}`
                                    : ''}
                            </p>
                            <div className="mt-4 flex flex-wrap items-center gap-3 text-sm text-(--welcome-body-text)">
                                <div className="flex items-center gap-1 text-base">
                                    {renderRatingStars(
                                        Math.round(
                                            Number(
                                                review_summary.average_rating ??
                                                    0,
                                            ),
                                        ),
                                    )}
                                </div>
                                <span className="font-medium text-(--welcome-strong)">
                                    {review_summary.total_reviews > 0 &&
                                    review_summary.average_rating
                                        ? `${review_summary.average_rating} / 5`
                                        : 'Not yet rated'}
                                </span>
                                <span className="text-(--welcome-muted-text)">
                                    {review_summary.total_reviews === 1
                                        ? '1 collector review'
                                        : `${review_summary.total_reviews} collector reviews`}
                                </span>
                            </div>
                            {product.categories.length > 0 && (
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {product.categories.map((category) => (
                                        <span
                                            key={category.id}
                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-[10px] tracking-[0.2em] text-(--welcome-muted-text) uppercase"
                                        >
                                            {category.name}
                                        </span>
                                    ))}
                                </div>
                            )}
                            <ProductColorSwatches colors={product.colors} />
                        </div>
                        <div className="rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Selling Price
                                </p>
                                {product.has_discount && (
                                    <span className="rounded-full bg-(--welcome-strong) px-3 py-1 text-[10px] font-semibold tracking-[0.3em] text-(--welcome-on-strong) uppercase">
                                        {product.effective_discount_percentage}%
                                        Off
                                    </span>
                                )}
                            </div>
                            <p className="mt-3 font-['Playfair_Display',serif] text-3xl">
                                {formatMoney(
                                    product.selling_price,
                                    DEFAULT_CURRENCY,
                                )}
                            </p>
                            {product.has_discount && (
                                <p className="mt-2 text-sm text-(--welcome-muted-text) line-through decoration-(--welcome-muted-text) decoration-1">
                                    {formatMoney(
                                        product.original_price,
                                        DEFAULT_CURRENCY,
                                    )}
                                </p>
                            )}
                            <p className="mt-2 text-sm text-(--welcome-body-text)">
                                Crafted by verified artisans and prepared for
                                collector-grade delivery.
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
                            <DismissibleStockDelayAlert
                                pageKey={`product-${product.id}`}
                                message={stockAvailability.stockDelayMessage}
                            />
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
                                    Available now
                                </span>
                                <span className="text-(--welcome-strong)">
                                    {product.pieces_count ?? 'Made to order'}
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
                                onClick={() => setIsInquiryOpen(true)}
                                className="rounded-full bg-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover)"
                            >
                                Send an inquiry
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

                <section className="mx-auto grid w-full max-w-6xl gap-8 px-6 pb-16 lg:grid-cols-[0.78fr_1.22fr]">
                    <div className="space-y-6">
                        <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-8">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Collector sentiment
                            </p>
                            <div className="mt-4 flex items-end gap-4">
                                <span className="font-['Playfair_Display',serif] text-5xl text-(--welcome-strong)">
                                    {review_summary.average_rating ?? '—'}
                                </span>
                                <div className="pb-2">
                                    <div className="flex items-center gap-1 text-lg">
                                        {renderRatingStars(
                                            Math.round(
                                                Number(
                                                    review_summary.average_rating ??
                                                        0,
                                                ),
                                            ),
                                        )}
                                    </div>
                                    <p className="mt-2 text-sm text-(--welcome-body-text)">
                                        {review_summary.total_reviews === 0
                                            ? 'Be the first verified buyer to review this piece.'
                                            : review_summary.total_reviews === 1
                                              ? 'Based on 1 verified review'
                                              : `Based on ${review_summary.total_reviews} verified reviews`}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-8">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Review eligibility
                            </p>
                            <h2 className="mt-3 font-['Playfair_Display',serif] text-2xl">
                                Verified after delivery
                            </h2>
                            <p className="mt-3 text-sm leading-6 text-(--welcome-body-text)">
                                Ratings are reserved for customers whose order
                                has been marked delivered, keeping product
                                feedback grounded in completed purchases.
                            </p>
                            {review_form.message && (
                                <p className="mt-4 rounded-3xl bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-body-text)">
                                    {review_form.message}
                                </p>
                            )}
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-8">
                            <div className="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Customer reviews
                                    </p>
                                    <h2 className="mt-3 font-['Playfair_Display',serif] text-3xl">
                                        What buyers are saying
                                    </h2>
                                </div>
                                <div className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-xs tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                                    Verified purchasers only
                                </div>
                            </div>

                            {reviewStatus && (
                                <div className="mt-6 rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-body-text)">
                                    {reviewStatus}
                                </div>
                            )}

                            {review_form.can_submit ? (
                                <form onSubmit={submitReview} className="mt-6 grid gap-5">
                                    <div>
                                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Your rating
                                        </p>
                                        <div className="mt-3 flex flex-wrap gap-2">
                                            {Array.from({ length: 5 }, (_, index) => {
                                                const rating = index + 1;
                                                const isSelected =
                                                    reviewFormState.data.rating >= rating;

                                                return (
                                                    <button
                                                        key={rating}
                                                        type="button"
                                                        onClick={() =>
                                                            reviewFormState.setData(
                                                                'rating',
                                                                rating,
                                                            )
                                                        }
                                                        className={`inline-flex h-11 w-11 items-center justify-center rounded-full border text-lg transition ${
                                                            isSelected
                                                                ? 'border-amber-500 bg-amber-500/12 text-amber-500'
                                                                : 'border-(--welcome-border) bg-(--welcome-surface-3) text-(--welcome-muted-text) hover:border-amber-500 hover:text-amber-500'
                                                        }`}
                                                        aria-label={`Rate ${rating} star${rating === 1 ? '' : 's'}`}
                                                        aria-pressed={reviewFormState.data.rating === rating}
                                                    >
                                                        ★
                                                    </button>
                                                );
                                            })}
                                        </div>
                                        <InputError
                                            message={reviewFormState.errors.rating}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            htmlFor="review"
                                            className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                                        >
                                            Your review
                                        </label>
                                        <textarea
                                            id="review"
                                            name="review"
                                            rows={5}
                                            value={reviewFormState.data.review}
                                            onChange={(event) =>
                                                reviewFormState.setData(
                                                    'review',
                                                    event.target.value,
                                                )
                                            }
                                            className="mt-3 w-full rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-3) px-5 py-4 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                            placeholder="Describe the workmanship, finish, delivery experience, or how the piece feels in your space."
                                        />
                                        <InputError
                                            message={reviewFormState.errors.review}
                                            className="mt-2"
                                        />
                                    </div>
                                    <div className="flex flex-wrap items-center justify-between gap-4">
                                        <p className="text-sm text-(--welcome-body-text)">
                                            Your review appears publicly on this
                                            product page once submitted.
                                        </p>
                                        <button
                                            type="submit"
                                            disabled={reviewFormState.processing}
                                            className="inline-flex items-center justify-center rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold tracking-[0.28em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                        >
                                            {reviewFormState.processing
                                                ? 'Publishing...'
                                                : 'Publish review'}
                                        </button>
                                    </div>
                                </form>
                            ) : (
                                <div className="mt-6 rounded-[32px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-6 text-sm leading-6 text-(--welcome-body-text)">
                                    {review_form.message ??
                                        'Reviews become available after a completed delivery.'}
                                </div>
                            )}
                        </div>

                        <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-8">
                            <div className="flex items-center justify-between gap-4">
                                <div>
                                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Recent feedback
                                    </p>
                                    <h2 className="mt-3 font-['Playfair_Display',serif] text-3xl">
                                        Review notes from delivered orders
                                    </h2>
                                </div>
                            </div>

                            {reviews.length === 0 ? (
                                <div className="mt-6 rounded-[32px] bg-(--welcome-surface-1) p-6 text-sm leading-6 text-(--welcome-body-text)">
                                    No reviews have been published yet. The
                                    first delivered buyer will set the tone for
                                    this listing.
                                </div>
                            ) : (
                                <div className="mt-6 grid gap-4">
                                    {reviews.map((review) => (
                                        <article
                                            key={review.id}
                                            className="rounded-[32px] bg-(--welcome-surface-1) p-6"
                                        >
                                            <div className="flex flex-wrap items-center justify-between gap-4">
                                                <div>
                                                    <p className="text-sm font-semibold text-(--welcome-strong)">
                                                        {review.reviewer_name}
                                                    </p>
                                                    <p className="mt-1 text-xs tracking-[0.25em] text-(--welcome-muted-text) uppercase">
                                                        {review.created_at_human ??
                                                            'Verified purchase'}
                                                    </p>
                                                </div>
                                                <div className="flex items-center gap-1 text-base">
                                                    {renderRatingStars(
                                                        review.rating,
                                                    )}
                                                </div>
                                            </div>
                                            <p className="mt-4 text-sm leading-7 text-(--welcome-body-text)">
                                                {review.review}
                                            </p>
                                        </article>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </section>

                <Dialog
                    open={isInquiryDialogOpen}
                    onOpenChange={setIsInquiryOpen}
                >
                    <DialogContent className="max-h-[92vh] overflow-y-auto border-(--welcome-border-soft) bg-(--welcome-surface-1) p-0 sm:max-w-2xl">
                        <DialogHeader className="sr-only">
                            <DialogTitle>Contact Vendor</DialogTitle>
                            <DialogDescription>
                                Send an inquiry directly to this vendor about
                                the selected product.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="p-8">
                            <VendorInquiryForm
                                vendorSlug={product.vendor.slug ?? ''}
                                contactEmail={product.vendor.contact_email}
                                contactPhone={product.vendor.contact_phone}
                                whatsappNumber={product.vendor.whatsapp_number}
                                status={status}
                            />
                        </div>
                    </DialogContent>
                </Dialog>

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
