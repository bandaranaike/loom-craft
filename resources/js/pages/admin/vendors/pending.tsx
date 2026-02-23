import { Form, Head, Link, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { approve, pending as adminVendorsPending, reject } from '@/routes/admin/vendors';
import type { BreadcrumbItem } from '@/types';

type VendorItem = {
    id: number;
    display_name: string;
    location: string | null;
    status: string;
    submitted_at: string | null;
    user_name: string;
    user_email: string;
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

type Props = {
    vendors: VendorItem[];
    status?: string;
    search?: string | null;
    per_page: number;
    pagination: Pagination;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pending Vendors',
        href: adminVendorsPending().url,
    },
];

const perPageOptions = [10, 25, 50];

export default function PendingVendors() {
    const { vendors, status, search, pagination, per_page } =
        usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pending Vendors">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex flex-col gap-2">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Review Queue
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                                Vendor applications awaiting approval
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                Approve artisans once their profile and documentation are verified.
                            </p>
                        </div>
                        <div className="flex w-full max-w-xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <Form
                                {...adminVendorsPending.form()}
                                className="flex w-full max-w-md items-center gap-3"
                            >
                                <input
                                    type="hidden"
                                    name="per_page"
                                    value={per_page}
                                />
                                <input
                                    type="search"
                                    name="search"
                                    placeholder="Search by display name"
                                    defaultValue={search ?? ''}
                                    className="w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-16px_var(--welcome-shadow)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                />
                                <button
                                    type="submit"
                                    className="inline-flex shrink-0 items-center justify-center rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-surface-3)"
                                >
                                    Search
                                </button>
                            </Form>
                            <Form
                                {...adminVendorsPending.form()}
                                className="flex items-center gap-2"
                            >
                                <input
                                    type="hidden"
                                    name="search"
                                    value={search ?? ''}
                                />
                                <label
                                    htmlFor="per_page"
                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                >
                                    Per page
                                </label>
                                <select
                                    id="per_page"
                                    name="per_page"
                                    defaultValue={per_page}
                                    className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-3 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) shadow-[0_8px_20px_-16px_var(--welcome-shadow)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
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
                            </Form>
                        </div>
                    </div>
                </div>

                {vendors.length === 0 ? (
                    <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
                        No pending vendor applications right now.
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {vendors.map((vendor) => (
                            <div
                                key={vendor.id}
                                className="flex h-full flex-col gap-4 rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]"
                            >
                                <div className="space-y-2">
                                    <div className="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                {vendor.status}
                                            </p>
                                            <h3 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                                {vendor.display_name}
                                            </h3>
                                        </div>
                                        {vendor.submitted_at && (
                                            <span className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-3 py-1 text-xs text-(--welcome-muted-text)">
                                                Submitted {vendor.submitted_at}
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        {vendor.location ?? 'Location not provided'}
                                    </p>
                                    <div className="text-sm text-(--welcome-strong)">
                                        <span className="font-semibold">
                                            {vendor.user_name}
                                        </span>{' '}
                                        <span className="text-(--welcome-muted-text)">
                                            ({vendor.user_email})
                                        </span>
                                    </div>
                                </div>

                                <div className="mt-auto grid gap-3">
                                    <Form
                                        {...approve.form(vendor.id)}
                                        className="w-full"
                                        disableWhileProcessing
                                    >
                                        {({ processing }) => (
                                            <button
                                                type="submit"
                                                className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-muted-text) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-surface-3) transition hover:-translate-y-0.5 hover:bg-(--welcome-muted-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                                disabled={processing}
                                            >
                                                {processing && <Spinner />}
                                                Approve Vendor
                                            </button>
                                        )}
                                    </Form>

                                    <Form
                                        {...reject.form(vendor.id)}
                                        className="grid gap-3"
                                        disableWhileProcessing
                                    >
                                        {({ processing, errors }) => (
                                            <>
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor={`reason-${vendor.id}`}
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Rejection reason
                                                    </label>
                                                    <textarea
                                                        id={`reason-${vendor.id}`}
                                                        name="reason"
                                                        rows={3}
                                                        placeholder="Explain why this vendor was rejected."
                                                        className="w-full rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-3 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-16px_var(--welcome-shadow)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                                        required
                                                    />
                                                    <InputError
                                                        message={errors.reason}
                                                        className="text-xs"
                                                    />
                                                </div>
                                                <button
                                                    type="submit"
                                                    className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:-translate-y-0.5 hover:bg-(--welcome-muted-text) hover:text-(--welcome-surface-3) disabled:cursor-not-allowed disabled:opacity-70"
                                                    disabled={processing}
                                                >
                                                    {processing && <Spinner />}
                                                    Reject Vendor
                                                </button>
                                            </>
                                        )}
                                    </Form>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {pagination.last_page > 1 && (
                    <div className="flex flex-wrap items-center justify-between gap-3 rounded-[24px] border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-3 text-xs text-(--welcome-muted-text)">
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
                                                ? 'border-(--welcome-muted-text) bg-(--welcome-muted-text) text-(--welcome-surface-3)'
                                                : 'border-(--welcome-muted-50) text-(--welcome-muted-text) hover:bg-(--welcome-muted-text) hover:text-(--welcome-surface-3)'
                                        }`}
                                    >
                                        {link.label}
                                    </Link>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
