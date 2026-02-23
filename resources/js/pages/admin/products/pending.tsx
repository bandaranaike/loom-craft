import { Form, Head, usePage } from '@inertiajs/react';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import { approve, pending } from '@/routes/admin/products';
import type { BreadcrumbItem } from '@/types';

type ProductItem = {
    id: number;
    name: string;
    status: string;
    vendor_name: string;
    vendor_price: string;
    selling_price: string;
    submitted_at: string | null;
};

type Props = {
    products: ProductItem[];
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pending Products',
        href: pending().url,
    },
];

export default function PendingProducts() {
    const { products, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pending Products">
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
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Review Queue
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                            Products awaiting admin approval
                        </h2>
                        <p className="text-sm text-(--welcome-body-text)">
                            Approve pending products to make them active on the marketplace.
                        </p>
                    </div>
                </div>

                {products.length === 0 ? (
                    <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
                        No products are pending review right now.
                    </div>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {products.map((product) => (
                            <div
                                key={product.id}
                                className="flex h-full flex-col gap-4 rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]"
                            >
                                <div className="space-y-2">
                                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                        {product.status}
                                    </p>
                                    <h3 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                                        {product.name}
                                    </h3>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        Vendor: {product.vendor_name}
                                    </p>
                                    <p className="text-sm text-(--welcome-body-text)">
                                        Vendor price: ${product.vendor_price}
                                    </p>
                                    <p className="text-sm text-(--welcome-strong)">
                                        Selling price: ${product.selling_price}
                                    </p>
                                    {product.submitted_at && (
                                        <p className="text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                            Submitted {product.submitted_at}
                                        </p>
                                    )}
                                </div>

                                <Form
                                    {...approve.form(product.id)}
                                    className="mt-auto"
                                    disableWhileProcessing
                                >
                                    {({ processing }) => (
                                        <button
                                            type="submit"
                                            className="inline-flex w-full items-center justify-center gap-2 rounded-full bg-(--welcome-muted-text) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-surface-3) transition hover:-translate-y-0.5 hover:bg-(--welcome-muted-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Approve Product
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
