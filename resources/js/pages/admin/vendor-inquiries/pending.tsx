import { Form, Head, Link, usePage } from '@inertiajs/react';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { approve, pending, reject } from '@/routes/admin/vendor-inquiries';
import { show as vendorShow } from '@/routes/vendors';
import type { BreadcrumbItem } from '@/types';

type InquiryItem = {
    id: number;
    vendor_id: number;
    vendor_name: string;
    vendor_slug: string | null;
    name: string;
    email: string;
    phone: string | null;
    subject: string;
    message: string;
    submitted_at: string | null;
};

type Props = {
    inquiries: InquiryItem[];
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inquiry Moderation',
        href: pending().url,
    },
];

export default function PendingVendorInquiries() {
    const { inquiries, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inquiry Moderation" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Admin Queue</p>
                    <h2 className="mt-2 font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                        Vendor inquiry moderation
                    </h2>
                    <p className="mt-2 text-sm text-(--welcome-body-text)">
                        Review incoming vendor leads and mark each inquiry as approved or rejected.
                    </p>
                </div>

                {inquiries.length === 0 ? (
                    <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
                        No pending inquiries right now.
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {inquiries.map((inquiry) => (
                            <article
                                key={inquiry.id}
                                className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6"
                            >
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    {inquiry.vendor_slug ? (
                                        <Link href={vendorShow(inquiry.vendor_slug)}>
                                            {inquiry.vendor_name}
                                        </Link>
                                    ) : (
                                        inquiry.vendor_name
                                    )}
                                </p>
                                <h3 className="mt-2 font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                    {inquiry.subject}
                                </h3>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">{inquiry.message}</p>

                                <div className="mt-4 text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                    <p>{inquiry.name} • {inquiry.email}</p>
                                    {inquiry.phone && <p className="mt-1">Phone {inquiry.phone}</p>}
                                    {inquiry.submitted_at && <p className="mt-1">Submitted {inquiry.submitted_at}</p>}
                                </div>

                                <div className="mt-5 grid gap-2 sm:grid-cols-2">
                                    <Form {...approve.form(inquiry.id)} disableWhileProcessing>
                                        {({ processing }) => (
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                            >
                                                {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                                Approve
                                            </button>
                                        )}
                                    </Form>

                                    <Form {...reject.form(inquiry.id)} disableWhileProcessing>
                                        {({ processing }) => (
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-(--welcome-muted-text) disabled:cursor-not-allowed disabled:opacity-70"
                                            >
                                                {processing && <Spinner />}
                                                Reject
                                            </button>
                                        )}
                                    </Form>
                                </div>
                            </article>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
