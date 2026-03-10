import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import ProductColorSelector from '@/components/product-color-selector';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { dashboard } from '@/routes';
import { store } from '@/routes/vendor/products';
import { show as vendorShow } from '@/routes/vendors';

type Props = {
    commission_rate: string;
    vendor_name: string | null;
    vendor_slug: string | null;
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
    status?: string;
};

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

const textAreaClassName =
    'w-full rounded-[24px] border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

const fileInputClassName =
    'w-full rounded-[24px] border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) shadow-[0_8px_20px_-18px_var(--welcome-shadow-strong)] file:mr-4 file:rounded-full file:border-0 file:bg-(--welcome-strong) file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-[0.3em] file:text-(--welcome-on-strong) hover:file:bg-(--welcome-strong-hover) focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function ProductCreate() {
    const { commission_rate, vendor_name, vendor_slug, categories, colors, status } = usePage<Props>().props;
    const [vendorPrice, setVendorPrice] = useState('');
    const [discountPercentage, setDiscountPercentage] = useState('');

    const baseSellingPrice = useMemo(() => {
        const parsed = Number.parseFloat(vendorPrice);
        if (!vendorPrice || Number.isNaN(parsed)) {
            return '—';
        }

        const rate = Number.parseFloat(commission_rate || '7');
        return (parsed * (1 + rate / 100)).toFixed(2);
    }, [commission_rate, vendorPrice]);

    const discountedSellingPrice = useMemo(() => {
        if (baseSellingPrice === '—') {
            return '—';
        }

        const discount = Number.parseFloat(discountPercentage || '0');

        if (Number.isNaN(discount) || discount <= 0) {
            return baseSellingPrice;
        }

        return (Number.parseFloat(baseSellingPrice) * (1 - discount / 100)).toFixed(2);
    }, [baseSellingPrice, discountPercentage]);

    return (
        <>
            <Head title="Create Product">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout
                canRegister={false}
                headerActions={
                    <Link
                        href={dashboard()}
                        className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                    >
                        Back to Dashboard
                    </Link>
                }
            >
                    <section className="relative z-10 mx-auto grid w-full max-w-7xl gap-8 px-6 pb-16 pt-4 lg:grid-cols-[0.8fr_1.2fr]">
                        <div className="space-y-5">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Product Atelier
                            </p>
                            <h1 className="font-['Playfair_Display',serif] text-3xl leading-tight md:text-4xl">
                                Register a new heritage piece for review.
                            </h1>
                            <p className="max-w-lg text-sm text-(--welcome-body-text)">
                                Share the provenance, pricing, and materials for each piece.
                                Approved listings are curated for collectors worldwide.
                            </p>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Vendor Atelier
                                    </p>
                                    <p className="mt-2 text-sm text-(--welcome-body-text)">
                                        {vendor_name && vendor_slug ? (
                                            <Link href={vendorShow(vendor_slug)}>
                                                {vendor_name}
                                            </Link>
                                        ) : (
                                            vendor_name ?? 'LoomCraft artisan'
                                        )}
                                    </p>
                                </div>
                                <div className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Commission
                                    </p>
                                    <p className="mt-2 text-sm text-(--welcome-body-text)">
                                        A fixed {commission_rate}% supports platform curation.
                                    </p>
                                </div>
                            </div>
                            <div className="grid gap-3 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Pricing Preview
                                </p>
                                <div className="flex items-center justify-between text-sm text-(--welcome-body-text)">
                                    <span>Vendor price</span>
                                    <span>
                                        {vendorPrice ? `$${vendorPrice}` : '—'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-sm text-(--welcome-body-text)">
                                    <span>Catalog price</span>
                                    <span>{baseSellingPrice === '—' ? '—' : `$${baseSellingPrice}`}</span>
                                </div>
                                <div className="flex items-center justify-between text-sm text-(--welcome-body-text)">
                                    <span>Customer price</span>
                                    <span>{discountedSellingPrice === '—' ? '—' : `$${discountedSellingPrice}`}</span>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)] lg:p-8">
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        Product dossier
                                    </p>
                                    <h2 className="font-['Playfair_Display',serif] text-2xl">
                                        Listing details
                                    </h2>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        Provide full production context and media references.
                                    </p>
                                </div>

                                {status && (
                                    <div className="mt-4 rounded-3xl border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-muted-text)">
                                        {status}
                                    </div>
                                )}

                                <Form
                                    {...store.form()}
                                    className="mt-6 grid gap-5 lg:grid-cols-2"
                                    encType="multipart/form-data"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2 lg:col-span-2">
                                            <div className="grid gap-5 lg:grid-cols-3">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="product_code"
                                                            className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                        >
                                                            Product code
                                                        </label>
                                                        <input
                                                            id="product_code"
                                                            type="text"
                                                            name="product_code"
                                                            placeholder="LC-00001"
                                                            className={inputClassName}
                                                            required
                                                        />
                                                        <InputError
                                                            message={errors.product_code}
                                                            className="text-xs"
                                                        />
                                                    </div>
                                                    <div className="grid gap-2 lg:col-span-2">
                                                        <label
                                                            htmlFor="name"
                                                            className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                        >
                                                            Product name
                                                        </label>
                                                        <input
                                                            id="name"
                                                            type="text"
                                                            name="name"
                                                            placeholder="Crimson Dumbara Runner"
                                                            className={inputClassName}
                                                            required
                                                        />
                                                        <InputError
                                                            message={errors.name}
                                                            className="text-xs"
                                                        />
                                                    </div>
                                                    <div className="grid gap-5 lg:col-span-3 lg:grid-cols-2">
                                                        <div className="grid gap-2">
                                                            <label
                                                                htmlFor="vendor_price"
                                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                            >
                                                                Vendor price
                                                            </label>
                                                            <input
                                                                id="vendor_price"
                                                                type="number"
                                                                name="vendor_price"
                                                                step="0.01"
                                                                min="0"
                                                                value={vendorPrice}
                                                                onChange={(event) =>
                                                                    setVendorPrice(
                                                                        event.target.value,
                                                                    )
                                                                }
                                                                placeholder="1500.00"
                                                                className={inputClassName}
                                                                required
                                                            />
                                                            <InputError
                                                                message={errors.vendor_price}
                                                                className="text-xs"
                                                            />
                                                        </div>
                                                        <div className="grid gap-2">
                                                            <label
                                                                htmlFor="discount_percentage"
                                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                            >
                                                                Product discount %
                                                            </label>
                                                            <input
                                                                id="discount_percentage"
                                                                type="number"
                                                                name="discount_percentage"
                                                                step="0.01"
                                                                min="0"
                                                                max="100"
                                                                value={discountPercentage}
                                                                onChange={(event) => setDiscountPercentage(event.target.value)}
                                                                placeholder="0.00"
                                                                className={inputClassName}
                                                            />
                                                            <InputError
                                                                message={errors.discount_percentage}
                                                                className="text-xs"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="grid gap-2 lg:col-span-2">
                                                <label
                                                    htmlFor="description"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                >
                                                    Description
                                                </label>
                                                <textarea
                                                    id="description"
                                                    name="description"
                                                    rows={5}
                                                    placeholder="Describe the motif, provenance, and artisan story."
                                                    className={textAreaClassName}
                                                    required
                                                />
                                                <InputError
                                                    message={errors.description}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <div className="grid gap-2 lg:col-span-2">
                                                <p className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    Categories
                                                </p>
                                                <div className="grid gap-2 rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-2) p-4">
                                                    {categories.length === 0 ? (
                                                        <p className="text-xs text-(--welcome-muted-text)">
                                                            No active categories are available yet.
                                                        </p>
                                                    ) : (
                                                        <div className="grid gap-2 sm:grid-cols-2">
                                                            {categories.map((category) => (
                                                                <label
                                                                    key={category.id}
                                                                    className="flex items-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-2 text-sm text-(--welcome-strong)"
                                                                >
                                                                    <input
                                                                        type="checkbox"
                                                                        name="category_ids[]"
                                                                        value={category.id}
                                                                    />
                                                                    {category.name}
                                                                </label>
                                                            ))}
                                                        </div>
                                                    )}
                                                </div>
                                                <InputError
                                                    message={errors.category_ids}
                                                    className="text-xs"
                                                />
                                                <InputError
                                                    message={errors['category_ids.0']}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <div className="lg:col-span-2">
                                                <ProductColorSelector
                                                    colors={colors}
                                                    errorMessage={errors.color_ids}
                                                    itemErrorMessage={errors['color_ids.0']}
                                                />
                                            </div>

                                            <div className="grid gap-4 lg:col-span-2 sm:grid-cols-2">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="materials"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Materials
                                                    </label>
                                                    <input
                                                        id="materials"
                                                        type="text"
                                                        name="materials"
                                                        placeholder="Hand-spun cotton, natural dyes"
                                                        className={inputClassName}
                                                    />
                                                    <InputError
                                                        message={errors.materials}
                                                        className="text-xs"
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="pieces_count"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Pieces produced
                                                    </label>
                                                    <input
                                                        id="pieces_count"
                                                        type="number"
                                                        name="pieces_count"
                                                        min="1"
                                                        placeholder="4"
                                                        className={inputClassName}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.pieces_count
                                                        }
                                                        className="text-xs"
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid gap-4 lg:col-span-2 sm:grid-cols-2">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="production_time_days"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Production days
                                                    </label>
                                                    <input
                                                        id="production_time_days"
                                                        type="number"
                                                        name="production_time_days"
                                                        min="1"
                                                        placeholder="28"
                                                        className={inputClassName}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.production_time_days
                                                        }
                                                        className="text-xs"
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="dimension_unit"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Dimension unit
                                                    </label>
                                                    <input
                                                        id="dimension_unit"
                                                        type="text"
                                                        name="dimension_unit"
                                                        placeholder="cm"
                                                        className={inputClassName}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.dimension_unit
                                                        }
                                                        className="text-xs"
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid gap-4 lg:col-span-2 sm:grid-cols-3">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="dimension_length"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Length
                                                    </label>
                                                    <input
                                                        id="dimension_length"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="dimension_length"
                                                        placeholder="120"
                                                        className={inputClassName}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.dimension_length
                                                        }
                                                        className="text-xs"
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="dimension_width"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Width
                                                    </label>
                                                    <input
                                                        id="dimension_width"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="dimension_width"
                                                        placeholder="60"
                                                        className={inputClassName}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.dimension_width
                                                        }
                                                        className="text-xs"
                                                    />
                                                </div>
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="dimension_height"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Height
                                                    </label>
                                                    <input
                                                        id="dimension_height"
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="dimension_height"
                                                        placeholder="2"
                                                        className={inputClassName}
                                                    />
                                                    <InputError
                                                        message={
                                                            errors.dimension_height
                                                        }
                                                        className="text-xs"
                                                    />
                                                </div>
                                            </div>

                                            <div className="grid gap-3 lg:col-span-2">
                                                <p className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    Product images
                                                </p>
                                                <p className="text-xs text-(--welcome-muted-80)">
                                                    Upload one or more images. JPG, PNG, WEBP, or GIF.
                                                </p>
                                                <input
                                                    type="file"
                                                    name="images[]"
                                                    accept=".jpg,.jpeg,.png,.webp,.gif"
                                                    multiple
                                                    className={fileInputClassName}
                                                    required
                                                />
                                                <InputError
                                                    message={errors.images}
                                                    className="text-xs"
                                                />
                                                <InputError
                                                    message={errors['images.0']}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <div className="grid gap-2 lg:col-span-2">
                                                <label
                                                    htmlFor="video"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                >
                                                    Optional video
                                                </label>
                                                <p className="text-xs text-(--welcome-muted-80)">
                                                    MP4, MOV, WEBM, or MKV. Uploaded to YouTube.
                                                </p>
                                                <input
                                                    id="video"
                                                    type="file"
                                                    name="video"
                                                    accept="video/mp4,video/quicktime,video/webm,video/x-matroska"
                                                    className={fileInputClassName}
                                                />
                                                <InputError
                                                    message={errors.video}
                                                    className="text-xs"
                                                />
                                            </div>

                                            <button
                                                type="submit"
                                                className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70 lg:col-span-2"
                                                disabled={processing}
                                            >
                                                {processing && (
                                                    <Spinner className="text-(--welcome-on-strong)" />
                                                )}
                                                Submit for Review
                                            </button>
                                            <p className="text-center text-xs uppercase tracking-[0.25em] text-(--welcome-muted-text) lg:col-span-2">
                                                Listings are reviewed before publishing.
                                            </p>
                                        </>
                                    )}
                                </Form>
                            </div>
                        </div>
                    </section>
            </PublicSiteLayout>
        </>
    );
}
