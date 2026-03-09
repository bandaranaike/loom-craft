import { Form, Head, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import {
    destroy as productColorDestroy,
    index as productColorsIndex,
    store as productColorStore,
    update as productColorUpdate,
} from '@/routes/admin/product-colors';
import type { BreadcrumbItem } from '@/types';

type ColorItem = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    sort_order: number;
    products_count: number;
};

type Props = {
    colors: ColorItem[];
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Product Colors',
        href: productColorsIndex().url,
    },
];

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-16px_var(--welcome-shadow)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function ProductColorsIndex() {
    const { colors, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Product Colors" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                        Color Dictionary
                    </p>
                    <h2 className="mt-2 font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                        Manage product colors
                    </h2>
                    <p className="mt-2 text-sm text-(--welcome-body-text)">
                        Maintain shopper-friendly color filters for products.
                    </p>
                </div>

                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                    <h3 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                        Create color
                    </h3>
                    <Form {...productColorStore.form()} className="mt-4 grid gap-4" disableWhileProcessing>
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <label htmlFor="new-name" className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Name
                                        </label>
                                        <input id="new-name" name="name" className={inputClassName} required />
                                        <InputError message={errors.name} className="text-xs" />
                                    </div>
                                    <div className="grid gap-2">
                                        <label htmlFor="new-slug" className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Slug (optional)
                                        </label>
                                        <input id="new-slug" name="slug" className={inputClassName} />
                                        <InputError message={errors.slug} className="text-xs" />
                                    </div>
                                </div>

                                <div className="grid gap-4 sm:grid-cols-[10rem_1fr] sm:items-center">
                                    <div className="grid gap-2">
                                        <label htmlFor="new-sort-order" className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                            Sort order
                                        </label>
                                        <input id="new-sort-order" type="number" name="sort_order" min={0} defaultValue={0} className={inputClassName} />
                                        <InputError message={errors.sort_order} className="text-xs" />
                                    </div>
                                    <label className="flex items-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong)">
                                        <input type="checkbox" name="is_active" value="1" defaultChecked />
                                        Active
                                    </label>
                                </div>

                                <button
                                    type="submit"
                                    className="inline-flex items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-on-strong) transition hover:bg-(--welcome-strong-hover) disabled:cursor-not-allowed disabled:opacity-70"
                                    disabled={processing}
                                >
                                    {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                    Create color
                                </button>
                            </>
                        )}
                    </Form>
                </div>

                <div className="grid gap-4">
                    {colors.map((color) => (
                        <div key={color.id} className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                    Used by {color.products_count} product{color.products_count === 1 ? '' : 's'}
                                </p>
                                <span className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs uppercase tracking-[0.2em] text-(--welcome-muted-text)">
                                    {color.is_active ? 'Active' : 'Archived'}
                                </span>
                            </div>

                            <Form {...productColorUpdate.form(color.id)} className="grid gap-4" disableWhileProcessing>
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-3 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <label htmlFor={`name-${color.id}`} className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    Name
                                                </label>
                                                <input id={`name-${color.id}`} name="name" defaultValue={color.name} className={inputClassName} required />
                                                <InputError message={errors.name} className="text-xs" />
                                            </div>
                                            <div className="grid gap-2">
                                                <label htmlFor={`slug-${color.id}`} className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    Slug
                                                </label>
                                                <input id={`slug-${color.id}`} name="slug" defaultValue={color.slug} className={inputClassName} />
                                                <InputError message={errors.slug} className="text-xs" />
                                            </div>
                                        </div>

                                        <div className="grid gap-4 sm:grid-cols-[10rem_1fr] sm:items-center">
                                            <div className="grid gap-2">
                                                <label htmlFor={`sort-${color.id}`} className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    Sort order
                                                </label>
                                                <input id={`sort-${color.id}`} type="number" name="sort_order" min={0} defaultValue={color.sort_order} className={inputClassName} />
                                                <InputError message={errors.sort_order} className="text-xs" />
                                            </div>
                                            <label className="flex items-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong)">
                                                <input type="hidden" name="is_active" value="0" />
                                                <input type="checkbox" name="is_active" value="1" defaultChecked={color.is_active} />
                                                Active
                                            </label>
                                        </div>

                                        <button
                                            type="submit"
                                            className="inline-flex items-center justify-center gap-2 rounded-full border border-(--welcome-muted-text) px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                            disabled={processing}
                                        >
                                            {processing && <Spinner />}
                                            Save color
                                        </button>
                                    </>
                                )}
                            </Form>

                            <Form {...productColorDestroy.form(color.id)} className="mt-3" disableWhileProcessing>
                                {({ processing }) => (
                                    <button
                                        type="submit"
                                        className="inline-flex items-center justify-center gap-2 rounded-full border border-red-300 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-red-700 transition hover:bg-red-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-70"
                                        disabled={processing}
                                    >
                                        {processing && <Spinner />}
                                        Delete color
                                    </button>
                                )}
                            </Form>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
