import { Form, Head, Link, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import ProductCard, { type ProductCardItem } from '@/components/product-card';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { dashboard, register } from '@/routes';
import { index as productsIndex } from '@/routes/products';
import { store as feedbackStore } from '@/routes/vendor/feedback';
import { show as vendorShow } from '@/routes/vendors';
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

type LedgerProps = {
    active_products: number;
    approved_feedback: number;
};

type FeedbackItem = {
    id: number;
    title: string;
    details: string;
    author_name: string;
    author_vendor_slug: string | null;
    author_role: string;
    approved_at: string | null;
};

type MyFeedback = {
    id: number;
    title: string;
    details: string;
    status: string;
} | null;

export default function Welcome({
    canRegister = true,
    atelier_ledger,
    vendor_feedback,
    latest_products,
    my_feedback,
}: {
    canRegister?: boolean;
    atelier_ledger: LedgerProps;
    vendor_feedback: FeedbackItem[];
    latest_products: ProductCardItem[];
    my_feedback: MyFeedback;
}) {
    const { auth } = usePage<SharedData>().props;
    const canLeaveFeedback =
        auth.user &&
        (auth.user.role === 'vendor' || auth.user.role === 'customer');

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
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-10 px-6 pt-6 pb-16 lg:grid-cols-[1.1fr_0.9fr]">
                    <div className="space-y-6">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Handloomed • Curated • Collectible
                        </div>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl lg:text-6xl">
                            A marketplace devoted to Sri Lanka&apos;s most rare
                            woven luxury.
                        </h1>
                        <p className="max-w-xl text-base text-(--welcome-body-text) md:text-lg">
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
                    <div className="relative hidden md:block">
                        <div className="relative grid gap-6 rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-8 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]">
                            <div className="flex items-center justify-end sm:justify-between">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Atelier Ledger
                                </p>
                                <span className="rounded-full bg-(--welcome-strong) px-3 py-1 text-xs font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase">
                                    Live
                                </span>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-2">
                                <div className="space-y-1 rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-2) p-4">
                                    <p className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        Active Products
                                    </p>
                                    <p className="font-['Playfair_Display',serif] text-3xl">
                                        {atelier_ledger.active_products}
                                    </p>
                                </div>
                                <div className="space-y-1 rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-2) p-4">
                                    <p className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        Approved Feedback
                                    </p>
                                    <p className="font-['Playfair_Display',serif] text-3xl">
                                        {atelier_ledger.approved_feedback}
                                    </p>
                                </div>
                            </div>
                            <div className="grid gap-4 rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-2) p-5">
                                <p className="text-sm font-semibold tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                    Latest Approved Vendor Note
                                </p>
                                {vendor_feedback.length > 0 ? (
                                    <div className="space-y-2">
                                        <div className="space-y-1">
                                            <p className="font-['Playfair_Display',serif] text-2xl">
                                                {vendor_feedback[0].author_vendor_slug ? (
                                                    <Link href={vendorShow(vendor_feedback[0].author_vendor_slug)}>
                                                        {vendor_feedback[0].author_name}
                                                    </Link>
                                                ) : (
                                                    vendor_feedback[0].author_name
                                                )}
                                            </p>
                                            <p className="text-sm text-(--welcome-body-text)">
                                                {vendor_feedback[0].title}
                                            </p>
                                        </div>
                                        <p className="text-xs text-(--welcome-muted-text)">
                                            {vendor_feedback[0].details}
                                        </p>
                                    </div>
                                ) : (
                                    <p className="text-sm text-(--welcome-body-text)">
                                        Vendor feedback will appear here once
                                        approved by admins.
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
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

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="mb-6">
                        <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Atelier Voices
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                            Feedback from the LoomCraft network.
                        </h2>
                    </div>
                    {canLeaveFeedback && (
                        <div className="mb-6 rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                            <p className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                {my_feedback
                                    ? 'Edit Your Feedback'
                                    : 'Share Your Feedback'}
                            </p>
                            <h3 className="mt-2 font-['Playfair_Display',serif] text-2xl">
                                {my_feedback
                                    ? 'Update your vendor or buyer note'
                                    : 'Add your vendor or buyer note'}
                            </h3>
                            <p className="mt-2 text-sm text-(--welcome-body-text)">
                                You can keep one feedback entry. Saving again
                                updates your existing submission.
                            </p>
                            {my_feedback && (
                                <p className="mt-2 text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                    Current status: {my_feedback.status}
                                </p>
                            )}
                            <Form
                                {...feedbackStore.form()}
                                className="mt-4 grid gap-4"
                                disableWhileProcessing
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="title"
                                                className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase"
                                            >
                                                Headline
                                            </label>
                                            <input
                                                id="title"
                                                name="title"
                                                defaultValue={
                                                    my_feedback?.title ?? ''
                                                }
                                                className="w-full rounded-xl border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) ring-(--welcome-ring) outline-none focus:ring-2"
                                                placeholder="Share one headline from your LoomCraft experience"
                                                required
                                            />
                                            <InputError
                                                message={errors.title}
                                                className="text-xs"
                                            />
                                        </div>
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="details"
                                                className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase"
                                            >
                                                Details
                                            </label>
                                            <textarea
                                                id="details"
                                                name="details"
                                                rows={4}
                                                defaultValue={
                                                    my_feedback?.details ?? ''
                                                }
                                                className="w-full rounded-xl border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) ring-(--welcome-ring) outline-none focus:ring-2"
                                                placeholder="Describe your experience as a vendor or buyer."
                                                required
                                            />
                                            <InputError
                                                message={errors.details}
                                                className="text-xs"
                                            />
                                        </div>
                                        <button
                                            type="submit"
                                            className="inline-flex w-fit items-center rounded-full bg-(--welcome-strong) px-5 py-2 text-xs font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase transition hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                        >
                                            {my_feedback
                                                ? 'Update Feedback'
                                                : 'Submit Feedback'}
                                        </button>
                                    </>
                                )}
                            </Form>
                        </div>
                    )}
                    {vendor_feedback.length === 0 ? (
                        <div className="rounded-4xl border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-8 text-sm text-(--welcome-muted-text)">
                            No approved feedback yet.
                        </div>
                    ) : (
                        <div className="grid gap-5 md:grid-cols-3">
                            {vendor_feedback.map((feedback) => (
                                <div
                                    key={feedback.id}
                                    className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6"
                                >
                                    <p className="text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                        {feedback.author_vendor_slug ? (
                                            <Link href={vendorShow(feedback.author_vendor_slug)}>
                                                {feedback.author_name}
                                            </Link>
                                        ) : (
                                            feedback.author_name
                                        )}{' '}
                                        •{' '}
                                        {feedback.author_role}
                                    </p>
                                    <h3 className="mt-2 font-['Playfair_Display',serif] text-2xl">
                                        {feedback.title}
                                    </h3>
                                    <p className="mt-3 text-sm text-(--welcome-body-text)">
                                        {feedback.details}
                                    </p>
                                    {feedback.approved_at && (
                                        <p className="mt-4 text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                            Approved {feedback.approved_at}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    <div className="grid gap-10 rounded-[48px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-10 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="space-y-6">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                Craftsmanship Flow
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl md:text-4xl">
                                From fiber to finish, every detail is traced.
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                LoomCraft documents each stage of creation with
                                fiber source notes, hand-dye batch records, loom
                                rhythm logs, and final finishing checks. Patrons
                                receive not only the textile, but a verifiable
                                record of materials, methods, and artisan
                                stewardship from start to delivery.
                            </p>
                        </div>
                        <div className="grid gap-4">
                            {craftsmanshipSteps.map((step, index) => (
                                <div
                                    key={step.label}
                                    className="flex items-start gap-4 rounded-3xl border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4"
                                >
                                    <div className="grid h-10 w-10 place-items-center rounded-full border border-(--welcome-accent) text-sm font-semibold">
                                        {index + 1}
                                    </div>
                                    <div>
                                        <p className="text-sm font-semibold tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                            {step.label}
                                        </p>
                                        <p className="mt-1 text-sm text-(--welcome-body-text)">
                                            {step.text}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
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
