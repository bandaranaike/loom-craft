import { Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import ProductCard, { type ProductCardItem } from '@/components/product-card';
import VendorInquiryForm from '@/components/vendor-inquiry-form';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { show as vendorShow } from '@/routes/vendors';

type CategorySummary = {
    id: number;
    name: string;
    slug: string;
    count: number;
};

type LocationItem = {
    id: number;
    location_name: string;
    address_line_1: string;
    address_line_2: string | null;
    city: string;
    region: string | null;
    postal_code: string | null;
    country: string;
    phone: string | null;
    hours: string | null;
    map_url: string | null;
    is_primary: boolean;
};

type VendorData = {
    id: number;
    display_name: string;
    slug: string;
    tagline: string | null;
    bio: string | null;
    about_title: string | null;
    website_url: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    whatsapp_number: string | null;
    location: string | null;
    years_active: number | null;
    craft_specialties: string[];
    is_contact_public: boolean;
    is_website_public: boolean;
    logo_url: string | null;
    cover_image_url: string | null;
    locations: LocationItem[];
};

type Props = {
    vendor: VendorData;
    products: ProductCardItem[];
    categories: CategorySummary[];
    status?: string;
};

function mapEmbedUrl(mapUrl: string | null): string | null {
    if (!mapUrl) {
        return null;
    }

    if (mapUrl.includes('/maps/embed')) {
        return mapUrl;
    }

    return null;
}

export default function VendorShow() {
    const { vendor, products, categories, status } = usePage<Props>().props;
    const [selectedCategory, setSelectedCategory] = useState<string>('all');
    const pageUrl = vendorShow(vendor.slug).url;
    const metaDescription = vendor.bio ?? vendor.tagline ?? `${vendor.display_name} on LoomCraft.`;
    const socialImage = vendor.logo_url ?? vendor.cover_image_url;

    const filteredProducts = useMemo(() => {
        if (selectedCategory === 'all') {
            return products;
        }

        return products.filter((product) =>
            (product.categories ?? []).some((category) => category.slug === selectedCategory),
        );
    }, [products, selectedCategory]);

    return (
        <>
            <Head title={`${vendor.display_name} | LoomCraft`}>
                <meta name="description" content={metaDescription} />
                <meta
                    property="og:title"
                    content={`${vendor.display_name} | LoomCraft`}
                />
                <meta property="og:description" content={metaDescription} />
                {socialImage && (
                    <meta property="og:image" content={socialImage} />
                )}
                <link rel="canonical" href={pageUrl} />
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>

            <PublicSiteLayout canRegister={false}>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pt-6 pb-16 lg:grid-cols-[1.1fr_0.9fr]">
                    <div className="space-y-6">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Artisan Storefront
                        </div>
                        <div className="min-w-0">
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl lg:text-6xl">
                                {vendor.display_name}
                            </h1>
                            <p className="mt-3 max-w-2xl text-base text-(--welcome-body-text) md:text-lg">
                                {vendor.tagline ??
                                    'Handmade textiles and heritage craft from a LoomCraft vendor.'}
                            </p>
                        </div>
                        <p className="max-w-3xl text-sm leading-7 text-(--welcome-body-text) md:text-base">
                            {vendor.bio ??
                                'This vendor has not added their full studio story yet.'}
                        </p>
                        <div className="flex flex-wrap items-center gap-4">
                            <a
                                href="#contact-us"
                                className="rounded-full border border-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                            >
                                Contact Vendor
                            </a>
                            {vendor.website_url && (
                                <a
                                    href={vendor.website_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="rounded-full bg-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover)"
                                >
                                    Visit Website
                                </a>
                            )}
                        </div>
                        <div className="flex flex-wrap items-center gap-6 text-xs tracking-[0.24em] text-(--welcome-muted-text) uppercase">
                            {vendor.location && <span>{vendor.location}</span>}
                            {vendor.years_active !== null && (
                                <span>{vendor.years_active} Years Active</span>
                            )}
                            <span>
                                {vendor.is_contact_public
                                    ? 'Direct Contact Visible'
                                    : 'Inquiry First Contact'}
                            </span>
                        </div>
                    </div>

                    <div className="relative hidden md:block">
                        <div className="relative grid gap-6 rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <div className="flex items-center justify-between">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Storefront Details
                                </p>
                                <span className="rounded-full bg-(--welcome-strong) px-3 py-1 text-xs font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase">
                                    Live
                                </span>
                            </div>
                            <div className="flex items-center gap-4 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-2) p-4">
                                {vendor.logo_url ? (
                                    <img
                                        src={vendor.logo_url}
                                        alt={`${vendor.display_name} logo`}
                                        className="h-18 w-auto shrink-0 object-cover"
                                    />
                                ) : (
                                    <div className="flex h-18 w-18 shrink-0 items-center justify-center rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) text-4xl tracking-tighter text-(--welcome-muted-text) uppercase">
                                        {vendor.display_name
                                            .split(/\s+/)
                                            .filter((word) => word.length > 0)
                                            .map((word) => word[0])
                                            .slice(0, 2)
                                            .join('')
                                            .toUpperCase()}
                                    </div>
                                )}
                                <div className="min-w-0">
                                    <p className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        Vendor Mark
                                    </p>
                                    <p className="font-['Playfair_Display',serif] text-2xl">
                                        {vendor.display_name}
                                    </p>
                                </div>
                            </div>
                            <dl className="grid gap-4 text-sm text-(--welcome-body-text)">
                                <div className="flex items-start justify-between gap-4 border-b border-(--welcome-border-soft) pb-3">
                                    <dt className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        Email
                                    </dt>
                                    <dd className="text-right wrap-break-word">
                                        {vendor.contact_email ??
                                            'Available on request'}
                                    </dd>
                                </div>
                                <div className="flex items-start justify-between gap-4 border-b border-(--welcome-border-soft) pb-3">
                                    <dt className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        Phone
                                    </dt>
                                    <dd className="text-right">
                                        {vendor.contact_phone ??
                                            'Available on request'}
                                    </dd>
                                </div>
                                <div className="flex items-start justify-between gap-4 border-b border-(--welcome-border-soft) pb-3">
                                    <dt className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        WhatsApp
                                    </dt>
                                    <dd className="text-right">
                                        {vendor.whatsapp_number ??
                                            'Available on request'}
                                    </dd>
                                </div>
                                <div className="flex items-start justify-between gap-4">
                                    <dt className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        Website
                                    </dt>
                                    <dd className="text-right wrap-break-word">
                                        {vendor.website_url ??
                                            'Not publicly linked'}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Products
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                                Crafted collection
                            </h2>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={() => setSelectedCategory('all')}
                                className={`rounded-full border px-3 py-1 text-xs tracking-[0.2em] uppercase ${
                                    selectedCategory === 'all'
                                        ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                        : 'border-(--welcome-border) text-(--welcome-muted-text)'
                                }`}
                            >
                                All ({products.length})
                            </button>
                            {categories.map((category) => (
                                <button
                                    key={category.id}
                                    type="button"
                                    onClick={() =>
                                        setSelectedCategory(category.slug)
                                    }
                                    className={`rounded-full border px-3 py-1 text-xs tracking-[0.2em] uppercase ${
                                        selectedCategory === category.slug
                                            ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                            : 'border-(--welcome-border) text-(--welcome-muted-text)'
                                    }`}
                                >
                                    {category.name} ({category.count})
                                </button>
                            ))}
                        </div>
                    </div>

                    {filteredProducts.length === 0 ? (
                        <div className="rounded-4xl border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-8 text-sm text-(--welcome-muted-text)">
                            No products available for this category yet.
                        </div>
                    ) : (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {filteredProducts.map((product) => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                />
                            ))}
                        </div>
                    )}
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="grid gap-8 rounded-[48px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-10 lg:grid-cols-3">
                        <div className="space-y-3 border-b border-(--welcome-border-soft) pb-6 lg:border-r lg:border-b-0 lg:pr-8 lg:pb-0">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Craft Focus
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Heritage specialties
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                {vendor.craft_specialties.length > 0
                                    ? vendor.craft_specialties.join(', ')
                                    : 'Heritage textile work curated through LoomCraft.'}
                            </p>
                        </div>

                        <div className="space-y-3 border-b border-(--welcome-border-soft) pb-6 lg:border-r lg:border-b-0 lg:px-8 lg:pb-0">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Website Access
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                External studio presence
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                {vendor.website_url
                                    ? 'This vendor links out to an external studio website for deeper browsing.'
                                    : 'This storefront is currently the vendor’s primary public destination.'}
                            </p>
                        </div>

                        <div className="space-y-3 lg:pl-8">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                {vendor.about_title ?? 'Studio Notes'}
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl">
                                Storefront notes
                            </h2>
                            <div className="space-y-3 text-sm text-(--welcome-body-text)">
                                <p>
                                    {vendor.location ??
                                        'Location is shared through atelier visits and inquiries.'}
                                </p>
                                <p>
                                    {vendor.is_contact_public
                                        ? 'Direct contact details are publicly visible on this storefront.'
                                        : 'Contact details are hidden and conversations start through the inquiry form.'}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 pb-20 lg:grid-cols-[1fr_1fr]">
                    <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-8">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Store Locations
                        </p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">
                            Visit the atelier
                        </h2>

                        {vendor.locations.length === 0 ? (
                            <p className="mt-4 text-sm text-(--welcome-body-text)">
                                No store locations provided yet.
                            </p>
                        ) : (
                            <div className="mt-6 space-y-6">
                                {vendor.locations.map((location) => {
                                    const embedUrl = mapEmbedUrl(
                                        location.map_url,
                                    );

                                    return (
                                        <div
                                            key={location.id}
                                            className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-5"
                                        >
                                            <p className="font-semibold text-(--welcome-strong)">
                                                {location.location_name}
                                                {location.is_primary && (
                                                    <span className="ml-2 text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                                        Primary
                                                    </span>
                                                )}
                                            </p>
                                            <div className="mt-3 space-y-1 text-sm text-(--welcome-body-text)">
                                                <p>
                                                    {location.address_line_1}
                                                    {location.address_line_2
                                                        ? `, ${location.address_line_2}`
                                                        : ''}
                                                </p>
                                                <p>
                                                    {location.city}
                                                    {location.region
                                                        ? `, ${location.region}`
                                                        : ''}
                                                    {location.postal_code
                                                        ? ` ${location.postal_code}`
                                                        : ''}
                                                    , {location.country}
                                                </p>
                                                {location.phone && (
                                                    <p>
                                                        Phone: {location.phone}
                                                    </p>
                                                )}
                                                {location.hours && (
                                                    <p>
                                                        Hours: {location.hours}
                                                    </p>
                                                )}
                                            </div>
                                            {embedUrl ? (
                                                <iframe
                                                    title={`${location.location_name} map`}
                                                    src={embedUrl}
                                                    loading="lazy"
                                                    className="mt-4 h-56 w-full rounded-[20px] border border-(--welcome-border-soft)"
                                                />
                                            ) : location.map_url ? (
                                                <a
                                                    href={location.map_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="mt-4 inline-flex text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase underline"
                                                >
                                                    Open in Google Maps
                                                </a>
                                            ) : null}
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    <div
                        id="contact-us"
                        className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-8"
                    >
                        <VendorInquiryForm
                            vendorSlug={vendor.slug}
                            contactEmail={vendor.contact_email}
                            contactPhone={vendor.contact_phone}
                            whatsappNumber={vendor.whatsapp_number}
                            status={status}
                        />
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
