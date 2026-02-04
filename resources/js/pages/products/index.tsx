import { Form, Head, Link, usePage } from '@inertiajs/react';
import { dashboard, home, login, register } from '@/routes';
import { index as productsIndex, show as productShow } from '@/routes/products';
import type { SharedData } from '@/types';

type ProductItem = {
    id: number;
    name: string;
    selling_price: string;
    vendor_name: string;
    vendor_location: string | null;
    image_url: string | null;
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
    pagination: Pagination;
    per_page: number;
    search: string | null;
    canRegister?: boolean;
};

const perPageOptions = [9, 12, 24];

export default function ProductIndex({
    products,
    pagination,
    per_page,
    search,
    canRegister = true,
}: ProductIndexProps) {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Products — LoomCraft">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="min-h-screen bg-[#F6F1E8] text-[#2b241c]">
                <div className="relative overflow-hidden">
                    <div className="pointer-events-none absolute -left-40 top-0 h-[420px] w-[420px] rounded-full bg-[radial-gradient(circle_at_top,_#c77b45,_transparent_65%)] opacity-40" />
                    <div className="pointer-events-none absolute -right-32 top-20 h-[360px] w-[360px] rounded-full bg-[radial-gradient(circle,_#a14d2a,_transparent_68%)] opacity-30" />
                    <div className="pointer-events-none absolute bottom-0 left-1/2 h-[320px] w-[720px] -translate-x-1/2 rounded-[100%] bg-[radial-gradient(ellipse_at_center,_#e0c7a7,_transparent_70%)] opacity-60" />

                    <header className="relative z-10 mx-auto flex w-full max-w-6xl items-center justify-between gap-6 px-6 pb-6 pt-8">
                        <Link href={home()} className="flex items-center gap-3">
                            <div className="grid h-12 w-12 place-items-center rounded-full border border-[#2b241c] bg-[#f2e4d4] text-lg font-semibold tracking-[0.08em]">
                                LC
                            </div>
                            <div>
                                <p className="text-sm uppercase tracking-[0.3em] text-[#7a5a3a]">
                                    LoomCraft
                                </p>
                                <p className="font-['Playfair_Display',serif] text-xl">
                                    Woven Heritage House
                                </p>
                            </div>
                        </Link>
                        <nav className="flex flex-wrap items-center gap-3 text-sm">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="rounded-full border border-[#2b241c] px-4 py-2 font-medium transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                >
                                    Enter Atelier
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="rounded-full border border-transparent px-4 py-2 font-medium text-[#2b241c]/70 transition hover:border-[#2b241c] hover:text-[#2b241c]"
                                    >
                                        Log in
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="rounded-full border border-[#2b241c] px-4 py-2 font-medium transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                        >
                                            Become a Patron
                                        </Link>
                                    )}
                                </>
                            )}
                        </nav>
                    </header>

                    <section className="relative z-10 mx-auto grid w-full max-w-6xl gap-8 px-6 pb-12 pt-6 lg:grid-cols-[1.1fr_0.9fr]">
                        <div className="space-y-4">
                            <div className="inline-flex items-center gap-3 rounded-full border border-[#d4b28c] bg-[#f9efe2] px-4 py-2 text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                Approved Collection
                            </div>
                            <h1 className="font-['Playfair_Display',serif] text-4xl leading-tight md:text-5xl">
                                Discover heritage pieces curated for collectors.
                            </h1>
                            <p className="max-w-xl text-sm text-[#5a4a3a] md:text-base">
                                Only LoomCraft-approved textiles are presented here — each woven
                                by verified artisans with documented provenance.
                            </p>
                        </div>
                        <div className="rounded-[36px] border border-[#d4b28c] bg-[#f9efe2] p-6 shadow-[0_30px_80px_-45px_rgba(43,36,28,0.5)]">
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
                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]"
                                >
                                    Search the catalog
                                </label>
                                <input
                                    id="search"
                                    type="search"
                                    name="search"
                                    placeholder="Search by product name"
                                    defaultValue={search ?? ''}
                                    className="w-full rounded-full border border-[#d4b28c] bg-[#fff8ed] px-4 py-2 text-sm text-[#2b241c] shadow-xs focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20"
                                />
                                <div className="flex flex-wrap items-center gap-3">
                                    <button
                                        type="submit"
                                        className="inline-flex items-center justify-center rounded-full border border-[#2b241c] px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] transition hover:bg-[#2b241c] hover:text-[#f6f1e8]"
                                    >
                                        Explore
                                    </button>
                                    <div className="flex items-center gap-2">
                                        <span className="text-xs font-semibold uppercase tracking-[0.3em] text-[#7a5a3a]">
                                            Per page
                                        </span>
                                        <select
                                            name="per_page"
                                            defaultValue={per_page}
                                            className="rounded-full border border-[#d4b28c] bg-[#fff8ed] px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-[#2b241c] shadow-xs focus:border-[#2b241c] focus:outline-none focus:ring-2 focus:ring-[#2b241c]/20"
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
                </div>

                <section className="mx-auto w-full max-w-6xl px-6 pb-16">
                    {products.length === 0 ? (
                        <div className="rounded-[32px] border border-dashed border-[#d4b28c] bg-[#fff8ed] p-10 text-center text-sm text-[#7a5a3a]">
                            No approved products yet. Please check back soon.
                        </div>
                    ) : (
                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {products.map((product) => (
                                <Link
                                    key={product.id}
                                    href={productShow(product.id).url}
                                    className="group flex h-full flex-col overflow-hidden rounded-[32px] border border-[#e0c7a7] bg-[#fff8ed] transition hover:-translate-y-1 hover:border-[#b6623a]"
                                >
                                    <div className="relative aspect-[4/3] overflow-hidden bg-[#f9efe2]">
                                        {product.image_url ? (
                                            <img
                                                src={product.image_url}
                                                alt={product.name}
                                                className="h-full w-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                            />
                                        ) : (
                                            <div className="flex h-full w-full items-center justify-center text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                Image forthcoming
                                            </div>
                                        )}
                                    </div>
                                    <div className="flex flex-1 flex-col gap-3 p-5">
                                        <div className="space-y-2">
                                            <p className="text-xs uppercase tracking-[0.3em] text-[#7a5a3a]">
                                                {product.vendor_name}
                                                {product.vendor_location
                                                    ? ` • ${product.vendor_location}`
                                                    : ''}
                                            </p>
                                            <h3 className="font-['Playfair_Display',serif] text-xl">
                                                {product.name}
                                            </h3>
                                        </div>
                                        <div className="mt-auto flex items-center justify-between text-sm">
                                            <span className="text-[#5a4a3a]">
                                                Selling price
                                            </span>
                                            <span className="font-semibold text-[#2b241c]">
                                                {product.selling_price} USD
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    )}
                </section>

                {pagination.last_page > 1 && (
                    <section className="mx-auto w-full max-w-6xl px-6 pb-20">
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-[28px] border border-[#e0c7a7] bg-[#fff8ed] px-4 py-3 text-xs text-[#7a5a3a]">
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
                                                className={`${baseClass} border-[#d4b28c] text-[#7a5a3a]/60`}
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
                                                    ? 'border-[#2b241c] bg-[#2b241c] text-[#f6f1e8]'
                                                    : 'border-[#2b241c]/50 text-[#2b241c] hover:bg-[#2b241c] hover:text-[#f6f1e8]'
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
            </div>
        </>
    );
}
