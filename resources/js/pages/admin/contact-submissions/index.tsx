import { Form, Head, usePage } from '@inertiajs/react';
import ContactSubmissionController from '@/actions/App/Http/Controllers/Admin/ContactSubmissionController';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';

type ContactSubmissionItem = {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    message: string;
    status: string;
    submitted_at: string | null;
    replied_at: string | null;
    latest_reply_message: string | null;
    customer_name: string | null;
};

type StatusOption = {
    value: string;
    label: string;
};

type Props = {
    submissions: ContactSubmissionItem[];
    statusOptions: StatusOption[];
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contact Messages',
        href: ContactSubmissionController.index().url,
    },
];

export default function ContactSubmissionsIndex() {
    const { submissions, statusOptions, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contact Messages" />

            <div className="flex h-full min-w-0 flex-1 flex-col gap-6 overflow-x-hidden rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Admin Inbox</p>
                    <h2 className="mt-2 font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">Contact messages</h2>
                    <p className="mt-2 text-sm text-(--welcome-body-text)">
                        Review inbound contact-us submissions, update their status, and send replies by email.
                    </p>
                </div>

                {submissions.length === 0 ? (
                    <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
                        No contact messages have been submitted yet.
                    </div>
                ) : (
                    <div className="grid gap-4 xl:grid-cols-2">
                        {submissions.map((submission) => (
                            <article
                                key={submission.id}
                                className="flex flex-col gap-5 rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]"
                            >
                                <div className="space-y-3">
                                    <div className="flex flex-wrap items-center gap-3">
                                        <span className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.25em] text-(--welcome-muted-text)">
                                            {submission.status.replaceAll('_', ' ')}
                                        </span>
                                        {submission.submitted_at && (
                                            <span className="text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                                Submitted {submission.submitted_at}
                                            </span>
                                        )}
                                    </div>

                                    <div>
                                        <h3 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                            {submission.name}
                                        </h3>
                                        <div className="mt-2 space-y-1 text-sm text-(--welcome-body-text)">
                                            {submission.email && <p>Email: {submission.email}</p>}
                                            {submission.phone && <p>Phone: {submission.phone}</p>}
                                            {submission.customer_name && <p>Linked user: {submission.customer_name}</p>}
                                        </div>
                                    </div>

                                    <p className="rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-4 text-sm leading-6 text-(--welcome-body-text)">
                                        {submission.message}
                                    </p>

                                    {submission.latest_reply_message && (
                                        <div className="rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-on-strong) px-4 py-4">
                                            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">Latest reply</p>
                                            <p className="mt-2 text-sm leading-6 text-(--welcome-body-text)">
                                                {submission.latest_reply_message}
                                            </p>
                                            {submission.replied_at && (
                                                <p className="mt-3 text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                                    Sent {submission.replied_at}
                                                </p>
                                            )}
                                        </div>
                                    )}
                                </div>

                                <div className="grid gap-4 lg:grid-cols-[220px_1fr]">
                                    <Form
                                        action={ContactSubmissionController.updateStatus.url(submission.id)}
                                        method="patch"
                                        disableWhileProcessing
                                    >
                                        {({ processing }) => (
                                            <div className="space-y-3">
                                                <label
                                                    htmlFor={`status-${submission.id}`}
                                                    className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                >
                                                    Update status
                                                </label>
                                                <select
                                                    id={`status-${submission.id}`}
                                                    name="status"
                                                    defaultValue={submission.status}
                                                    className="w-full rounded-[18px] border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-3 text-sm text-(--welcome-strong) focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                                >
                                                    {statusOptions.map((option) => (
                                                        <option key={option.value} value={option.value}>
                                                            {option.label}
                                                        </option>
                                                    ))}
                                                </select>
                                                <button
                                                    type="submit"
                                                    disabled={processing}
                                                    className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                                >
                                                    {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                                    Save status
                                                </button>
                                            </div>
                                        )}
                                    </Form>

                                    <Form
                                        action={ContactSubmissionController.reply.url(submission.id)}
                                        method="post"
                                        disableWhileProcessing
                                        resetOnSuccess={['reply_message']}
                                    >
                                        {({ processing, errors }) => (
                                            <div className="space-y-3">
                                                <div className="flex flex-wrap items-center justify-between gap-3">
                                                    <label
                                                        htmlFor={`reply-${submission.id}`}
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Reply by email
                                                    </label>
                                                    {!submission.email && (
                                                        <span className="text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                                            No email available
                                                        </span>
                                                    )}
                                                </div>
                                                <textarea
                                                    id={`reply-${submission.id}`}
                                                    name="reply_message"
                                                    className="min-h-36 w-full rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-4 text-sm text-(--welcome-strong) focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)"
                                                    placeholder="Write the email response to this message."
                                                />
                                                {errors.reply_message && (
                                                    <p className="text-xs text-red-600">{errors.reply_message}</p>
                                                )}
                                                <button
                                                    type="submit"
                                                    disabled={processing || !submission.email}
                                                    className="inline-flex w-full items-center justify-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-3 text-xs font-semibold uppercase tracking-[0.2em] text-(--welcome-muted-text) disabled:cursor-not-allowed disabled:opacity-70"
                                                >
                                                    {processing && <Spinner />}
                                                    Send reply
                                                </button>
                                            </div>
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
