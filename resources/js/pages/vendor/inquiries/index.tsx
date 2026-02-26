import { Form, Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { index as vendorInquiriesIndex } from '@/routes/vendor/inquiries';
import type { BreadcrumbItem } from '@/types';

type InquiryItem = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    subject: string;
    message: string;
    status: 'pending' | 'approved' | 'rejected';
    submitted_at: string | null;
    handled_at: string | null;
};

type Props = {
    inquiries: InquiryItem[];
    selected_status?: string | null;
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Customer Inquiries',
        href: vendorInquiriesIndex().url,
    },
];

const statuses: Array<{ label: string; value: string }> = [
    { label: 'All', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' },
];

export default function VendorInquiriesIndex() {
    const { inquiries, selected_status: selectedStatus, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Customer Inquiries" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Inbox</p>
                    <h2 className="mt-2 font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                        Customer inquiries
                    </h2>
                    <p className="mt-2 text-sm text-(--welcome-body-text)">
                        Review messages sent through your public vendor page.
                    </p>

                    <Form {...vendorInquiriesIndex.form()} className="mt-5 flex flex-wrap gap-2">
                        {statuses.map((filterStatus) => (
                            <button
                                key={filterStatus.label}
                                type="submit"
                                name="status"
                                value={filterStatus.value}
                                className={`rounded-full border px-3 py-1 text-xs uppercase tracking-[0.2em] ${
                                    (selectedStatus ?? '') === filterStatus.value
                                        ? 'border-(--welcome-strong) bg-(--welcome-strong) text-(--welcome-on-strong)'
                                        : 'border-(--welcome-border) text-(--welcome-muted-text)'
                                }`}
                            >
                                {filterStatus.label}
                            </button>
                        ))}
                    </Form>
                </div>

                {inquiries.length === 0 ? (
                    <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-8 text-center text-sm text-(--welcome-muted-text)">
                        No inquiries available for this filter.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {inquiries.map((inquiry) => (
                            <article
                                key={inquiry.id}
                                className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6"
                            >
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            {inquiry.name} • {inquiry.email}
                                        </p>
                                        <h3 className="mt-2 font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                            {inquiry.subject}
                                        </h3>
                                    </div>
                                    <span className="rounded-full border border-(--welcome-border) px-3 py-1 text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                        {inquiry.status}
                                    </span>
                                </div>
                                <p className="mt-3 text-sm text-(--welcome-body-text)">{inquiry.message}</p>
                                <div className="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                    {inquiry.phone && <span>Phone {inquiry.phone}</span>}
                                    {inquiry.submitted_at && <span>Submitted {inquiry.submitted_at}</span>}
                                    {inquiry.handled_at && <span>Handled {inquiry.handled_at}</span>}
                                </div>
                            </article>
                        ))}
                    </div>
                )}

                <div className="text-right">
                    <Link
                        href={vendorInquiriesIndex()}
                        className="text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text) underline"
                    >
                        Refresh
                    </Link>
                </div>
            </div>
        </AppLayout>
    );
}
