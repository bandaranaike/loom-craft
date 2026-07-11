import { Link, usePage } from '@inertiajs/react';
import { Gift, Leaf, PackageCheck, ShoppingBag, Truck, Utensils } from 'lucide-react';
import ProductCard, { type ProductCardItem } from '@/components/product-card';
import SeoHead from '@/components/seo-head';
import PublicSiteLayout from '@/layouts/public-site-layout';
import categoryCookiesImage from '@/images/naturesnature/category-cookies.png';
import categoryGiftBoxesImage from '@/images/naturesnature/category-gift-boxes.png';
import categoryPantryImage from '@/images/naturesnature/category-pantry.png';
import featureHomemadeBatchesImage from '@/images/naturesnature/feature-homemade-batches.png';
import featurePackedFreshImage from '@/images/naturesnature/feature-packed-fresh.png';
import featureShippingImage from '@/images/naturesnature/feature-shipping.png';
import giftFoodBasketImage from '@/images/naturesnature/gift-food-basket.png';
import giftJackfruitImage from '@/images/naturesnature/gift-jackfruit.png';
import giftPumpkinImage from '@/images/naturesnature/gift-pumpkin.png';
import makerTableImage from '@/images/naturesnature/naturesnature-maker-table.png';
import heroImage from '@/images/naturesnature/naturesnature-hero.png';
import { dashboard, register } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import type { SharedData } from '@/types';

