import { Form, Head, Link } from '@inertiajs/react';
import { formatMoney } from '@/lib/currency';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { index as productsIndex, show as productShow } from '@/routes/products';
import { show as vendorShow } from '@/routes/vendors';

type ProductItem = {
    id: number;
    name: string;
    selling_price: string;
    vendor_name: string;
    vendor_slug: string | null;
    vendor_location: string | null;
    image_url: string | null;
    categories: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Pagination = {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};

type ProductIndexProps = {
    products: ProductItem[];
    categories: Array<{
        id: number;
        name: string;
        slug: string;
    }>;
    pagination: Pagination;
    per_page: number;
    search: string | null;
    selected_category: string | null;
    canRegister?: boolean;
};

const perPageOptions = [9, 12, 24];

export default function ProductIndex({
    products,
    categories,
    pagination,
    per_page,
    search,
    selected_category,
    canRegister = true,
}: ProductIndexProps) {
    return (
        <>
            <Head title="Products — LoomCraft">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <PublicSiteLayout canRegister={canRegister}>
                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-8 px-6 pb-12 pt-6 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="space-y-4">
                            <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Approved Collection
                            </div>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                Discover heritage pieces curated for collectors.
                            </h1>
                            <p className="max-w-xl text-sm text-(--welcome-body-text) md:text-base">
                                Only LoomCraft-approved textiles are presented here — each woven
                                by verified artisans with documented provenance.
                            </p>
                        </div>
                        <div className="rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow-medium)]">
                            <Form
                                {...productsIndex.form()}
                                className="flex flex-col gap-4"
                            >
                                <input
                                    type="hidden"
                                    name="per_page"
                                    value={per_page}
                                />
                                <label
                                    htmlFor="search"
                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                >
                                    Search the catalog
                                </label>
                                <input
                                    id="search"
                                    type="search"
                                    name="search"
                                    placeholder="Search by product name"
                                    defaultValue={search ?? ''}
                                    className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                />
                                <div className="flex flex-wrap items-center gap-3">
                                    <button
                                        type="submit"
                                        className="inline-flex items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                                    >
                                        Explore
                                    </button>
                                    <div className="flex items-center gap-2">
                                        <span className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Category
                                        </span>
                                        <select
                                            name="category"
                                            defaultValue={selected_category ?? ''}
                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                            onChange={(event) => {
                                                event.currentTarget.form?.requestSubmit();
                                            }}
                                        >
                                            <option value="">All</option>
                                            {categories.map((category) => (
                                                <option key={category.id} value={category.slug}>
                                                    {category.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Per page
                                        </span>
                                        <select
                                            name="per_page"
                                            defaultValue={per_page}
                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                            onChange={(event) => {
                                                event.currentTarget.form?.requestSubmit();
                                            }}
                                        >
                                            {perPageOptions.map((option) => (
                                                <option key={option} value={option}>
                                                    {option}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                            </Form>
                        </div>
                    </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    {products.length === 0 ? (
                        <div className="rounded-[32px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
                            No approved products yet. Please check back soon.
                        </div>
                    ) : (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {products.map((product) => (
                                <article
                                    key={product.id}
                                    className="group flex h-full flex-col overflow-hidden rounded-[32px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) transition hover:-translate-y-1 hover:border-(--welcome-accent)"
                                >
                                    <Link
                                        href={productShow(product.id)}
                                        className="relative aspect-[4/3] overflow-hidden bg-(--welcome-surface-1)"
                                    >
                                        {product.image_url ? (
                                            <img
                                                src={product.image_url}
                                                alt={product.name}
                                                className="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                            />
                                        ) : (
                                            <div className="flex h-full w-full items-center justify-center text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                Image forthcoming
                                            </div>
                                        )}
                                    </Link>
                                    <div className="flex flex-1 flex-col gap-3 p-5">
                                        <div className="space-y-2">
                                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                {product.vendor_slug ? (
                                                    <Link href={vendorShow(product.vendor_slug)}>
                                                        {product.vendor_name}
                                                    </Link>
                                                ) : (
                                                    product.vendor_name
                                                )}
                                                {product.vendor_location
                                                    ? ` • ${product.vendor_location}`
                                                    : ''}
                                            </p>
                                            <h3 className="font-['Playfair_Display',serif] text-xl">
                                                <Link href={productShow(product.id)}>
                                                    {product.name}
                                                </Link>
                                            </h3>
                                            {product.categories.length > 0 && (
                                                <div className="flex flex-wrap gap-2">
                                                    {product.categories.map((category) => (
                                                        <span
                                                            key={category.id}
                                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-2.5 py-1 text-[10px] uppercase tracking-[0.2em] text-(--welcome-muted-text)"
                                                        >
                                                            {category.name}
                                                        </span>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                        <div className="mt-auto flex items-center justify-between text-sm">
                                            <span className="text-(--welcome-body-text)">
                                                Selling price
                                            </span>
                                            <span className="font-semibold text-(--welcome-strong)">
                                                {formatMoney(product.selling_price, 'LKR')}
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </div>
                    )}
                </section>

                {pagination.last_page > 1 && (
                    <section className="mx-auto w-full max-w-6xl px-6 pb-20">
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-3 text-xs text-(--welcome-muted-text)">
                            <div>
                                Showing {pagination.from ?? 0} - {pagination.to ?? 0} of{' '}
                                {pagination.total}
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {pagination.links.map((link) => {
                                    const key = `${link.label}-${link.url}`;
                                    const baseClass =
                                        'rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] transition';
                                    if (!link.url) {
                                        return (
                                            <span
                                                key={key}
                                                className={`${baseClass} border-(--welcome-border) text-(--welcome-muted-60)`}
                                            >
                                                {link.label}
                                            </span>
                                        );
                                    }

                                    return (
                                        <Link
                                            key={key}
                                            href={link.url}
                                            className={`${baseClass} ${
                                                link.active
                                                    ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                                    : 'border-(--welcome-strong-50) text-(--welcome-strong) hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)'
                                            }`}
                                        >
                                            {link.label}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    </section>
                )}
            </PublicSiteLayout>
        </>
    );
}
