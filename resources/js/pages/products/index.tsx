import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import ProductCard, { type ProductCardItem } from '@/components/product-card';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { resolveProductColorSwatch } from '@/lib/product-color-swatches';
import { index as productsIndex } from '@/routes/products';

type FilterOption = {
    id: number;
    name: string;
    slug: string;
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
    products: ProductCardItem[];
    categories: FilterOption[];
    vendors: FilterOption[];
    colors: FilterOption[];
    pagination: Pagination;
    per_page: number;
    search: string | null;
    selected_category: string | null;
    selected_vendor: string | null;
    selected_colors: string[];
    min_price: string | number | null;
    max_price: string | number | null;
    canRegister?: boolean;
};

const perPageOptions = [9, 12, 24];
const searchDebounceMs = 600;

export default function ProductIndex({
    products,
    categories,
    vendors,
    colors,
    pagination,
    per_page,
    search,
    selected_category,
    selected_vendor,
    selected_colors,
    min_price,
    max_price,
    canRegister = true,
}: ProductIndexProps) {
    const [searchInput, setSearchInput] = useState(search ?? '');
    const [categoryFilter, setCategoryFilter] = useState(
        selected_category ?? '',
    );
    const [vendorFilter, setVendorFilter] = useState(selected_vendor ?? '');
    const [selectedColorFilters, setSelectedColorFilters] =
        useState<string[]>(selected_colors);
    const [minPriceFilter, setMinPriceFilter] = useState(
        min_price !== null && min_price !== undefined ? String(min_price) : '',
    );
    const [maxPriceFilter, setMaxPriceFilter] = useState(
        max_price !== null && max_price !== undefined ? String(max_price) : '',
    );
    const [perPageFilter, setPerPageFilter] = useState(per_page);
    const [isLoading, setIsLoading] = useState(false);
    const [isMobileFilterOpen, setIsMobileFilterOpen] = useState(false);

    const applyFilters = (overrides?: {
        search?: string;
        category?: string;
        vendor?: string;
        colors?: string[];
        minPrice?: string;
        maxPrice?: string;
        perPage?: number;
    }): void => {
        const nextSearch = (overrides?.search ?? searchInput).trim();
        const nextCategory = overrides?.category ?? categoryFilter;
        const nextVendor = overrides?.vendor ?? vendorFilter;
        const nextColors = overrides?.colors ?? selectedColorFilters;
        const nextMinPrice = (overrides?.minPrice ?? minPriceFilter).trim();
        const nextMaxPrice = (overrides?.maxPrice ?? maxPriceFilter).trim();
        const nextPerPage = overrides?.perPage ?? perPageFilter;

        router.get(
            productsIndex().url,
            {
                search: nextSearch !== '' ? nextSearch : undefined,
                category: nextCategory !== '' ? nextCategory : undefined,
                vendor: nextVendor !== '' ? nextVendor : undefined,
                colors: nextColors.length > 0 ? nextColors : undefined,
                min_price: nextMinPrice !== '' ? nextMinPrice : undefined,
                max_price: nextMaxPrice !== '' ? nextMaxPrice : undefined,
                per_page: nextPerPage,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setIsLoading(true),
                onFinish: () => setIsLoading(false),
            },
        );
    };

    useEffect(() => {
        const activeSearch = (search ?? '').trim();
        const pendingSearch = searchInput.trim();

        if (pendingSearch === activeSearch) {
            return;
        }

        const timeoutId = window.setTimeout(() => {
            applyFilters({ search: searchInput });
        }, searchDebounceMs);

        return () => {
            window.clearTimeout(timeoutId);
        };
    }, [search, searchInput]);

    const hasActiveFilters =
        searchInput.trim() !== '' ||
        categoryFilter !== '' ||
        vendorFilter !== '' ||
        selectedColorFilters.length > 0 ||
        minPriceFilter.trim() !== '' ||
        maxPriceFilter.trim() !== '';

    const clearAllFilters = (): void => {
        setSearchInput('');
        setCategoryFilter('');
        setVendorFilter('');
        setSelectedColorFilters([]);
        setMinPriceFilter('');
        setMaxPriceFilter('');
        applyFilters({
            search: '',
            category: '',
            vendor: '',
            colors: [],
            minPrice: '',
            maxPrice: '',
        });
    };

    const toggleColor = (slug: string): void => {
        const nextColors = selectedColorFilters.includes(slug)
            ? selectedColorFilters.filter((colorSlug) => colorSlug !== slug)
            : [...selectedColorFilters, slug];

        setSelectedColorFilters(nextColors);
        applyFilters({ colors: nextColors });
    };

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
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-8 px-6 pt-6 pb-12 lg:grid-cols-[1.1fr_0.9fr]">
                    <div className="space-y-4">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            Approved Collection
                        </div>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            Discover heritage pieces curated for collectors.
                        </h1>
                        <p className="max-w-xl text-sm text-(--welcome-body-text) md:text-base">
                            Only LoomCraft-approved textiles are presented here,
                            each woven by verified artisans with documented
                            provenance.
                        </p>
                    </div>

                    <div className="rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow-medium)]">
                        <div className="space-y-4">
                            <div className="flex items-center justify-between gap-3">
                                <label
                                    htmlFor="search"
                                    className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase"
                                >
                                    Search the catalog
                                </label>
                                <div className="flex items-center gap-2">
                                    {isLoading && (
                                        <Spinner className="text-(--welcome-strong)" />
                                    )}
                                    {hasActiveFilters && (
                                        <button
                                            type="button"
                                            onClick={clearAllFilters}
                                            className="text-[10px] font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase transition hover:text-(--welcome-strong)"
                                        >
                                            Clear all
                                        </button>
                                    )}
                                </div>
                            </div>
                            <input
                                id="search"
                                type="search"
                                placeholder="Search by product name"
                                value={searchInput}
                                onChange={(event) =>
                                    setSearchInput(event.currentTarget.value)
                                }
                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                            />

                            <button
                                type="button"
                                onClick={() =>
                                    setIsMobileFilterOpen(
                                        (currentState) => !currentState,
                                    )
                                }
                                className="inline-flex w-full items-center justify-between rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase md:hidden"
                            >
                                Filters
                                <span>
                                    {isMobileFilterOpen ? 'Hide' : 'Show'}
                                </span>
                            </button>

                            <div
                                className={`grid gap-4 overflow-hidden transition-all duration-300 md:grid md:overflow-visible ${
                                    isMobileFilterOpen
                                        ? 'max-h-[1000px] opacity-100'
                                        : 'max-h-0 opacity-0 md:max-h-none md:opacity-100'
                                }`}
                            >
                                <div className="grid gap-3 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Category
                                        </span>
                                        <select
                                            value={categoryFilter}
                                            onChange={(event) => {
                                                const nextCategory =
                                                    event.currentTarget.value;
                                                setCategoryFilter(nextCategory);
                                                applyFilters({
                                                    category: nextCategory,
                                                });
                                            }}
                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                        >
                                            <option value="">All</option>
                                            {categories.map((category) => (
                                                <option
                                                    key={category.id}
                                                    value={category.slug}
                                                >
                                                    {category.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="grid gap-2">
                                        <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Vendor
                                        </span>
                                        <select
                                            value={vendorFilter}
                                            onChange={(event) => {
                                                const nextVendor =
                                                    event.currentTarget.value;
                                                setVendorFilter(nextVendor);
                                                applyFilters({
                                                    vendor: nextVendor,
                                                });
                                            }}
                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                        >
                                            <option value="">All</option>
                                            {vendors.map((vendor) => (
                                                <option
                                                    key={vendor.id}
                                                    value={vendor.slug}
                                                >
                                                    {vendor.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="grid gap-2">
                                        <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Min price
                                        </span>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={minPriceFilter}
                                            onChange={(event) => {
                                                const nextMinPrice =
                                                    event.currentTarget.value;
                                                setMinPriceFilter(nextMinPrice);
                                                applyFilters({
                                                    minPrice: nextMinPrice,
                                                });
                                            }}
                                            placeholder="0.00"
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Max price
                                        </span>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={maxPriceFilter}
                                            onChange={(event) => {
                                                const nextMaxPrice =
                                                    event.currentTarget.value;
                                                setMaxPriceFilter(nextMaxPrice);
                                                applyFilters({
                                                    maxPrice: nextMaxPrice,
                                                });
                                            }}
                                            placeholder="0.00"
                                            className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                        />
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                        Per page
                                    </span>
                                    <select
                                        value={perPageFilter}
                                        onChange={(event) => {
                                            const nextPerPage = Number(
                                                event.currentTarget.value,
                                            );
                                            setPerPageFilter(nextPerPage);
                                            applyFilters({
                                                perPage: nextPerPage,
                                            });
                                        }}
                                        className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                    >
                                        {perPageOptions.map((option) => (
                                            <option key={option} value={option}>
                                                {option}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {colors.length > 0 && (
                                    <div className="grid gap-2">
                                        <p className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Colors
                                        </p>
                                        <div className="flex flex-wrap gap-1">
                                            {colors.map((color) => {
                                                const checked =
                                                    selectedColorFilters.includes(
                                                        color.slug,
                                                    );

                                                return (
                                                    <button
                                                        key={color.id}
                                                        type="button"
                                                        title={color.name}
                                                        onClick={() =>
                                                            toggleColor(
                                                                color.slug,
                                                            )
                                                        }
                                                        className={`relative grid h-8 w-8 cursor-pointer place-items-center rounded-md border p   -1 transition ${
                                                            checked
                                                                ? 'border-(--welcome-strong) bg-(--welcome-surface-1)'
                                                                : 'border-(--welcome-border) bg-(--welcome-surface-3) hover:border-(--welcome-strong-50)'
                                                        }`}
                                                        aria-label={`Filter by ${color.name}`}
                                                        aria-pressed={checked}
                                                    >
                                                        <span
                                                            className="block h-5 w-5 rounded-sm border border-black/10"
                                                            style={{
                                                                backgroundColor: resolveProductColorSwatch(color.slug),
                                                            }}
                                                        />
                                                        <span
                                                            className={`pointer-events-none absolute inset-0 grid place-items-center text-xs font-bold text-white mix-blend-difference transition ${
                                                                checked ? 'opacity-100' : 'opacity-0'
                                                            }`}
                                                        >
                                                            ✓
                                                        </span>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                {hasActiveFilters && (
                    <section className="mx-auto w-full max-w-6xl px-6 pb-5">
                        <div className="flex flex-wrap items-center gap-2 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4">
                            {searchInput.trim() !== '' && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSearchInput('');
                                        applyFilters({ search: '' });
                                    }}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs text-(--welcome-strong)"
                                >
                                    Search: {searchInput.trim()} ×
                                </button>
                            )}
                            {categoryFilter !== '' && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setCategoryFilter('');
                                        applyFilters({ category: '' });
                                    }}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs text-(--welcome-strong)"
                                >
                                    Category:{' '}
                                    {categories.find(
                                        (category) =>
                                            category.slug === categoryFilter,
                                    )?.name ?? categoryFilter}{' '}
                                    ×
                                </button>
                            )}
                            {vendorFilter !== '' && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setVendorFilter('');
                                        applyFilters({ vendor: '' });
                                    }}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs text-(--welcome-strong)"
                                >
                                    Vendor:{' '}
                                    {vendors.find(
                                        (vendor) =>
                                            vendor.slug === vendorFilter,
                                    )?.name ?? vendorFilter}{' '}
                                    ×
                                </button>
                            )}
                            {minPriceFilter.trim() !== '' && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setMinPriceFilter('');
                                        applyFilters({ minPrice: '' });
                                    }}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs text-(--welcome-strong)"
                                >
                                    Min: {minPriceFilter} ×
                                </button>
                            )}
                            {maxPriceFilter.trim() !== '' && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setMaxPriceFilter('');
                                        applyFilters({ maxPrice: '' });
                                    }}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs text-(--welcome-strong)"
                                >
                                    Max: {maxPriceFilter} ×
                                </button>
                            )}
                            {selectedColorFilters.map((slug) => (
                                <button
                                    key={slug}
                                    type="button"
                                    onClick={() => toggleColor(slug)}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs text-(--welcome-strong)"
                                >
                                    Color:{' '}
                                    {colors.find((color) => color.slug === slug)
                                        ?.name ?? slug}{' '}
                                    ×
                                </button>
                            ))}
                        </div>
                    </section>
                )}

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    {products.length === 0 ? (
                        <div className="rounded-[32px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
                            <p>No products found for the current filters.</p>
                            <button
                                type="button"
                                onClick={clearAllFilters}
                                className="mt-4 rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong)"
                            >
                                Reset filters
                            </button>
                        </div>
                    ) : (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {products.map((product) => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                />
                            ))}
                        </div>
                    )}
                </section>

                {pagination.last_page > 1 && (
                    <section className="mx-auto w-full max-w-6xl px-6 pb-20">
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-3 text-xs text-(--welcome-muted-text)">
                            <div>
                                Showing {pagination.from ?? 0} -{' '}
                                {pagination.to ?? 0} of {pagination.total}
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
                                                dangerouslySetInnerHTML={{
                                                    __html: link.label,
                                                }}
                                            />
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
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
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