const highlights = [
    {
        title: 'Dumbara Rataa Heritage',
        description: 'Time-honored motifs, hand-loomed by master artisans with lineage-backed techniques.',
    },
    {
        title: 'Direct Artisan Market',
        description: 'Vendors present original work directly to patrons through a curated marketplace.',
    },
    {
        title: 'Collector-Grade Craft',
        description: 'Limited runs, precise dimensions, and provenance notes for each piece.',
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

const foodHighlights = [
    {
        title: 'Small-Batch Cookies',
        description: 'Warm bakes, honest ingredients, and batches made for slow sharing.',
    },
    {
        title: 'Organic Pantry',
        description: 'Curated jars, snacks, and seasonal treats from independent food makers.',
    },
    {
        title: 'Gift-Ready Boxes',
        description: 'Layered food gifts with rich colors, careful packing, and homemade charm.',
    },
];

const categoryFallbacks = [
    {
        name: 'Cookies',
        note: 'Brown-butter, spice, and golden small-batch bakes.',
        icon: Utensils,
        image: categoryCookiesImage,
    },
    {
        name: 'Pantry',
        note: 'Jars, snacks, and seasonal homemade staples.',
        icon: ShoppingBag,
        image: categoryPantryImage,
    },
    {
        name: 'Gift Boxes',
        note: 'Curated bundles packed for celebrations and thank-you notes.',
        icon: Gift,
        image: categoryGiftBoxesImage,
    },
];

const makerProofs = [
    {
        title: 'Home-Made Batches',
        description: 'Made in small runs with simple ingredients and careful timing.',
        icon: Leaf,
    },
    {
        title: 'Packed Fresh',
        description: 'Prepared for gifting, delivery, and clean presentation.',
        icon: PackageCheck,
    },
    {
        title: 'Nationwide Shipping',
        description: 'Ready for customer orders, family boxes, and seasonal drops.',
        icon: Truck,
    },
];

type CategorySection = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    products: ProductCardItem[];
};

export default function Welcome({ canRegister = true, category_sections }: { canRegister?: boolean; category_sections: CategorySection[] }) {
    const { auth, site } = usePage<SharedData>().props;

    if (site.key === 'naturesnature') {
        return <NaturesNatureWelcome canRegister={canRegister} categorySections={category_sections} />;
    }

    return (
        <>
            <SeoHead
                title="LoomCraft — Heritage Woven Luxury"
                description="Handwoven Sri Lankan textiles, curated artisan pieces, and collectible home decor from verified LoomCraft vendors."
                canonical="/"
                schema={{
                    '@context': 'https://schema.org',
                    '@type': 'Organization',
                    name: 'LoomCraft',
                    url: '/',
                }}
            >
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600" rel="stylesheet" />
            </SeoHead>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="relative z-10 mx-auto w-full max-w-6xl px-6 pt-4 pb-10 md:pt-6 md:pb-16">
                    <div className="space-y-5 lg:max-w-3xl">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Handloomed • Curated • Collectible
                        </div>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl lg:text-6xl">
                            A marketplace devoted to Sri Lanka&apos;s most rare woven luxury.
                        </h1>
                        <p className="text-base text-(--welcome-body-text) md:text-lg">
                            LoomCraft brings Dumbara Rataa artisans into a refined global atelier. Each piece is crafted in limited numbers with detailed provenance, authentic
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
                    <div className="mb-10 h-px bg-linear-to-r from-transparent via-(--welcome-border-soft) to-transparent" />

                    {category_sections.length === 0 ? (
                        <div className="rounded-4xl border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-8 text-sm text-(--welcome-muted-text)">
                            No products available yet.
                        </div>
                    ) : (
                        <div>
                            {category_sections.map((category, index) => (
                                <section key={category.id}>
                                    {index > 0 && <div className="my-10 h-px bg-linear-to-r from-transparent via-(--welcome-border-soft) to-transparent" />}
                                    <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                                        <div>
                                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Category</p>
                                            <h3 className="mt-2 font-['Playfair_Display',serif] text-2xl md:text-3xl">{category.name}</h3>
                                            {category.description && <p className="mt-2 max-w-2xl text-sm text-(--welcome-body-text)">{category.description}</p>}
                                        </div>
                                        <Link
                                            href={productsIndex.url({
                                                query: {
                                                    category: category.slug,
                                                    page: 1,
                                                    per_page: 9,
                                                },
                                            })}
                                            className="rounded-full border border-(--welcome-strong) px-5 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                        >
                                            More in {category.name}
                                        </Link>
                                    </div>
                                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                        {category.products.map((product) => (
                                            <ProductCard key={product.id} product={product} />
                                        ))}
                                    </div>
                                </section>
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
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Signature</p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl">{item.title}</h2>
                                <p className="text-sm text-(--welcome-body-text)">{item.description}</p>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 lg:grid-cols-[0.9fr_1.1fr]">
                    <div className="space-y-6">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Curated Collections</p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">Designed for galleries, rituals, and refined living.</h2>
                        <p className="text-sm text-(--welcome-body-text)">
                            Every collection is an editorial of texture, story, and provenance — ready for patrons, design studios, and collectors worldwide.
                        </p>
                    </div>
                    <div className="grid gap-4 md:grid-cols-3">
                        {collections.map((collection) => (
                            <div
                                key={collection.name}
                                className="group rounded-4xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 transition hover:-translate-y-1 hover:border-(--welcome-accent)"
                            >
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Collection</p>
                                <h3 className="mt-3 font-['Playfair_Display',serif] text-xl">{collection.name}</h3>
                                <p className="mt-2 text-sm text-(--welcome-body-text)">{collection.note}</p>
                                <div className="mt-6 h-1 w-12 rounded-full bg-(--welcome-accent) transition group-hover:w-20" />
                            </div>
                        ))}
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-8 px-6 pb-20 lg:grid-cols-[1fr_1fr]">
                    <div className="rounded-[40px] border border-(--welcome-border-soft) bg-(--welcome-strong) p-8 text-(--welcome-on-strong)">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-accent-soft) uppercase">For Vendors</p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">A refined marketplace for master weavers.</h2>
                        <p className="mt-3 text-sm text-(--welcome-on-strong-muted)">
                            Gain access to a curated audience, transparent pricing, and a dashboard built around your craft. Manual approval keeps the marketplace authentic and
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
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Patron Notes</p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">"The weave carries the rhythm of the loom itself."</h2>
                        <p className="mt-3 text-sm text-(--welcome-body-text)">
                            Each order arrives with a maker&apos;s note, woven coordinates, and recommended care to preserve its luster for generations.
                        </p>
                        <div className="mt-6 flex items-center gap-4">
                            <div className="h-12 w-12 rounded-full border border-(--welcome-accent) bg-(--welcome-surface-1)" />
                            <div>
                                <p className="text-sm font-semibold text-(--welcome-strong)">Ishara Karunadasa</p>
                                <p className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">Curator, LoomCraft</p>
                            </div>
                        </div>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}

function NaturesNatureWelcome({ canRegister, categorySections }: { canRegister: boolean; categorySections: CategorySection[] }) {
    const { auth, site } = usePage<SharedData>().props;
    const featuredProduct = categorySections[0]?.products[0] ?? null;
    const categoryCards =
        categorySections.length > 0
            ? categorySections.slice(0, 3).map((category, index) => ({
                  name: category.name,
                  note: category.description ?? categoryFallbacks[index]?.note ?? 'Homemade food prepared with natural ingredients.',
                  slug: category.slug,
                  icon: categoryFallbacks[index]?.icon ?? ShoppingBag,
                  image: categoryFallbacks[index]?.image,
              }))
            : categoryFallbacks.map((category) => ({
                  ...category,
                  slug: null,
              }));

    return (
        <>
            <SeoHead
                title={`${site.displayName} — Organic Homemade Foods`}
                description={site.description}
                canonical="/"
                schema={{
                    '@context': 'https://schema.org',
                    '@type': 'Organization',
                    name: site.displayName,
                    url: '/',
                }}
            >
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600" rel="stylesheet" />
            </SeoHead>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="relative mx-auto w-full max-w-6xl px-6 pt-2 pb-14 text-center md:pb-18">
                    <div className="pointer-events-none absolute inset-x-6 top-8 h-64 rounded-[48px] bg-[radial-gradient(circle_at_18%_32%,var(--nature-yellow),transparent_18%),radial-gradient(circle_at_84%_28%,var(--nature-orange),transparent_16%),linear-gradient(180deg,var(--welcome-surface-1),transparent)] opacity-35 blur-2xl" />
                    <div className="relative mx-auto max-w-3xl">
                        <div className="mx-auto inline-flex items-center gap-3 rounded-full border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-2 text-xs tracking-[0.28em] text-(--welcome-muted-text) uppercase shadow-[0_18px_40px_-36px_var(--welcome-shadow)]">
                            Made fresh in small batches
                        </div>
                        <h1 className="mt-6 font-['Playfair_Display',serif] text-5xl leading-[0.95] text-(--welcome-strong) md:text-6xl lg:text-7xl">
                            Real food to fuel your sweetest days.
                        </h1>
                        <p className="mx-auto mt-5 max-w-2xl text-base leading-8 text-(--welcome-body-text) md:text-lg">
                            {site.displayName} brings organic homemade cookies, pantry favorites, and curated food boxes into a warm modern market built around natural ingredients.
                        </p>
                        <div className="mt-7 flex flex-wrap justify-center gap-3">
                            <Link
                                href={productsIndex().url}
                                className="rounded-full bg-(--nature-leaf) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong)"
                            >
                                Shop the pantry
                            </Link>
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="rounded-full border border-(--welcome-strong) bg-(--welcome-surface-3) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                >
                                    Open kitchen
                                </Link>
                            ) : (
                                canRegister && (
                                    <Link
                                        href={register()}
                                        className="rounded-full border border-(--welcome-strong) bg-(--welcome-surface-3) px-6 py-3 text-sm font-semibold tracking-[0.2em] text-(--welcome-strong) uppercase transition hover:-translate-y-0.5 hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                    >
                                        Join as maker
                                    </Link>
                                )
                            )}
                        </div>
                    </div>

                    <div className="relative mt-10 overflow-hidden rounded-[42px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) shadow-[0_30px_80px_-48px_var(--welcome-shadow-strong)]">
                        <img
                            src={heroImage}
                            alt="Homemade cookies, fruit, and natural ingredients arranged with NaturesNature food pouches"
                            className="h-auto min-h-[330px] w-full object-cover object-center"
                        />
                    </div>
                </section>

                <section className="border-y border-(--welcome-border-soft) bg-(--welcome-surface-3) py-16">
                    <div className="mx-auto w-full max-w-6xl px-6 text-center">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Made with care</p>
                        <h2 className="mx-auto mt-3 max-w-3xl font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Elevated homemade food for the road, the table, and every gift in between.
                        </h2>
                        <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-(--welcome-body-text)">
                            From small-batch bakes to curated food boxes, our makers bring rich flavor to everyday moments.
                        </p>
                        <div className="mt-10 grid gap-5 text-left md:grid-cols-3">
                            {categoryCards.map((category) => {
                                const Icon = category.icon;

                                return (
                                    <Link
                                        key={category.name}
                                        href={
                                            category.slug
                                                ? productsIndex.url({
                                                      query: {
                                                          category: category.slug,
                                                          page: 1,
                                                          per_page: 9,
                                                      },
                                                  })
                                                : productsIndex().url
                                        }
                                        className="group rounded-[12px] border border-(--welcome-border-soft) bg-(--welcome-surface-2) p-5 shadow-[0_20px_45px_-38px_var(--welcome-shadow)] transition hover:-translate-y-1 hover:border-(--welcome-accent)"
                                    >
                                        <div className="flex items-end justify-between gap-4">
                                            {category.image ? (
                                                <img src={category.image} alt="" className="h-14 w-14 rounded-full object-cover" />
                                            ) : (
                                                <span className="inline-flex h-11 w-11 items-center justify-center rounded-full bg-(--nature-leaf-soft) text-(--nature-leaf)">
                                                    <Icon className="h-5 w-5" />
                                                </span>
                                            )}
                                            <span className="text-xl text-(--welcome-muted-text) transition group-hover:translate-x-1">→</span>
                                        </div>
                                        <h3 className="mt-5 font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">{category.name}</h3>
                                        <p className="mt-2 text-sm leading-6 text-(--welcome-body-text)">{category.note}</p>
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 py-18 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div className="space-y-5">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">From our makers to your table</p>
                        <h2 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">From kitchen to table, it is personal.</h2>
                        <p className="max-w-md text-sm leading-7 text-(--welcome-body-text)">
                            We connect home food makers with customers who care about honest ingredients, generous flavor, and thoughtful presentation.
                        </p>
                        <Link
                            href={productsIndex().url}
                            className="inline-flex rounded-full bg-(--nature-leaf) px-5 py-3 text-xs font-semibold tracking-[0.28em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5"
                        >
                            Find your flavor
                        </Link>
                    </div>
                    <div className="grid gap-4">
                        <img
                            src={makerTableImage}
                            alt="Homemade cookies and natural pantry ingredients arranged on a rustic table"
                            className="min-h-72 w-full rounded-[28px] border border-(--welcome-border-soft) object-cover shadow-[0_28px_65px_-44px_var(--welcome-shadow-heavy)]"
                        />
                        <div className="grid gap-4 md:grid-cols-3">
                            {makerProofs.map((item) => {
                                const Icon = item.icon;

                                return (
                                    <article
                                        key={item.title}
                                        className="rounded-[12px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4 shadow-[0_18px_38px_-34px_var(--welcome-shadow)]"
                                    >
                                        <Icon className="h-5 w-5 text-(--nature-leaf)" />
                                        <h3 className="mt-3 font-['Playfair_Display',serif] text-lg">{item.title}</h3>
                                        <p className="mt-2 text-xs leading-5 text-(--welcome-body-text)">{item.description}</p>
                                    </article>
                                );
                            })}
                        </div>
                    </div>
                </section>

                <section className="relative overflow-hidden bg-(--nature-leaf) px-6 py-16 text-(--welcome-on-strong)">
                    <div className="pointer-events-none absolute inset-x-0 -top-16 h-24 rounded-b-[100%] bg-(--welcome-surface-2)" />
                    <div className="mx-auto w-full max-w-6xl text-center">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-on-strong-muted) uppercase">What we make</p>
                        <h2 className="mt-3 font-['Playfair_Display',serif] text-4xl md:text-5xl">Everyday bites, specialty treats, crafted with care.</h2>
                        <div className="mt-10 grid items-center gap-8 lg:grid-cols-[0.75fr_1fr_0.75fr]">
                            <img
                                src={featureHomemadeBatchesImage}
                                alt="Freshly baked homemade cookies and ingredients"
                                className="hidden aspect-square rounded-full border border-(--welcome-on-strong-45) object-cover opacity-90 lg:block"
                            />
                            <div className="mx-auto w-full max-w-md rounded-[18px] bg-(--welcome-surface-3) p-4 text-left text-(--welcome-strong) shadow-[0_28px_80px_-44px_var(--welcome-shadow-heavy)]">
                                <img src={featurePackedFreshImage} alt="Fresh pantry ingredients prepared for packing" className="min-h-58 w-full rounded-[14px] object-cover" />
                                <h3 className="mt-5 font-['Playfair_Display',serif] text-2xl">{featuredProduct?.name ?? 'Green cardamom cookies'}</h3>
                                <p className="mt-1 text-sm text-(--welcome-body-text)">Small-batch flavor, packed for gifting and everyday snacking.</p>
                            </div>
                            <img
                                src={featureShippingImage}
                                alt="A woven basket of homemade foods ready for delivery"
                                className="hidden aspect-square rounded-full border border-(--welcome-on-strong-45) object-cover opacity-90 lg:block"
                            />
                        </div>
                        <Link
                            href={productsIndex().url}
                            className="mt-9 inline-flex rounded-full bg-(--welcome-on-strong) px-5 py-3 text-xs font-semibold tracking-[0.28em] text-(--nature-leaf) uppercase transition hover:-translate-y-0.5"
                        >
                            Shop all products
                        </Link>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 py-16">
                    <div className="mb-8 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Fresh from the pantry</p>
                            <h2 className="mt-2 font-['Playfair_Display',serif] text-4xl">Cookies, pantry treats, and food gifts.</h2>
                        </div>
                        <Link
                            href={productsIndex().url}
                            className="rounded-full border border-(--welcome-strong) bg-(--welcome-surface-3) px-5 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                        >
                            Shop all
                        </Link>
                    </div>

                    {categorySections.length === 0 ? (
                        <div className="rounded-[32px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-8 text-sm text-(--welcome-muted-text)">
                            Products are being prepared.
                        </div>
                    ) : (
                        <div className="grid gap-8">
                            {categorySections.map((category) => (
                                <section key={category.id} className="grid gap-5">
                                    <div className="flex flex-wrap items-center justify-between gap-4">
                                        <div>
                                            <h3 className="font-['Playfair_Display',serif] text-3xl">{category.name}</h3>
                                            {category.description && <p className="mt-2 max-w-2xl text-sm text-(--welcome-body-text)">{category.description}</p>}
                                        </div>
                                        <Link
                                            href={productsIndex.url({
                                                query: {
                                                    category: category.slug,
                                                    page: 1,
                                                    per_page: 9,
                                                },
                                            })}
                                            className="rounded-full bg-(--welcome-accent) px-4 py-2 text-xs font-semibold tracking-[0.24em] text-(--welcome-on-strong) uppercase"
                                        >
                                            More
                                        </Link>
                                    </div>
                                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                                        {category.products.map((product) => (
                                            <ProductCard key={product.id} product={product} />
                                        ))}
                                    </div>
                                </section>
                            ))}
                        </div>
                    )}
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 pb-20 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <div className="grid grid-cols-2 gap-4">
                        <img
                            src={giftFoodBasketImage}
                            alt="Curated homemade food basket with cookies and pantry items"
                            className="min-h-72 w-full rounded-[18px] border border-(--welcome-border-soft) object-cover"
                        />
                        <div className="grid gap-4 pt-10">
                            <img
                                src={giftJackfruitImage}
                                alt="Golden jackfruit pieces and homemade cookies"
                                className="min-h-38 w-full rounded-[14px] border border-(--welcome-border-soft) object-cover"
                            />
                            <img
                                src={giftPumpkinImage}
                                alt="Pumpkin, rice flour, and homemade cookies"
                                className="min-h-38 w-full rounded-[14px] border border-(--welcome-border-soft) object-cover"
                            />
                        </div>
                    </div>
                    <div className="space-y-5">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Gifting, thoughtfully curated</p>
                        <h2 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">Food boxes with color, warmth, and homemade care.</h2>
                        <p className="text-sm leading-7 text-(--welcome-body-text)">
                            Build polished food gifts from cookies, snacks, and seasonal pantry items. Placeholder visuals keep the structure ready until final product photography
                            arrives.
                        </p>
                        <div className="grid gap-3 sm:grid-cols-3">
                            {foodHighlights.map((item) => (
                                <div key={item.title} className="rounded-[12px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                                    <p className="font-['Playfair_Display',serif] text-lg">{item.title}</p>
                                    <p className="mt-2 text-xs leading-5 text-(--welcome-body-text)">{item.description}</p>
                                </div>
                            ))}
                        </div>
                        <Link
                            href={productsIndex().url}
                            className="inline-flex rounded-full bg-(--nature-leaf) px-5 py-3 text-xs font-semibold tracking-[0.28em] text-(--welcome-on-strong) uppercase transition hover:-translate-y-0.5"
                        >
                            Browse gifts
                        </Link>
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
