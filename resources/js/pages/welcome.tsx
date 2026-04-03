import { Head, Link, usePage } from '@inertiajs/react';
import ProductCard, { type ProductCardItem } from '@/components/product-card';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { dashboard, register } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import type { SharedData } from '@/types';

const highlights = [
    {
        title: 'Dumbara Rataa Heritage',
        description:
            'Time-honored motifs, hand-loomed by master artisans with lineage-backed techniques.',
    },
    {
        title: 'Direct Artisan Market',
        description:
            'Vendors present original work directly to patrons through a curated marketplace.',
    },
    {
        title: 'Collector-Grade Craft',
        description:
            'Limited runs, precise dimensions, and provenance notes for each piece.',
    },
];

const collections = [
    {
        name: 'Ceremonial Weaves',
        note: 'Rich dyes, layered texture, heirloom-grade threads.',
    },
    {
        name: 'Heritage Loom Sets',
        note: 'Curated bundles for modern interiors with classic motifs.',
    },
    {
        name: 'Artisan Spotlight',
        note: 'Signature pieces, signed and dated by the maker.',
    },
];

export default function Welcome({
    canRegister = true,
    latest_products,
}: {
    canRegister?: boolean;
    latest_products: ProductCardItem[];
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="LoomCraft — Heritage Woven Luxury">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="relative z-10 mx-auto w-full max-w-6xl px-6 pt-4 pb-10 md:pt-6 md:pb-16">
                    <div className="space-y-5 lg:max-w-5xl">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Handloomed • Curated • Collectible
                        </div>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl lg:text-6xl">
                            A marketplace devoted to Sri Lanka&apos;s most rare
                            woven luxury.
                        </h1>
                        <p className="text-base text-(--welcome-body-text) md:text-lg">
                            LoomCraft brings Dumbara Rataa artisans into a
                            refined global atelier. Each piece is crafted in
                            limited numbers with detailed provenance, authentic
                            materials, and a heritage-first story.
                        </p>
                        <div className="flex flex-wrap items-center gap-4">
                            <Link
                                href={productsIndex().url}
                                className="rounded-full border border-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                            >
                                Browse Products
                            </Link>
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="rounded-full bg-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover)"
                                >
                                    Visit Dashboard
                                </Link>
                            ) : (
                                <>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="rounded-full bg-(--welcome-strong) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong-hover)"
                                        >
                                            Start Collecting
                                        </Link>
                                    )}
                                </>
                            )}
                        </div>
                        <div className="flex flex-wrap items-center gap-6 text-xs tracking-[0.24em] text-(--welcome-muted-text) uppercase">
                            <span>Collector-Safe Checkout</span>
                            <span>Traceable Artisan Provenance</span>
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-14 md:pb-16">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                New Arrivals
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                                Shop the latest atelier pieces first.
                            </h2>
                        </div>
                        <Link
                            href={productsIndex().url}
                            className="rounded-full border border-(--welcome-strong) px-5 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                        >
                            View all products
                        </Link>
                    </div>
                    {latest_products.length === 0 ? (
                        <div className="rounded-4xl border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-8 text-sm text-(--welcome-muted-text)">
                            No products available yet.
                        </div>
                    ) : (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {latest_products.map((product) => (
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>
                    )}
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="grid gap-8 rounded-[48px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-10 md:grid-cols-3">
                        {highlights.map((item) => (
                            <div
                                key={item.title}
                                className="space-y-3 border-b border-(--welcome-border-soft) pb-6 last:border-b-0 last:pb-0 md:border-r md:border-b-0 md:pr-8 md:pb-0 md:last:border-r-0 md:last:pr-0"
                            >
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Signature
                                </p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl">
                                    {item.title}
                                </h2>
                                <p className="text-sm text-(--welcome-body-text)">
                                    {item.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 lg:grid-cols-[0.9fr_1.1fr]">
                    <div className="space-y-6">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Curated Collections
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                            Designed for galleries, rituals, and refined living.
                        </h2>
                        <p className="text-sm text-(--welcome-body-text)">
                            Every collection is an editorial of texture, story,
                            and provenance — ready for patrons, design studios,
                            and collectors worldwide.
                        </p>
                    </div>
                    <div className="grid gap-4 md:grid-cols-3">
                        {collections.map((collection) => (
                            <div
                                key={collection.name}
                                className="group rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 transition hover:-translate-y-1 hover:border-(--welcome-accent)"
                            >
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Collection
                                </p>
                                <h3 className="mt-3 font-['Playfair_Display',serif] text-xl">
                                    {collection.name}
                                </h3>
                                <p className="mt-2 text-sm text-(--welcome-body-text)">
                                    {collection.note}
                                </p>
                                <div className="mt-6 h-1 w-12 rounded-full bg-(--welcome-accent) transition group-hover:w-20" />
                            </div>
                        ))}
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-8 px-6 pb-20 lg:grid-cols-[1fr_1fr]">
                    <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-strong) p-8 text-(--welcome-on-strong)">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-accent-soft) uppercase">
                            For Vendors
                        </p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">
                            A refined marketplace for master weavers.
                        </h2>
                        <p className="mt-3 text-sm text-(--welcome-on-strong-muted)">
                            Gain access to a curated audience, transparent
                            pricing, and a dashboard built around your craft.
                            Manual approval keeps the marketplace authentic and
                            exclusive.
                        </p>
                        {canRegister && !auth.user && (
                            <div className="mt-6">
                                <Link
                                    href={register()}
                                    className="inline-flex items-center rounded-full border border-(--welcome-on-strong) px-5 py-2 text-xs font-semibold tracking-[0.3em] uppercase transition hover:bg-(--welcome-on-strong) hover:text-(--welcome-strong)"
                                >
                                    Apply as Vendor
                                </Link>
                            </div>
                        )}
                    </div>
                    <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-8">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Patron Notes
                        </p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">
                            "The weave carries the rhythm of the loom itself."
                        </h2>
                        <p className="mt-3 text-sm text-(--welcome-body-text)">
                            Each order arrives with a maker&apos;s note, woven
                            coordinates, and recommended care to preserve its
                            luster for generations.
                        </p>
                        <div className="mt-6 flex items-center gap-4">
                            <div className="h-12 w-12 rounded-full border border-(--welcome-accent) bg-(--welcome-surface-1)" />
                            <div>
                                <p className="text-sm font-semibold text-(--welcome-strong)">
                                    Ishara Karunadasa
                                </p>
                                <p className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                    Curator, LoomCraft
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
