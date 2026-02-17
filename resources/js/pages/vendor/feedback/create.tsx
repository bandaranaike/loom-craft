import { Form, Head, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { create, store } from '@/routes/vendor/feedback';
import type { BreadcrumbItem } from '@/types';

type Props = {
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Vendor Feedback',
        href: create().url,
    },
];

export default function VendorFeedbackCreate() {
    const { status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Vendor Feedback" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                {status && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100">
                        {status}
                    </div>
                )}

                <div className="rounded-xl border border-sidebar-border/70 bg-sidebar/30 p-6 dark:border-sidebar-border">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-muted-foreground">
                            Vendor Voice
                        </p>
                        <h2 className="text-2xl font-semibold text-foreground">
                            Share your LoomCraft feedback
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Approved submissions are highlighted on the home page.
                        </p>
                    </div>
                </div>

                <div className="rounded-xl border border-sidebar-border/70 bg-background p-6 shadow-xs dark:border-sidebar-border">
                    <Form
                        {...store.form()}
                        className="grid gap-5"
                        disableWhileProcessing
                    >
                        {({ errors, processing }) => (
                            <>
                                <div className="grid gap-2">
                                    <label
                                        htmlFor="title"
                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground"
                                    >
                                        Headline
                                    </label>
                                    <input
                                        id="title"
                                        name="title"
                                        placeholder="What changed for your studio on LoomCraft?"
                                        className="w-full rounded-xl border border-sidebar-border/70 bg-background px-4 py-3 text-sm text-foreground shadow-xs focus:border-foreground focus:outline-none focus:ring-2 focus:ring-foreground/20 dark:border-sidebar-border"
                                        required
                                    />
                                    <InputError message={errors.title} className="text-xs" />
                                </div>

                                <div className="grid gap-2">
                                    <label
                                        htmlFor="details"
                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground"
                                    >
                                        Details
                                    </label>
                                    <textarea
                                        id="details"
                                        name="details"
                                        rows={6}
                                        placeholder="Share your experience as a vendor."
                                        className="w-full rounded-xl border border-sidebar-border/70 bg-background px-4 py-3 text-sm text-foreground shadow-xs focus:border-foreground focus:outline-none focus:ring-2 focus:ring-foreground/20 dark:border-sidebar-border"
                                        required
                                    />
                                    <InputError
                                        message={errors.details}
                                        className="text-xs"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    className="inline-flex w-fit items-center justify-center gap-2 rounded-full bg-foreground px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-background transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-70"
                                    disabled={processing}
                                >
                                    {processing && <Spinner />}
                                    Submit for Approval
                                </button>
                            </>
                        )}
                    </Form>
                </div>
            </div>
        </AppLayout>
    );
}
