import { Head, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import ProductCard, { type ProductCardItem } from '@/components/product-card';
import VendorInquiryForm from '@/components/vendor-inquiry-form';
import PublicSiteLayout from '@/layouts/public-site-layout';

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

export default function VendorShow() {
    const { vendor, products, categories, status } = usePage<Props>().props;
    const [selectedCategory, setSelectedCategory] = useState<string>('all');

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
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>

            <PublicSiteLayout canRegister={false}>
                <section
                    className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-6 lg:grid-cols-[1.1fr_0.9fr]"
                    style={
                        vendor.cover_image_url
                            ? {
                                  backgroundImage: `linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), url(${vendor.cover_image_url})`,
                                  backgroundSize: 'cover',
                                  backgroundPosition: 'center',
                              }
                            : undefined
                    }
                >
                    <div className="space-y-6">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Artisan Storefront
                        </div>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl lg:text-6xl">
                            {vendor.display_name}
                        </h1>
                        <p className="max-w-2xl text-base text-(--welcome-body-text) md:text-lg">
                            {vendor.tagline ?? 'Handmade textiles and heritage craft from a verified LoomCraft vendor.'}
                        </p>
                        {vendor.location && (
                            <p className="text-xs uppercase tracking-[0.24em] text-(--welcome-muted-text)">
                                {vendor.location}
                            </p>
                        )}
                        <div className="flex flex-wrap gap-3">
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
                    </div>

                    <div className="relative">
                        <div className="relative grid gap-6 rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <div className="flex items-center justify-between">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Vendor Highlights
                                </p>
                                <span className="rounded-full bg-(--welcome-strong) px-3 py-1 text-xs font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase">
                                    Live
                                </span>
                            </div>
                            <div className="space-y-3">
                                <p className="text-sm text-(--welcome-body-text)">
                                    {vendor.about_title ?? 'Our weaving story'}
                                </p>
                                <p className="text-sm text-(--welcome-body-text)">
                                    {vendor.bio ?? 'This vendor has not added their full story yet.'}
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {vendor.years_active && (
                                    <span className="rounded-full border border-(--welcome-border) px-3 py-1 text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                        {vendor.years_active}+ years
                                    </span>
                                )}
                                {vendor.craft_specialties.map((specialty) => (
                                    <span
                                        key={specialty}
                                        className="rounded-full border border-(--welcome-border) px-3 py-1 text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)"
                                    >
                                        {specialty}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Products</p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                                Crafted collection
                            </h2>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={() => setSelectedCategory('all')}
                                className={`rounded-full border px-3 py-1 text-xs uppercase tracking-[0.2em] ${
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
                                    onClick={() => setSelectedCategory(category.slug)}
                                    className={`rounded-full border px-3 py-1 text-xs uppercase tracking-[0.2em] ${
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
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>
                    )}
                </section>

                <section id="contact-us" className="mx-auto grid w-full max-w-6xl gap-10 px-6 pb-20 lg:grid-cols-[1fr_1fr]">
                    <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-8">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Store Locations</p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">Visit our atelier</h2>

                        {vendor.locations.length === 0 ? (
                            <p className="mt-4 text-sm text-(--welcome-body-text)">No store locations provided yet.</p>
                        ) : (
                            <div className="mt-5 space-y-4 text-sm text-(--welcome-body-text)">
                                {vendor.locations.map((location) => (
                                    <div key={location.id}>
                                        <p className="font-semibold text-(--welcome-strong)">
                                            {location.location_name}
                                            {location.is_primary && (
                                                <span className="ml-2 text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                                    Primary
                                                </span>
                                            )}
                                        </p>
                                        <p>
                                            {location.address_line_1}
                                            {location.address_line_2 ? `, ${location.address_line_2}` : ''}
                                        </p>
                                        <p>
                                            {location.city}
                                            {location.region ? `, ${location.region}` : ''}
                                            {location.postal_code ? ` ${location.postal_code}` : ''}, {location.country}
                                        </p>
                                        {location.phone && <p>Phone: {location.phone}</p>}
                                        {location.hours && <p>Hours: {location.hours}</p>}
                                        {location.map_url && (
                                            <a
                                                href={location.map_url}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="inline-flex text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase underline"
                                            >
                                                Open map
                                            </a>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-8">
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
