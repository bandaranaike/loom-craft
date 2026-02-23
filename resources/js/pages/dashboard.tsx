import { Head, Link, usePage } from '@inertiajs/react';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { pending as adminVendorPending } from '@/routes/admin/vendors';
import { register as vendorRegister } from '@/routes/vendor';
import type { SharedData } from '@/types';
import type { BreadcrumbItem } from '@/types';

type Props = {
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    const { status, auth } = usePage<SharedData & Props>().props;
    const isAdmin = auth?.user?.role === 'admin';

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|work-sans:300,400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-3xl bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-3xl border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="relative flex h-full flex-col justify-between overflow-hidden rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                        <div className="space-y-2">
                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                Vendor Access
                            </p>
                            <h2 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                Apply to become a vendor
                            </h2>
                            <p className="text-sm text-(--welcome-body-text)">
                                Submit your artisan profile for manual approval.
                            </p>
                        </div>
                        <Link
                            href={vendorRegister()}
                            className="mt-4 inline-flex w-fit items-center rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-surface-3)"
                        >
                            Start Application
                        </Link>
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Atelier Pulse
                        </p>
                        <p className="mt-2 font-['Playfair_Display',serif] text-xl text-(--welcome-strong)">
                            Marketplace readiness
                        </p>
                        <PlaceholderPattern className="absolute inset-x-4 bottom-4 top-20 stroke-(--welcome-muted-20)" />
                    </div>
                    <div className="relative aspect-video overflow-hidden rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-4 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Craft Ledger
                        </p>
                        <p className="mt-2 font-['Playfair_Display',serif] text-xl text-(--welcome-strong)">
                            Approval workflow
                        </p>
                        <PlaceholderPattern className="absolute inset-x-4 bottom-4 top-20 stroke-(--welcome-muted-20)" />
                    </div>
                </div>
                {isAdmin && (
                    <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Admin Review
                                </p>
                                <h2 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                    Pending vendor approvals
                                </h2>
                                <p className="text-sm text-(--welcome-body-text)">
                                    Review and approve artisan applications.
                                </p>
                            </div>
                            <Link
                                href={adminVendorPending()}
                                className="inline-flex items-center rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-surface-3)"
                            >
                                Review Vendors
                            </Link>
                        </div>
                    </div>
                )}
                <div className="relative min-h-[52vh] flex-1 overflow-hidden rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-6 md:min-h-min">
                    <div className="space-y-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Studio Workspace
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                            Dashboard canvas
                        </h2>
                        <p className="max-w-2xl text-sm text-(--welcome-body-text)">
                            This space is prepared for upcoming analytics and artisan operations
                            modules.
                        </p>
                    </div>
                    <PlaceholderPattern className="absolute inset-x-6 bottom-6 top-28 stroke-(--welcome-muted-20)" />
                </div>
            </div>
        </AppLayout>
    );
}
