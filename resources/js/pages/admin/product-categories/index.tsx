import { Form, Head, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import {
    index as productCategoriesIndex,
    store as productCategoryStore,
    update as productCategoryUpdate,
} from '@/routes/admin/product-categories';
import type { BreadcrumbItem } from '@/types';

type CategoryItem = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    discount_percentage: string | null;
    is_active: boolean;
    sort_order: number;
    products_count: number;
};

type Props = {
    categories: CategoryItem[];
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Product Categories',
        href: productCategoriesIndex().url,
    },
];

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-16px_var(--welcome-shadow)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

const textAreaClassName =
    'w-full rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-16px_var(--welcome-shadow)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function ProductCategoriesIndex() {
    const { categories, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Product Categories" />

            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <div className="flex flex-col gap-2">
                        <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                            Catalog Taxonomy
                        </p>
                        <h2 className="font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">
                            Manage product categories
                        </h2>
                        <p className="text-sm text-(--welcome-body-text)">
                            Add, edit, reorder, and archive storefront categories.
                        </p>
                    </div>
                </div>

                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                    <h3 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">
                        Create category
                    </h3>
                    <Form {...productCategoryStore.form()} className="mt-4 grid gap-4" disableWhileProcessing>
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <label
                                            htmlFor="new-name"
                                            className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                        >
                                            Name
                                        </label>
                                        <input id="new-name" name="name" className={inputClassName} required />
                                        <InputError message={errors.name} className="text-xs" />
                                    </div>
                                    <div className="grid gap-2">
                                        <label
                                            htmlFor="new-slug"
                                            className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                        >
                                            Slug (optional)
                                        </label>
                                        <input id="new-slug" name="slug" className={inputClassName} />
                                        <InputError message={errors.slug} className="text-xs" />
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <label
                                        htmlFor="new-description"
                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                    >
                                        Description
                                    </label>
                                    <textarea
                                        id="new-description"
                                        name="description"
                                        rows={3}
                                        className={textAreaClassName}
                                    />
                                    <InputError message={errors.description} className="text-xs" />
                                </div>

                                <div className="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="new-sort-order"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Sort order
                                            </label>
                                            <input
                                                id="new-sort-order"
                                                type="number"
                                                name="sort_order"
                                                min={0}
                                                defaultValue={0}
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.sort_order} className="text-xs" />
                                        </div>
                                        <div className="grid gap-2">
                                            <label
                                                htmlFor="new-discount-percentage"
                                                className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                            >
                                                Discount %
                                            </label>
                                            <input
                                                id="new-discount-percentage"
                                                type="number"
                                                name="discount_percentage"
                                                min={0}
                                                max={100}
                                                step="0.01"
                                                className={inputClassName}
                                            />
                                            <InputError message={errors.discount_percentage} className="text-xs" />
                                        </div>
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
                                    Create category
                                </button>
                            </>
                        )}
                    </Form>
                </div>

                {categories.length === 0 ? (
                    <div className="rounded-[24px] border border-dashed border-(--welcome-border) bg-(--welcome-surface-3) p-10 text-center text-sm text-(--welcome-muted-text)">
                        No categories added yet.
                    </div>
                ) : (
                    <div className="grid gap-4">
                        {categories.map((category) => (
                            <div
                                key={category.id}
                                className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5"
                            >
                                <Form
                                    {...productCategoryUpdate.form(category.id)}
                                    className="grid gap-4"
                                    disableWhileProcessing
                                >
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="flex flex-wrap items-center justify-between gap-3">
                                                <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    Used by {category.products_count} product
                                                    {category.products_count === 1 ? '' : 's'}
                                                </p>
                                                <label className="inline-flex items-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-4 py-1.5 text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                                                    <input
                                                        type="hidden"
                                                        name="is_active"
                                                        value="0"
                                                    />
                                                    <input
                                                        type="checkbox"
                                                        name="is_active"
                                                        value="1"
                                                        defaultChecked={category.is_active}
                                                    />
                                                    {category.is_active ? 'Active' : 'Archived'}
                                                </label>
                                            </div>

                                            <div className="grid gap-3 sm:grid-cols-2">
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor={`name-${category.id}`}
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Name
                                                    </label>
                                                    <input
                                                        id={`name-${category.id}`}
                                                        name="name"
                                                        defaultValue={category.name}
                                                        className={inputClassName}
                                                        required
                                                    />
                                                    <InputError message={errors.name} className="text-xs" />
                                                </div>
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor={`slug-${category.id}`}
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Slug
                                                    </label>
                                                    <input
                                                        id={`slug-${category.id}`}
                                                        name="slug"
                                                        defaultValue={category.slug}
                                                        className={inputClassName}
                                                    />
                                                    <InputError message={errors.slug} className="text-xs" />
                                                </div>
                                            </div>

                                            <div className="grid gap-3 sm:grid-cols-[12rem_1fr]">
                                                <div className="grid gap-3">
                                                    <div className="grid gap-2">
                                                        <label
                                                            htmlFor={`sort-order-${category.id}`}
                                                            className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                        >
                                                            Sort order
                                                        </label>
                                                        <input
                                                            id={`sort-order-${category.id}`}
                                                            type="number"
                                                            name="sort_order"
                                                            min={0}
                                                            defaultValue={category.sort_order}
                                                            className={inputClassName}
                                                        />
                                                        <InputError message={errors.sort_order} className="text-xs" />
                                                    </div>
                                                    <div className="grid gap-2">
                                                        <label
                                                            htmlFor={`discount-percentage-${category.id}`}
                                                            className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                        >
                                                            Discount %
                                                        </label>
                                                        <input
                                                            id={`discount-percentage-${category.id}`}
                                                            type="number"
                                                            name="discount_percentage"
                                                            min={0}
                                                            max={100}
                                                            step="0.01"
                                                            defaultValue={category.discount_percentage ?? ''}
                                                            className={inputClassName}
                                                        />
                                                        <InputError message={errors.discount_percentage} className="text-xs" />
                                                    </div>
                                                </div>
                                                <div className="grid gap-2">
                                                    <label
                                                        htmlFor={`description-${category.id}`}
                                                        className="text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text)"
                                                    >
                                                        Description
                                                    </label>
                                                    <textarea
                                                        id={`description-${category.id}`}
                                                        name="description"
                                                        rows={3}
                                                        defaultValue={category.description ?? ''}
                                                        className={textAreaClassName}
                                                    />
                                                    <InputError message={errors.description} className="text-xs" />
                                                </div>
                                            </div>

                                            <button
                                                type="submit"
                                                className="inline-flex items-center justify-center gap-2 rounded-full border border-(--welcome-muted-text) px-5 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-muted-text) transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
                                                disabled={processing}
                                            >
                                                {processing && <Spinner />}
                                                Save category
                                            </button>
                                        </>
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
