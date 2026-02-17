import { Form, Head, usePage } from '@inertiajs/react';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { approve, pending } from '@/routes/admin/feedback';
import type { BreadcrumbItem } from '@/types';

type FeedbackItem = {
    id: number;
    title: string;
    details: string;
    vendor_name: string;
    submitted_at: string | null;
};

type Props = {
    feedback: FeedbackItem[];
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pending Feedback',
        href: pending().url,
    },
];

export default function PendingFeedback() {
    const { feedback, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pending Feedback" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {status && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100">
                        {status}
                    </div>
                )}

                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Curation Queue
                        </p>
                        <h2 className="text-2xl font-semibold text-foreground">
                            Vendor feedback awaiting approval
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Approve responses to publish them on the home page.
                        </p>
                    </div>
                </div>

                {feedback.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-sidebar-border/80 p-10 text-center text-sm text-muted-foreground dark:border-sidebar-border">
                        No pending vendor feedback right now.
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {feedback.map((item) => (
                            <div
                                key={item.id}
                                className="flex h-full flex-col gap-4 rounded-xl border border-sidebar-border/70 bg-background p-6 shadow-xs dark:border-sidebar-border"
                            >
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                                        {item.vendor_name}
                                    </p>
                                    <h3 className="text-xl font-semibold text-foreground">
                                        {item.title}
                                    </h3>
                                    <p className="text-sm text-muted-foreground">
                                        {item.details}
                                    </p>
                                    {item.submitted_at && (
                                        <p className="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                                            Submitted {item.submitted_at}
                                        </p>
                                    )}
                                </div>

                                <Form
                                    {...approve.form(item.id)}
                                    className="mt-auto"
                                    disableWhileProcessing
                                >
                                    {({ processing }) => (
                                        <button
                                            type="submit"
                                            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-foreground px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-background transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Approve Feedback
                                        </button>
                                    )}
                                </Form>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
