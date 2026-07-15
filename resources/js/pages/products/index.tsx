import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Gift, Leaf, PackageCheck, ShoppingBag, Utensils } from 'lucide-react';
import ProductCard, { type ProductCardItem } from '@/components/product-card';
import SeoHead from '@/components/seo-head';
import { Spinner } from '@/components/ui/spinner';
import PublicSiteLayout from '@/layouts/public-site-layout';
import { resolveProductColorSwatch } from '@/lib/product-color-swatches';
import { index as productsIndex } from '@/routes/products';
import type { SharedData } from '@/types';

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
    const { site } = usePage<SharedData>().props;
    const [searchInput, setSearchInput] = useState(search ?? '');
    const [categoryFilter, setCategoryFilter] = useState(selected_category ?? '');
    const [vendorFilter, setVendorFilter] = useState(selected_vendor ?? '');
    const [selectedColorFilters, setSelectedColorFilters] = useState<string[]>(selected_colors);
    const [minPriceFilter, setMinPriceFilter] = useState(min_price !== null && min_price !== undefined ? String(min_price) : '');
    const [maxPriceFilter, setMaxPriceFilter] = useState(max_price !== null && max_price !== undefined ? String(max_price) : '');
    const [perPageFilter, setPerPageFilter] = useState(per_page);
    const [isLoading, setIsLoading] = useState(false);
    const [isMobileFilterOpen, setIsMobileFilterOpen] = useState(false);

    const applyFilters = (overrides?: { search?: string; category?: string; vendor?: string; colors?: string[]; minPrice?: string; maxPrice?: string; perPage?: number }): void => {
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
        const nextColors = selectedColorFilters.includes(slug) ? selectedColorFilters.filter((colorSlug) => colorSlug !== slug) : [...selectedColorFilters, slug];

        setSelectedColorFilters(nextColors);
        applyFilters({ colors: nextColors });
    };

    if (site.key === 'naturesnature') {
        return (
            <NaturesNatureProductIndex
                products={products}
                categories={categories}
                vendors={vendors}
                pagination={pagination}
                per_page={per_page}
                search={search}
                selected_category={selected_category}
                selected_vendor={selected_vendor}
                min_price={min_price}
                max_price={max_price}
                canRegister={canRegister}
                searchInput={searchInput}
                setSearchInput={setSearchInput}
                categoryFilter={categoryFilter}
                setCategoryFilter={setCategoryFilter}
                vendorFilter={vendorFilter}
                setVendorFilter={setVendorFilter}
                minPriceFilter={minPriceFilter}
                setMinPriceFilter={setMinPriceFilter}
                maxPriceFilter={maxPriceFilter}
                setMaxPriceFilter={setMaxPriceFilter}
                perPageFilter={perPageFilter}
                setPerPageFilter={setPerPageFilter}
                applyFilters={applyFilters}
                isLoading={isLoading}
                clearAllFilters={clearAllFilters}
            />
        );
    }

    return (
        <>
            <SeoHead
                title={`${site.productsLabel} — ${site.displayName}`}
                description={
                    site.key === 'naturesnature'
                        ? 'Browse organic homemade cookies, pantry foods, and curated food gifts from independent makers.'
                        : 'Browse approved LoomCraft products, handmade by verified artisans and curated for collectors.'
                }
                canonical="/products"
            >
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600" rel="stylesheet" />
            </SeoHead>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-8 px-6 pt-6 pb-12 lg:grid-cols-[1.1fr_0.9fr]">
                    <div className="space-y-4">
                        <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                            {site.key === 'naturesnature' ? 'Fresh From Makers' : 'Approved Collection'}
                        </div>
                        <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                            {site.key === 'naturesnature' ? 'Discover homemade foods with a rich organic finish.' : 'Discover heritage pieces curated for collectors.'}
                        </h1>
                        <p className="max-w-xl text-sm text-(--welcome-body-text) md:text-base">
                            {site.key === 'naturesnature'
                                ? 'Shop cookies, pantry treats, and gift-ready food boxes prepared by approved homemade food makers.'
                                : 'Only LoomCraft-approved textiles are presented here, each woven by verified artisans with documented provenance.'}
                        </p>
                    </div>

                    <div className="rounded-[36px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow-medium)]">
                        <div className="space-y-4">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Refine results</p>
                                <div className="flex items-center gap-2">
                                    {isLoading && <Spinner className="text-(--welcome-strong)" />}
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

                            <button
                                type="button"
                                onClick={() => setIsMobileFilterOpen((currentState) => !currentState)}
                                className="inline-flex w-full items-center justify-between rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase md:hidden"
                            >
                                Filters
                                <span>{isMobileFilterOpen ? 'Hide' : 'Show'}</span>
                            </button>

                            <div
                                className={`grid gap-4 overflow-hidden transition-all duration-300 md:grid md:overflow-visible ${
                                    isMobileFilterOpen ? 'max-h-250 opacity-100' : 'max-h-0 opacity-0 md:max-h-none md:opacity-100'
                                }`}
                            >
                                <div className="grid gap-3 md:grid-cols-2">
                                    <div className="col-span-2 grid gap-2">
                                        <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">{site.vendorLabel}</span>
                                        <select
                                            value={vendorFilter}
                                            onChange={(event) => {
                                                const nextVendor = event.currentTarget.value;
                                                setVendorFilter(nextVendor);
                                                applyFilters({
                                                    vendor: nextVendor,
                                                });
                                            }}
                                            className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                        >
                                            <option value="">All</option>
                                            {vendors.map((vendor) => (
                                                <option key={vendor.id} value={vendor.slug}>
                                                    {vendor.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>

                                    <div className="grid gap-2">
                                        <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Min price</span>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={minPriceFilter}
                                            onChange={(event) => {
                                                const nextMinPrice = event.currentTarget.value;
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
                                        <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Max price</span>
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={maxPriceFilter}
                                            onChange={(event) => {
                                                const nextMaxPrice = event.currentTarget.value;
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
                                    <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Per page</span>
                                    <select
                                        value={perPageFilter}
                                        onChange={(event) => {
                                            const nextPerPage = Number(event.currentTarget.value);
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
                                        <p className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Colors</p>
                                        <div className="flex flex-wrap gap-1">
                                            {colors.map((color) => {
                                                const checked = selectedColorFilters.includes(color.slug);

                                                return (
                                                    <button
                                                        key={color.id}
                                                        type="button"
                                                        title={color.name}
                                                        onClick={() => toggleColor(color.slug)}
                                                        className={`relative grid h-8 w-8 cursor-pointer place-items-center rounded-md border p-1 transition ${
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

                <section className="mx-auto w-full max-w-6xl px-6 pb-6">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div className="w-full max-w-md space-y-2">
                            <input
                                id="product-search"
                                type="search"
                                placeholder="Search by product name"
                                value={searchInput}
                                onChange={(event) => setSearchInput(event.currentTarget.value)}
                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                            />
                        </div>

                        <div className="flex flex-wrap gap-2 lg:justify-end">
                            <button
                                type="button"
                                onClick={() => {
                                    setCategoryFilter('');
                                    applyFilters({ category: '' });
                                }}
                                className={`rounded-full border px-3 py-1 text-xs tracking-[0.2em] uppercase ${
                                    categoryFilter === ''
                                        ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                        : 'border-(--welcome-border) text-(--welcome-muted-text)'
                                }`}
                            >
                                All
                            </button>
                            {categories.map((category) => (
                                <button
                                    key={category.id}
                                    type="button"
                                    onClick={() => {
                                        setCategoryFilter(category.slug);
                                        applyFilters({
                                            category: category.slug,
                                        });
                                    }}
                                    className={`rounded-full border px-3 py-1 text-xs tracking-[0.2em] uppercase ${
                                        categoryFilter === category.slug
                                            ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                            : 'border-(--welcome-border) text-(--welcome-muted-text)'
                                    }`}
                                >
                                    {category.name}
                                </button>
                            ))}
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
                                    Category: {categories.find((category) => category.slug === categoryFilter)?.name ?? categoryFilter} ×
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
                                    Vendor: {vendors.find((vendor) => vendor.slug === vendorFilter)?.name ?? vendorFilter} ×
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
                                    Color: {colors.find((color) => color.slug === slug)?.name ?? slug} ×
                                </button>
                            ))}
                        </div>
                    </section>
                )}

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    {products.length === 0 ? (
                        <div className="rounded-4xl border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
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
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>
                    )}
                </section>

                {pagination.last_page > 1 && (
                    <section className="mx-auto w-full max-w-6xl px-6 pb-20">
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-3 text-xs text-(--welcome-muted-text)">
                            <div>
                                Showing {pagination.from ?? 0} - {pagination.to ?? 0} of {pagination.total}
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {pagination.links.map((link) => {
                                    const key = `${link.label}-${link.url}`;
                                    const baseClass = 'rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] transition';

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

type NaturesNatureProductIndexProps = Omit<ProductIndexProps, 'colors' | 'selected_colors'> & {
    searchInput: string;
    setSearchInput: (value: string) => void;
    categoryFilter: string;
    setCategoryFilter: (value: string) => void;
    vendorFilter: string;
    setVendorFilter: (value: string) => void;
    minPriceFilter: string;
    setMinPriceFilter: (value: string) => void;
    maxPriceFilter: string;
    setMaxPriceFilter: (value: string) => void;
    perPageFilter: number;
    setPerPageFilter: (value: number) => void;
    applyFilters: (overrides?: { search?: string; category?: string; vendor?: string; minPrice?: string; maxPrice?: string; perPage?: number }) => void;
    isLoading: boolean;
    clearAllFilters: () => void;
};

function NaturesNatureProductIndex({
    products,
    categories,
    vendors,
    pagination,
    per_page,
    search,
    selected_category,
    selected_vendor,
    min_price,
    max_price,
    canRegister = true,
    searchInput,
    setSearchInput,
    categoryFilter,
    setCategoryFilter,
    vendorFilter,
    setVendorFilter,
    minPriceFilter,
    setMinPriceFilter,
    maxPriceFilter,
    setMaxPriceFilter,
    perPageFilter,
    setPerPageFilter,
    applyFilters,
    isLoading,
    clearAllFilters,
}: NaturesNatureProductIndexProps) {
    const { site } = usePage<SharedData>().props;

    return (
        <>
            <SeoHead
                title={`${site.productsLabel} — ${site.displayName}`}
                description="Browse cookies, pantry treats, and curated gift boxes from approved homemade food makers."
                canonical="/products"
            >
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600" rel="stylesheet" />
            </SeoHead>
            <PublicSiteLayout canRegister={canRegister}>
                <section className="mx-auto w-full max-w-6xl px-6 pt-6 pb-12">
                    <div className="rounded-[42px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-6 shadow-[0_28px_70px_-44px_var(--welcome-shadow-heavy)] md:p-8">
                        <div className="grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                            <div className="space-y-5">
                                <div className="inline-flex items-center gap-3 rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-2 text-xs tracking-[0.28em] text-(--welcome-muted-text) uppercase">
                                    Fresh from the kitchen
                                </div>
                                <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight text-(--welcome-strong) md:text-5xl">
                                    Homemade food, pantry treats, and gift boxes.
                                </h1>
                                <p className="max-w-2xl text-sm leading-7 text-(--welcome-body-text) md:text-base">
                                    Browse approved makers and find fresh cookies, rich pantry staples, and food gifts packed with care.
                                </p>
                                <div className="flex flex-wrap gap-3">
                                    <Link
                                        href={productsIndex().url}
                                        className="rounded-full bg-(--nature-leaf) px-5 py-3 text-xs font-semibold tracking-[0.24em] text-(--welcome-on-strong) uppercase"
                                    >
                                        Shop all
                                    </Link>
                                    <Link
                                        href="#product-list"
                                        className="rounded-full border border-(--welcome-strong) bg-(--welcome-surface-3) px-5 py-3 text-xs font-semibold tracking-[0.24em] text-(--welcome-strong) uppercase"
                                    >
                                        Browse products
                                    </Link>
                                </div>
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="rounded-[18px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5 shadow-[0_18px_45px_-36px_var(--welcome-shadow)]">
                                    <Utensils className="h-6 w-6 text-(--nature-leaf)" />
                                    <p className="mt-4 text-xs tracking-[0.28em] text-(--welcome-muted-text) uppercase">Made fresh</p>
                                    <h2 className="mt-2 font-['Playfair_Display',serif] text-2xl">Cookies & bakes</h2>
                                </div>
                                <div className="rounded-[18px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5 shadow-[0_18px_45px_-36px_var(--welcome-shadow)]">
                                    <Gift className="h-6 w-6 text-(--nature-orange)" />
                                    <p className="mt-4 text-xs tracking-[0.28em] text-(--welcome-muted-text) uppercase">Curated gifts</p>
                                    <h2 className="mt-2 font-['Playfair_Display',serif] text-2xl">Packed for giving</h2>
                                </div>
                                <div className="rounded-[18px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5 shadow-[0_18px_45px_-36px_var(--welcome-shadow)]">
                                    <Leaf className="h-6 w-6 text-(--nature-leaf)" />
                                    <p className="mt-4 text-xs tracking-[0.28em] text-(--welcome-muted-text) uppercase">Natural ingredients</p>
                                    <h2 className="mt-2 font-['Playfair_Display',serif] text-2xl">Simple pantry goods</h2>
                                </div>
                                <div className="rounded-[18px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5 shadow-[0_18px_45px_-36px_var(--welcome-shadow)]">
                                    <PackageCheck className="h-6 w-6 text-(--nature-red)" />
                                    <p className="mt-4 text-xs tracking-[0.28em] text-(--welcome-muted-text) uppercase">Approved</p>
                                    <h2 className="mt-2 font-['Playfair_Display',serif] text-2xl">Maker verified</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl px-6 pb-6">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div className="w-full max-w-md space-y-2">
                            <input
                                id="product-search"
                                type="search"
                                placeholder="Search cookies, pantry items, or boxes"
                                value={searchInput}
                                onChange={(event) => setSearchInput(event.currentTarget.value)}
                                className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                            />
                        </div>

                        <div className="flex flex-wrap gap-2 lg:justify-end">
                            {['', ...categories.map((category) => category.slug)].map((slug) => {
                                const label = slug === '' ? 'All' : (categories.find((category) => category.slug === slug)?.name ?? slug);
                                const active = categoryFilter === slug;

                                return (
                                    <button
                                        key={slug || 'all'}
                                        type="button"
                                        onClick={() => {
                                            setCategoryFilter(slug);
                                            applyFilters({ category: slug });
                                        }}
                                        className={`rounded-full border px-3 py-1 text-xs tracking-[0.2em] uppercase ${
                                            active
                                                ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                                : 'border-(--welcome-border) text-(--welcome-muted-text)'
                                        }`}
                                    >
                                        {label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </section>

                <section className="mx-auto w-full max-w-6xl space-y-8 px-6 pb-6">
                    <aside className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-5">
                        <div className="flex items-center justify-between gap-3">
                            <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Refine</p>
                            <div className="flex items-center gap-2">
                                {isLoading && <Spinner className="text-(--welcome-strong)" />}
                                {(searchInput.trim() !== '' || categoryFilter !== '' || vendorFilter !== '' || minPriceFilter.trim() !== '' || maxPriceFilter.trim() !== '') && (
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
                        <div className="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <label className="grid gap-2">
                                <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Maker</span>
                                <select
                                    value={vendorFilter}
                                    onChange={(event) => {
                                        const nextVendor = event.currentTarget.value;
                                        setVendorFilter(nextVendor);
                                        applyFilters({ vendor: nextVendor });
                                    }}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                >
                                    <option value="">All makers</option>
                                    {vendors.map((vendor) => (
                                        <option key={vendor.id} value={vendor.slug}>
                                            {vendor.name}
                                        </option>
                                    ))}
                                </select>
                            </label>
                            <label className="grid gap-2">
                                <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Min price</span>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={minPriceFilter}
                                    onChange={(event) => {
                                        const nextMinPrice = event.currentTarget.value;
                                        setMinPriceFilter(nextMinPrice);
                                        applyFilters({ minPrice: nextMinPrice });
                                    }}
                                    placeholder="0.00"
                                    className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                />
                            </label>
                            <label className="grid gap-2">
                                <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Max price</span>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={maxPriceFilter}
                                    onChange={(event) => {
                                        const nextMaxPrice = event.currentTarget.value;
                                        setMaxPriceFilter(nextMaxPrice);
                                        applyFilters({ maxPrice: nextMaxPrice });
                                    }}
                                    placeholder="0.00"
                                    className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-4 py-3 text-sm text-(--welcome-strong) shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                />
                            </label>
                            <label className="grid gap-2">
                                <span className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Per page</span>
                                <select
                                    value={perPageFilter}
                                    onChange={(event) => {
                                        const nextPerPage = Number(event.currentTarget.value);
                                        setPerPageFilter(nextPerPage);
                                        applyFilters({ perPage: nextPerPage });
                                    }}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-3) px-3 py-3 text-xs font-semibold tracking-[0.3em] text-(--welcome-strong) uppercase shadow-xs focus:border-(--welcome-strong) focus:ring-2 focus:ring-(--welcome-strong-20) focus:outline-none"
                                >
                                    {[9, 12, 24].map((option) => (
                                        <option key={option} value={option}>
                                            {option}
                                        </option>
                                    ))}
                                </select>
                            </label>
                        </div>
                    </aside>

                    <div id="product-list" className="mx-auto w-full max-w-6xl space-y-4">
                        <div className="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Products</p>
                                <h2 className="mt-1 font-['Playfair_Display',serif] text-3xl">Fresh batches and gift-ready packs</h2>
                            </div>
                            <p className="text-xs tracking-[0.24em] text-(--welcome-muted-text) uppercase">{pagination.total} items</p>
                        </div>

                        {products.length === 0 ? (
                            <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
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
                            <div className="grid gap-6 md:grid-cols-3">
                                {products.map((product) => (
                                    <ProductCard key={product.id} product={product} />
                                ))}
                            </div>
                        )}

                        {pagination.last_page > 1 && (
                            <div className="flex flex-wrap items-center justify-between gap-3 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-3 text-xs text-(--welcome-muted-text)">
                                <div>
                                    Showing {pagination.from ?? 0} - {pagination.to ?? 0} of {pagination.total}
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {pagination.links.map((link) => {
                                        const key = `${link.label}-${link.url}`;
                                        const baseClass = 'rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] transition';

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
                        )}
                    </div>
                </section>
            </PublicSiteLayout>
        </>
    );
}
