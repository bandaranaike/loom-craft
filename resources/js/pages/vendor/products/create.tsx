import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes';
import { store } from '@/routes/vendor/products';

type Props = {
    commission_rate: string;
    vendor_name: string | null;
    status?: string;
};

const inputClassName =
    'w-full rounded-full border border-[#d4b28c] bg-[#fdf8f0] px-4 py-3 text-sm text-[#2b241c] placeholder:text-[#7a5a3a]/70 shadow-[0_8px_20px_-18px_rgba(43,36,28,0.7)] focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20';

const textAreaClassName =
    'w-full rounded-[24px] border border-[#d4b28c] bg-[#fdf8f0] px-4 py-3 text-sm text-[#2b241c] placeholder:text-[#7a5a3a]/70 shadow-[0_8px_20px_-18px_rgba(43,36,28,0.7)] focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20';

const fileInputClassName =
    'w-full rounded-[24px] border border-[#d4b28c] bg-[#fdf8f0] px-4 py-3 text-sm text-[#2b241c] shadow-[0_8px_20px_-18px_rgba(43,36,28,0.7)] file:mr-4 file:rounded-full file:border-0 file:bg-[#2b241c] file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-[0.3em] file:text-[#f6f1e8] hover:file:bg-[#3a2f25] focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20';

export default function ProductCreate() {
    const { commission_rate, vendor_name, status } = usePage<Props>().props;
    const [vendorPrice, setVendorPrice] = useState('');

    const sellingPrice = useMemo(() => {
        const parsed = Number.parseFloat(vendorPrice);
        if (!vendorPrice || Number.isNaN(parsed)) {
            return '—';
        }

        const rate = Number.parseFloat(commission_rate || '7');
        return (parsed * (1 + rate / 100)).toFixed(2);
    }, [commission_rate, vendorPrice]);

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
                        className="rounded-full border border-[#2b241c] px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                    >
                        Back to Dashboard
                    </Link>
                }
            >
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-4 lg:grid-cols-[1.05fr_0.95fr]">
                        <div className="space-y-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Product Atelier
                            </p>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                Register a new heritage piece for review.
                            </h1>
                            <p className="max-w-xl text-sm text-[#5a4a3a] md:text-base">
                                Share the provenance, pricing, and materials for each piece.
                                Approved listings are curated for collectors worldwide.
                            </p>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Vendor Atelier
                                    </p>
                                    <p className="mt-3 text-sm text-[#5a4a3a]">
                                        {vendor_name ?? 'LoomCraft artisan'}
                                    </p>
                                </div>
                                <div className="rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Commission
                                    </p>
                                    <p className="mt-3 text-sm text-[#5a4a3a]">
                                        A fixed {commission_rate}% supports platform curation.
                                    </p>
                                </div>
                            </div>
                            <div className="grid gap-3 rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] p-5">
                                <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    Pricing Preview
                                </p>
                                <div className="flex items-center justify-between text-sm text-[#5a4a3a]">
                                    <span>Vendor price</span>
                                    <span>
                                        {vendorPrice ? `$${vendorPrice}` : '—'}
                                    </span>
                                </div>
                                <div className="flex items-center justify-between text-sm text-[#5a4a3a]">
                                    <span>Selling price</span>
                                    <span>
                                        {sellingPrice === '—'
                                            ? '—'
                                            : `$${sellingPrice}`}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="relative">
                            <div className="absolute -right-4 top-10 h-48 w-48 rounded-[32px] border border-[#d4b28c] bg-[#fdf8f0] shadow-[0_20px_60px_-30px_rgba(43,36,28,0.45)]" />
                            <div className="relative rounded-[36px] border border-[#d4b28c] bg-[#f9efe2] p-8 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.6)]">
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Product dossier
                                    </p>
                                    <h2 className="font-['Playfair_Display',serif] text-2xl">
                                        Listing details
                                    </h2>
                                    <p className="text-sm text-[#5a4a3a]">
                                        Provide full production context and media references.
                                    </p>
                                </div>

                                {status && (
                                    <div className="mt-4 rounded-[24px] border border-[#b6623a]/40 bg-[#fff8ed] px-4 py-3 text-sm text-[#7a5a3a]">
                                        {status}
                                    </div>
                                )}

                                <Form
                                    {...store.form()}
                                    className="mt-6 grid gap-5"
                                    encType="multipart/form-data"
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="name"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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

                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="description"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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

                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="vendor_price"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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

                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="materials"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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

                                            <div className="grid gap-4 sm:grid-cols-2">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="production_time_days"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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

                                            <div className="grid gap-4 sm:grid-cols-3">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor="dimension_length"
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
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

                                            <div className="grid gap-3">
                                                <p className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                    Product images
                                                </p>
                                                <p className="text-xs text-[#7a5a3a]/80">
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

                                            <div className="grid gap-2">
                                                <label
                                                    htmlFor="video"
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
                                                >
                                                    Optional video
                                                </label>
                                                <p className="text-xs text-[#7a5a3a]/80">
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
                                                className="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#2b241c] px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-[#f6f1e8] transition hover:-translate-y-0.5 hover:bg-[#3a2f25] disabled:cursor-not-allowed disabled:opacity-70"
                                                disabled={processing}
                                            >
                                                {processing && (
                                                    <Spinner className="text-[#f6f1e8]" />
                                                )}
                                                Submit for Review
                                            </button>
                                            <p className="text-center text-xs uppercase tracking-[0.25em] text-[#7a5a3a]">
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
