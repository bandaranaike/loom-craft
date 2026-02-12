import { Head, Link, usePage } from '@inertiajs/react';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { dashboard, login, register } from '@/routes';
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
            'Vendors set their base price; the platform applies a transparent 7% commission.',
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

const craftsmanshipSteps = [
    {
        label: 'Gather',
        text: 'Natural fibers selected for tone, tension, and longevity.',
    },
    {
        label: 'Dye',
        text: 'Layered color baths for depth and a soft, luminous finish.',
    },
    {
        label: 'Weave',
        text: 'Measured rhythm, motif by motif, across traditional looms.',
    },
    {
        label: 'Finish',
        text: 'Edges sealed, texture refined, and provenance documented.',
    },
];

export default function Welcome({
    canRegister = true,
}: {
    canRegister?: boolean;
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
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 pt-6 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="space-y-6">
                            <div className="inline-flex items-center gap-3 rounded-full border border-[#d4b28c] bg-[#f9efe2] px-4 py-2 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Handloomed • Curated • Collectible
                            </div>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl lg:text-6xl">
                                A marketplace devoted to Sri Lanka&apos;s most rare woven luxury.
                            </h1>
                            <p className="max-w-xl text-base text-[#5a4a3a] md:text-lg">
                                LoomCraft brings Dumbara Rataa artisans into a refined global
                                atelier. Each piece is crafted in limited numbers with detailed
                                provenance, authentic materials, and a heritage-first story.
                            </p>
                            <div className="flex flex-wrap items-center gap-4">
                                <Link
                                    href={productsIndex().url}
                                    className="rounded-full border border-[#2b241c] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#2b241c] transition hover:-translate-y-0.5 hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                >
                                    Browse Products
                                </Link>
                                {auth.user ? (
                                    <Link
                                        href={dashboard()}
                                        className="rounded-full bg-[#2b241c] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#f6f1e8] transition hover:-translate-y-0.5 hover:bg-[#3a2f25]"
                                    >
                                        Visit Dashboard
                                    </Link>
                                ) : (
                                    <>
                                        {canRegister && (
                                            <Link
                                                href={register()}
                                                className="rounded-full bg-[#2b241c] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#f6f1e8] transition hover:-translate-y-0.5 hover:bg-[#3a2f25]"
                                            >
                                                Start Collecting
                                            </Link>
                                        )}
                                        <Link
                                            href={login()}
                                            className="rounded-full border border-[#2b241c] px-6 py-3 text-sm font-semibold uppercase tracking-[0.2em] text-[#2b241c] transition hover:-translate-y-0.5 hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                        >
                                            Sign In
                                        </Link>
                                    </>
                                )}
                            </div>
                            <div className="flex flex-wrap items-center gap-6 text-xs uppercase tracking-[0.24em] text-[#7a5a3a]">
                                <span>Guest Checkout</span>
                                <span>USD / EUR / LKR</span>
                                <span>Manual &amp; Stripe Payments</span>
                            </div>
                        </div>
                        <div className="relative">
                            <div className="absolute -right-6 top-12 h-60 w-60 rounded-[40px] border border-[#d4b28c] bg-[#fdf8f0] shadow-[0_20px_60px_-30px_rgba(43,36,28,0.45)]" />
                            <div className="relative grid gap-6 rounded-[36px] border border-[#d4b28c] bg-[#f9efe2] p-8 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.6)]">
                                <div className="flex items-center justify-between">
                                    <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                        Atelier Ledger
                                    </p>
                                    <span className="rounded-full bg-[#2b241c] px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-[#f6f1e8]">
                                        Live
                                    </span>
                                </div>
                                <div className="space-y-3">
                                    <p className="font-['Playfair_Display',serif] text-3xl">
                                        214
                                    </p>
                                    <p className="text-sm text-[#5a4a3a]">
                                        Heritage pieces currently available
                                    </p>
                                </div>
                                <div className="grid gap-4 rounded-[28px] border border-[#e0c7a7] bg-[#fdf8f0] p-5">
                                    <p className="text-sm font-semibold uppercase tracking-[0.2em] text-[#7a5a3a]">
                                        Featured Artisan
                                    </p>
                                    <div className="space-y-1">
                                        <p className="font-['Playfair_Display',serif] text-2xl">
                                            Dilhani Perera
                                        </p>
                                        <p className="text-sm text-[#5a4a3a]">
                                            18 years in Dumbara Rataa weaving
                                        </p>
                                    </div>
                                    <div className="h-2 w-full overflow-hidden rounded-full bg-[#e7d1b6]">
                                        <div className="h-full w-2/3 rounded-full bg-[#b6623a]" />
                                    </div>
                                    <p className="text-xs text-[#7a5a3a]">
                                        Limited release: 6 signed textiles this season.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="grid gap-8 rounded-[48px] border border-[#e0c7a7] bg-[#fff8ed] p-10 md:grid-cols-3">
                        {highlights.map((item) => (
                            <div
                                key={item.title}
                                className="space-y-3 border-b border-[#e0c7a7] pb-6 last:border-b-0 last:pb-0 md:border-b-0 md:border-r md:pb-0 md:pr-8 md:last:border-r-0 md:last:pr-0"
                            >
                                <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    Signature
                                </p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl">
                                    {item.title}
                                </h2>
                                <p className="text-sm text-[#5a4a3a]">
                                    {item.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-10 px-6 pb-16 lg:grid-cols-[0.9fr_1.1fr]">
                    <div className="space-y-6">
                        <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                            Curated Collections
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                            Designed for galleries, rituals, and refined living.
                        </h2>
                        <p className="text-sm text-[#5a4a3a]">
                            Every collection is an editorial of texture, story, and provenance —
                            ready for patrons, design studios, and collectors worldwide.
                        </p>
                    </div>
                    <div className="grid gap-4 md:grid-cols-3">
                        {collections.map((collection) => (
                            <div
                                key={collection.name}
                                className="group rounded-[32px] border border-[#e0c7a7] bg-[#fff8ed] p-6 transition hover:-translate-y-1 hover:border-[#b6623a]"
                            >
                                <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    Collection
                                </p>
                                <h3 className="mt-3 font-['Playfair_Display',serif] text-xl">
                                    {collection.name}
                                </h3>
                                <p className="mt-2 text-sm text-[#5a4a3a]">
                                    {collection.note}
                                </p>
                                <div className="mt-6 h-1 w-12 rounded-full bg-[#b6623a] transition group-hover:w-20" />
                            </div>
                        ))}
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="grid gap-10 rounded-[48px] border border-[#e0c7a7] bg-[#f9efe2] p-10 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="space-y-6">
                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Craftsmanship Flow
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                                From fiber to finish, every detail is traced.
                            </h2>
                            <p className="text-sm text-[#5a4a3a]">
                                LoomCraft documents each stage of creation, so patrons receive
                                not only the textile, but its story, materials, and maker.
                            </p>
                        </div>
                        <div className="grid gap-4">
                            {craftsmanshipSteps.map((step, index) => (
                                <div
                                    key={step.label}
                                    className="flex items-start gap-4 rounded-[24px] border border-[#e0c7a7] bg-[#fff8ed] p-4"
                                >
                                    <div className="grid h-10 w-10 place-items-center rounded-full border border-[#b6623a] text-sm font-semibold">
                                        {index + 1}
                                    </div>
                                    <div>
                                        <p className="text-sm font-semibold uppercase tracking-[0.2em] text-[#7a5a3a]">
                                            {step.label}
                                        </p>
                                        <p className="mt-1 text-sm text-[#5a4a3a]">
                                            {step.text}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="mx-auto grid w-full max-w-6xl gap-8 px-6 pb-20 lg:grid-cols-[1fr_1fr]">
                    <div className="rounded-[40px] border border-[#e0c7a7] bg-[#2b241c] p-8 text-[#f6f1e8]">
                        <p className="text-xs uppercase tracking-[0.3em] text-[#e6c9a6]">
                            For Vendors
                        </p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">
                            A refined marketplace for master weavers.
                        </h2>
                        <p className="mt-3 text-sm text-[#f6f1e8]/80">
                            Gain access to a curated audience, transparent pricing, and a
                            dashboard built around your craft. Manual approval keeps the
                            marketplace authentic and exclusive.
                        </p>
                        {canRegister && !auth.user && (
                            <div className="mt-6">
                                <Link
                                    href={register()}
                                    className="inline-flex items-center rounded-full border border-[#f6f1e8] px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] transition hover:bg-[#f6f1e8] hover:text-[#2b241c]"
                                >
                                    Apply as Vendor
                                </Link>
                            </div>
                        )}
                    </div>
                    <div className="rounded-[40px] border border-[#e0c7a7] bg-[#fff8ed] p-8">
                        <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                            Patron Notes
                        </p>
                        <h2 className="mt-4 font-['Playfair_Display',serif] text-3xl">
                            "The weave carries the rhythm of the loom itself."
                        </h2>
                        <p className="mt-3 text-sm text-[#5a4a3a]">
                            Each order arrives with a maker&apos;s note, woven coordinates, and
                            recommended care to preserve its luster for generations.
                        </p>
                        <div className="mt-6 flex items-center gap-4">
                            <div className="h-12 w-12 rounded-full border border-[#b6623a] bg-[#f9efe2]" />
                            <div>
                                <p className="text-sm font-semibold text-[#2b241c]">
                                    Amara Wijekoon
                                </p>
                                <p className="text-xs uppercase tracking-[0.2em] text-[#7a5a3a]">
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
