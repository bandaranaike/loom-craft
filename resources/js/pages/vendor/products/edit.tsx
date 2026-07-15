import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import ProductColorSelector from '@/components/product-color-selector';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { formatMoney } from '@/lib/currency';
import { dashboard } from '@/routes';
import { index as vendorProductsIndex, update } from '@/routes/vendor/products';
import { destroy, store } from '@/routes/vendor/products/images';
import { show as vendorShow } from '@/routes/vendors';
import type { SharedData } from '@/types';

type ProductForm = {
    id: number;
    product_code: string;
    name: string;
    description: string;
    vendor_price: string;
    discount_percentage: string | null;
    materials: string | null;
    pieces_count: number | null;
    production_time_days: number | null;
    expiry_information: string | null;
    dimension_unit: string | null;
    category_ids: number[];
    color_ids: number[];
    variations: Array<{
        id: number;
        label: string;
        vendor_price: string;
        dimension_length: number | null;
        dimension_width: number | null;
        dimension_height: number | null;
    }>;
    images: Array<{
        id: number;
        url: string;
    }>;
};

type Props = {
    base_currency: string;
    commission_rate: string;
    vendor_name: string | null;
    vendor_slug: string | null;
    product: ProductForm;
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

type VariationDraft = {
    key: string;
    id: number | null;
    label: string;
    vendor_price: string;
    dimension_length: string;
    dimension_width: string;
    dimension_height: string;
};

const variationKey = () => Math.random().toString(36).slice(2);

export default function ProductEdit() {
    const { base_currency, commission_rate, vendor_name, vendor_slug, product, categories, colors, status, site } = usePage<SharedData & Props>().props;
    const [variations, setVariations] = useState<VariationDraft[]>(
        product.variations.length > 0
            ? product.variations.map((variation) => ({
                  key: `variation-${variation.id}`,
                  id: variation.id,
                  label: variation.label,
                  vendor_price: variation.vendor_price,
                  dimension_length: variation.dimension_length?.toString() ?? '',
                  dimension_width: variation.dimension_width?.toString() ?? '',
                  dimension_height: variation.dimension_height?.toString() ?? '',
              }))
            : [{ key: variationKey(), id: null, label: 'Standard', vendor_price: product.vendor_price, dimension_length: '', dimension_width: '', dimension_height: '' }],
    );
    const [discountPercentage, setDiscountPercentage] = useState(product.discount_percentage ?? '');
    const vendorPrice = variations[0]?.vendor_price ?? '';

    const updateVariation = (index: number, field: keyof Omit<VariationDraft, 'key' | 'id'>, value: string) => {
        setVariations((current) => current.map((variation, variationIndex) => (variationIndex === index ? { ...variation, [field]: value } : variation)));
    };

    const addVariation = () => {
        setVariations((current) => [...current, { key: variationKey(), id: null, label: '', vendor_price: '', dimension_length: '', dimension_width: '', dimension_height: '' }]);
    };

    const removeVariation = (index: number) => {
        setVariations((current) => (current.length === 1 ? current : current.filter((_, variationIndex) => variationIndex !== index)));
    };

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
            <Head title="Edit Product">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600" rel="stylesheet" />
            </Head>
            <PublicSiteLayout
                canRegister={false}
                headerActions={
                    <div className="flex gap-2">
                        <Link
                            href={vendorProductsIndex().url}
                            className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                        >
                            Back to Products
                        </Link>
                        <Link
                            href={dashboard()}
                            className="rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                        >
                            Dashboard
                        </Link>
                    </div>
                }
            >
                <section className="relative z-10 mx-auto grid w-full max-w-7xl gap-8 px-6 pt-4 pb-16 lg:grid-cols-[0.8fr_1.2fr]">
                    <div className="space-y-5">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Product Atelier</p>
                        <h1 className="font-['Playfair_Display',serif] text-3xl leading-tight md:text-4xl">Edit your heritage listing.</h1>
                        <p className="max-w-lg text-sm text-(--welcome-body-text)">Update dossier details and pricing while preserving LoomCraft's fixed commission model.</p>
                        <div className="grid gap-3 sm:grid-cols-2">
                            <div className="rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Vendor Atelier</p>
                                <p className="mt-2 text-sm text-(--welcome-body-text)">
                                    {vendor_name && vendor_slug ? <Link href={vendorShow(vendor_slug)}>{vendor_name}</Link> : (vendor_name ?? 'LoomCraft artisan')}
                                </p>
                            </div>
                            <div className="rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Commission</p>
                                <p className="mt-2 text-sm text-(--welcome-body-text)">A fixed {commission_rate}% supports platform curation.</p>
                            </div>
                        </div>
                        <div className="grid gap-3 rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Pricing Preview</p>
                            <div className="flex items-center justify-between text-sm text-(--welcome-body-text)">
                                <span>Starting vendor price</span>
                                <span>{vendorPrice ? formatMoney(vendorPrice, base_currency) : '—'}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm text-(--welcome-body-text)">
                                <span>Catalog price</span>
                                <span>{baseSellingPrice === '—' ? '—' : formatMoney(baseSellingPrice, base_currency)}</span>
                            </div>
                            <div className="flex items-center justify-between text-sm text-(--welcome-body-text)">
                                <span>Customer price</span>
                                <span>{discountedSellingPrice === '—' ? '—' : formatMoney(discountedSellingPrice, base_currency)}</span>
                            </div>
                        </div>
                    </div>

                    <div className="relative">
                        <div className="relative rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)] lg:p-8">
                            <div className="space-y-2">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Product dossier</p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl">Edit listing details</h2>
                                <p className="text-sm text-(--welcome-body-text)">Update production context and pricing details.</p>
                            </div>

                            {status && (
                                <div className="mt-4 rounded-3xl border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-muted-text)">
                                    {status}
                                </div>
                            )}

                            <div className="mt-6 grid gap-4 rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                                <p className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Product images</p>
                                {product.images.length === 0 ? (
                                    <p className="text-sm text-(--welcome-body-text)">No product images yet.</p>
                                ) : (
                                    <div className="grid gap-3 sm:grid-cols-2">
                                        {product.images.map((image) => (
                                            <div key={image.id} className="rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-3">
                                                <img src={image.url} alt="Product" className="h-32 w-full rounded-2xl object-cover" />
                                                <Form
                                                    {...destroy.form({
                                                        product: product.id,
                                                        image: image.id,
                                                    })}
                                                    className="mt-3"
                                                    disableWhileProcessing
                                                >
                                                    {({ processing }) => (
                                                        <button
                                                            type="submit"
                                                            className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                                            disabled={processing}
                                                        >
                                                            {processing && <Spinner />}
                                                            Delete image
                                                        </button>
                                                    )}
                                                </Form>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <Form {...store.form(product.id)} className="grid gap-3" encType="multipart/form-data" disableWhileProcessing>
                                    {({ processing, errors }) => (
                                        <>
                                            <input type="file" name="images[]" accept=".jpg,.jpeg,.png,.webp,.gif" multiple className={fileInputClassName} required />
                                            <InputError message={errors.images} className="text-xs" />
                                            <InputError message={errors['images.0']} className="text-xs" />
                                            <button
                                                type="submit"
                                                className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                                disabled={processing}
                                            >
                                                {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                                Upload images
                                            </button>
                                        </>
                                    )}
                                </Form>
                            </div>

                            <Form {...update.form(product.id)} className="mt-6 grid gap-5 lg:grid-cols-2">
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-2 lg:col-span-2">
                                            <div className="grid gap-5 lg:grid-cols-3">
                                                <div className="grid gap-2">
                                                    <label htmlFor="product_code" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                        Product code
                                                    </label>
                                                    <input
                                                        id="product_code"
                                                        type="text"
                                                        name="product_code"
                                                        defaultValue={product.product_code}
                                                        className={inputClassName}
                                                        required
                                                    />
                                                    <InputError message={errors.product_code} className="text-xs" />
                                                </div>
                                                <div className="grid gap-2 lg:col-span-2">
                                                    <label htmlFor="name" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                        Product name
                                                    </label>
                                                    <input id="name" type="text" name="name" defaultValue={product.name} className={inputClassName} required />
                                                    <InputError message={errors.name} className="text-xs" />
                                                </div>
                                                <div className="grid gap-5 lg:col-span-3 lg:grid-cols-2">
                                                    <input type="hidden" name="vendor_price" value={vendorPrice} />
                                                    <div className="grid gap-2">
                                                        <label
                                                            htmlFor="discount_percentage"
                                                            className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase"
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
                                                            className={inputClassName}
                                                        />
                                                        <InputError message={errors.discount_percentage} className="text-xs" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="grid gap-3 lg:col-span-2">
                                            <div className="flex flex-wrap items-center justify-between gap-3">
                                                <p className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Sizes and prices</p>
                                                <button
                                                    type="button"
                                                    onClick={addVariation}
                                                    className="inline-flex items-center justify-center rounded-full border border-(--welcome-strong) p-2 text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                                    title="Add size"
                                                >
                                                    <Plus className="size-4" />
                                                </button>
                                            </div>
                                            <div className="grid gap-3">
                                                {variations.map((variation, index) => (
                                                    <div
                                                        key={variation.key}
                                                        className="grid gap-3 rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-2) p-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_0.8fr_0.8fr_0.8fr_auto]"
                                                    >
                                                        {variation.id !== null && <input type="hidden" name={`variations[${index}][id]`} value={variation.id} />}
                                                        <div className="grid gap-2">
                                                            <label
                                                                htmlFor={`variation_label_${index}`}
                                                                className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                                                            >
                                                                Size
                                                            </label>
                                                            <input
                                                                id={`variation_label_${index}`}
                                                                type="text"
                                                                name={`variations[${index}][label]`}
                                                                value={variation.label}
                                                                onChange={(event) => updateVariation(index, 'label', event.target.value)}
                                                                className={inputClassName}
                                                                required
                                                            />
                                                            <InputError message={errors[`variations.${index}.label`]} className="text-xs" />
                                                        </div>
                                                        <div className="grid gap-2">
                                                            <label
                                                                htmlFor={`variation_vendor_price_${index}`}
                                                                className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                                                            >
                                                                Vendor price
                                                            </label>
                                                            <input
                                                                id={`variation_vendor_price_${index}`}
                                                                type="number"
                                                                name={`variations[${index}][vendor_price]`}
                                                                step="0.01"
                                                                min="0"
                                                                value={variation.vendor_price}
                                                                onChange={(event) => updateVariation(index, 'vendor_price', event.target.value)}
                                                                className={inputClassName}
                                                                required
                                                            />
                                                            <InputError message={errors[`variations.${index}.vendor_price`]} className="text-xs" />
                                                        </div>
                                                        <div className="grid gap-2">
                                                            <label
                                                                htmlFor={`variation_dimension_length_${index}`}
                                                                className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                                                            >
                                                                Length
                                                            </label>
                                                            <input
                                                                id={`variation_dimension_length_${index}`}
                                                                type="number"
                                                                name={`variations[${index}][dimension_length]`}
                                                                step="0.01"
                                                                min="0"
                                                                value={variation.dimension_length}
                                                                onChange={(event) => updateVariation(index, 'dimension_length', event.target.value)}
                                                                className={inputClassName}
                                                            />
                                                            <InputError message={errors[`variations.${index}.dimension_length`]} className="text-xs" />
                                                        </div>
                                                        <div className="grid gap-2">
                                                            <label
                                                                htmlFor={`variation_dimension_width_${index}`}
                                                                className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                                                            >
                                                                Width
                                                            </label>
                                                            <input
                                                                id={`variation_dimension_width_${index}`}
                                                                type="number"
                                                                name={`variations[${index}][dimension_width]`}
                                                                step="0.01"
                                                                min="0"
                                                                value={variation.dimension_width}
                                                                onChange={(event) => updateVariation(index, 'dimension_width', event.target.value)}
                                                                className={inputClassName}
                                                            />
                                                            <InputError message={errors[`variations.${index}.dimension_width`]} className="text-xs" />
                                                        </div>
                                                        <div className="grid gap-2">
                                                            <label
                                                                htmlFor={`variation_dimension_height_${index}`}
                                                                className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                                                            >
                                                                Height
                                                            </label>
                                                            <input
                                                                id={`variation_dimension_height_${index}`}
                                                                type="number"
                                                                name={`variations[${index}][dimension_height]`}
                                                                step="0.01"
                                                                min="0"
                                                                value={variation.dimension_height}
                                                                onChange={(event) => updateVariation(index, 'dimension_height', event.target.value)}
                                                                className={inputClassName}
                                                            />
                                                            <InputError message={errors[`variations.${index}.dimension_height`]} className="text-xs" />
                                                        </div>
                                                        <button
                                                            type="button"
                                                            onClick={() => removeVariation(index)}
                                                            disabled={variations.length === 1}
                                                            className="inline-flex size-11 items-center justify-center self-end rounded-full border border-(--welcome-muted-text) text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-40"
                                                            title="Remove size"
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </button>
                                                    </div>
                                                ))}
                                            </div>
                                            <InputError message={errors.variations} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2 lg:col-span-2">
                                            <label htmlFor="description" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                Description
                                            </label>
                                            <textarea id="description" name="description" rows={5} defaultValue={product.description} className={textAreaClassName} required />
                                            <InputError message={errors.description} className="text-xs" />
                                        </div>

                                        <div className="grid gap-2 lg:col-span-2">
                                            <p className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Categories</p>
                                            <div className="grid gap-2 rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-2) p-4">
                                                {categories.length === 0 ? (
                                                    <p className="text-xs text-(--welcome-muted-text)">No active categories are available yet.</p>
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
                                                                    defaultChecked={product.category_ids.includes(category.id)}
                                                                />
                                                                {category.name}
                                                            </label>
                                                        ))}
                                                    </div>
                                                )}
                                            </div>
                                            <InputError message={errors.category_ids} className="text-xs" />
                                            <InputError message={errors['category_ids.0']} className="text-xs" />
                                        </div>

                                        <div className="lg:col-span-2">
                                            <ProductColorSelector
                                                colors={colors}
                                                selectedColorIds={product.color_ids}
                                                errorMessage={errors.color_ids}
                                                itemErrorMessage={errors['color_ids.0']}
                                            />
                                        </div>

                                        <div className="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                                            <div className="grid gap-2">
                                                <label htmlFor="materials" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Materials
                                                </label>
                                                <input id="materials" type="text" name="materials" defaultValue={product.materials ?? ''} className={inputClassName} />
                                                <InputError message={errors.materials} className="text-xs" />
                                            </div>
                                            <div className="grid gap-2">
                                                <label htmlFor="pieces_count" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Pieces produced
                                                </label>
                                                <input
                                                    id="pieces_count"
                                                    type="number"
                                                    name="pieces_count"
                                                    min="1"
                                                    defaultValue={product.pieces_count ?? ''}
                                                    className={inputClassName}
                                                />
                                                <InputError message={errors.pieces_count} className="text-xs" />
                                            </div>
                                        </div>

                                        <div className="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                                            <div className="grid gap-2">
                                                <label htmlFor="production_time_days" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Production days
                                                </label>
                                                <input
                                                    id="production_time_days"
                                                    type="number"
                                                    name="production_time_days"
                                                    min="1"
                                                    defaultValue={product.production_time_days ?? ''}
                                                    className={inputClassName}
                                                />
                                                <InputError message={errors.production_time_days} className="text-xs" />
                                            </div>
                                            {site.key === 'naturesnature' && (
                                                <div className="grid gap-2">
                                                    <label htmlFor="expiry_information" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                        Best before / expiry
                                                    </label>
                                                    <input
                                                        id="expiry_information"
                                                        type="text"
                                                        name="expiry_information"
                                                        defaultValue={product.expiry_information ?? ''}
                                                        placeholder="3 months after manufacture or 2026-12-01"
                                                        className={inputClassName}
                                                    />
                                                    <InputError message={errors.expiry_information} className="text-xs" />
                                                </div>
                                            )}
                                            <div className="grid gap-2">
                                                <label htmlFor="dimension_unit" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Dimension unit
                                                </label>
                                                <input
                                                    id="dimension_unit"
                                                    type="text"
                                                    name="dimension_unit"
                                                    defaultValue={product.dimension_unit ?? ''}
                                                    className={inputClassName}
                                                />
                                                <InputError message={errors.dimension_unit} className="text-xs" />
                                            </div>
                                        </div>

                                        <button
                                            type="submit"
                                            className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-6 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70 lg:col-span-2"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                            Save Product
                                        </button>
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
