import { Form, Head, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/app-layout';
import {
    destroy as carrierDestroy,
    index as carriersIndex,
    store as carrierStore,
    update as carrierUpdate,
} from '@/routes/admin/shipping-carriers';
import {
    destroy as serviceDestroy,
    store as serviceStore,
    update as serviceUpdate,
} from '@/routes/admin/shipping-carriers/services';
import type { BreadcrumbItem } from '@/types';

type ServiceItem = {
    id: number;
    name: string;
    code: string | null;
    is_active: boolean;
    sort_order: number;
};

type CarrierItem = {
    id: number;
    name: string;
    code: string | null;
    is_active: boolean;
    sort_order: number;
    shipments_count: number;
    services: ServiceItem[];
};

type Props = {
    carriers: CarrierItem[];
    status?: string;
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Shipping Carriers',
        href: carriersIndex().url,
    },
];

const inputClassName =
    'w-full rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong) placeholder:text-(--welcome-muted-70) shadow-[0_8px_20px_-16px_var(--welcome-shadow)] focus:border-(--welcome-strong) focus:outline-none focus:ring-2 focus:ring-(--welcome-strong-20)';

export default function ShippingCarriersIndex() {
    const { carriers, status } = usePage<Props>().props;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Shipping Carriers" />

            <div className="flex h-full min-w-0 flex-1 flex-col gap-6 overflow-x-hidden rounded-[24px] bg-(--welcome-on-strong) p-5 text-(--welcome-strong)">
                {status && (
                    <div className="rounded-[24px] border border-(--welcome-accent-40) bg-(--welcome-surface-3) px-6 py-4 text-sm text-(--welcome-muted-text)">
                        {status}
                    </div>
                )}

                <div className="rounded-[28px] border border-(--welcome-border) bg-(--welcome-surface-1) p-7 shadow-[0_20px_50px_-36px_var(--welcome-shadow-strong)]">
                    <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Courier Directory</p>
                    <h2 className="mt-2 font-['Playfair_Display',serif] text-3xl text-(--welcome-strong)">Manage shipping carriers</h2>
                    <p className="mt-2 text-sm text-(--welcome-body-text)">Maintain carrier and service-level dropdowns for shipment tracking.</p>
                </div>

                <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6">
                    <h3 className="font-['Playfair_Display',serif] text-2xl text-(--welcome-strong)">Create carrier</h3>
                    <Form {...carrierStore.form()} className="mt-4 grid gap-4" disableWhileProcessing>
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <label htmlFor="new-carrier-name" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Name
                                        </label>
                                        <input id="new-carrier-name" name="name" className={inputClassName} required />
                                        <InputError message={errors.name} className="text-xs" />
                                    </div>
                                    <div className="grid gap-2">
                                        <label htmlFor="new-carrier-code" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Code
                                        </label>
                                        <input id="new-carrier-code" name="code" className={inputClassName} />
                                        <InputError message={errors.code} className="text-xs" />
                                    </div>
                                </div>
                                <div className="grid gap-4 sm:grid-cols-[10rem_1fr] sm:items-center">
                                    <div className="grid gap-2">
                                        <label htmlFor="new-carrier-sort" className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                            Sort order
                                        </label>
                                        <input id="new-carrier-sort" type="number" name="sort_order" min={0} defaultValue={0} className={inputClassName} />
                                        <InputError message={errors.sort_order} className="text-xs" />
                                    </div>
                                    <label className="flex items-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong)">
                                        <input type="checkbox" name="is_active" value="1" defaultChecked />
                                        Active
                                    </label>
                                </div>
                                <button type="submit" className="inline-flex items-center justify-center gap-2 rounded-full bg-(--welcome-strong) px-5 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-on-strong) uppercase transition hover:bg-(--welcome-strong-hover) disabled:opacity-70" disabled={processing}>
                                    {processing && <Spinner className="text-(--welcome-on-strong)" />}
                                    Create carrier
                                </button>
                            </>
                        )}
                    </Form>
                </div>

                <div className="grid gap-4">
                    {carriers.map((carrier) => (
                        <div key={carrier.id} className="rounded-[24px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-5">
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                    Used by {carrier.shipments_count} shipment{carrier.shipments_count === 1 ? '' : 's'}
                                </p>
                                <span className="rounded-full border border-(--welcome-border) bg-(--welcome-surface-1) px-3 py-1 text-xs tracking-[0.2em] text-(--welcome-muted-text) uppercase">
                                    {carrier.is_active ? 'Active' : 'Archived'}
                                </span>
                            </div>

                            <Form {...carrierUpdate.form(carrier.id)} className="grid gap-4" disableWhileProcessing>
                                {({ processing, errors }) => (
                                    <>
                                        <div className="grid gap-3 sm:grid-cols-2">
                                            <div className="grid gap-2">
                                                <label htmlFor={`carrier-name-${carrier.id}`} className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Name
                                                </label>
                                                <input id={`carrier-name-${carrier.id}`} name="name" defaultValue={carrier.name} className={inputClassName} required />
                                                <InputError message={errors.name} className="text-xs" />
                                            </div>
                                            <div className="grid gap-2">
                                                <label htmlFor={`carrier-code-${carrier.id}`} className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Code
                                                </label>
                                                <input id={`carrier-code-${carrier.id}`} name="code" defaultValue={carrier.code ?? ''} className={inputClassName} />
                                                <InputError message={errors.code} className="text-xs" />
                                            </div>
                                        </div>
                                        <div className="grid gap-4 sm:grid-cols-[10rem_1fr] sm:items-center">
                                            <div className="grid gap-2">
                                                <label htmlFor={`carrier-sort-${carrier.id}`} className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">
                                                    Sort order
                                                </label>
                                                <input id={`carrier-sort-${carrier.id}`} type="number" name="sort_order" min={0} defaultValue={carrier.sort_order} className={inputClassName} />
                                                <InputError message={errors.sort_order} className="text-xs" />
                                            </div>
                                            <label className="flex items-center gap-2 rounded-full border border-(--welcome-border) bg-(--welcome-surface-2) px-4 py-2 text-sm text-(--welcome-strong)">
                                                <input type="hidden" name="is_active" value="0" />
                                                <input type="checkbox" name="is_active" value="1" defaultChecked={carrier.is_active} />
                                                Active
                                            </label>
                                        </div>
                                        <button type="submit" className="inline-flex items-center justify-center gap-2 rounded-full border border-(--welcome-muted-text) px-5 py-2 text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase transition hover:bg-(--welcome-muted-text) hover:text-(--welcome-on-strong) disabled:opacity-70" disabled={processing}>
                                            {processing && <Spinner />}
                                            Save carrier
                                        </button>
                                    </>
                                )}
                            </Form>

                            <div className="mt-5 rounded-[20px] border border-(--welcome-border) bg-(--welcome-surface-1) p-4">
                                <p className="text-xs tracking-[0.3em] text-(--welcome-muted-text) uppercase">Services</p>
                                <Form {...serviceStore.form(carrier.id)} className="mt-3 grid gap-3 md:grid-cols-[1fr_10rem_8rem_auto] md:items-end" disableWhileProcessing>
                                    {({ processing, errors }) => (
                                        <>
                                            <div className="grid gap-2">
                                                <label className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Name</label>
                                                <input name="name" className={inputClassName} required />
                                                <InputError message={errors.name} className="text-xs" />
                                            </div>
                                            <div className="grid gap-2">
                                                <label className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Code</label>
                                                <input name="code" className={inputClassName} />
                                                <InputError message={errors.code} className="text-xs" />
                                            </div>
                                            <div className="grid gap-2">
                                                <label className="text-xs font-semibold tracking-[0.3em] text-(--welcome-muted-text) uppercase">Sort</label>
                                                <input type="number" name="sort_order" min={0} defaultValue={0} className={inputClassName} />
                                            </div>
                                            <button type="submit" className="rounded-full bg-(--welcome-strong) px-4 py-2 text-xs font-semibold tracking-[0.2em] text-(--welcome-on-strong) uppercase disabled:opacity-70" disabled={processing}>
                                                Add
                                            </button>
                                        </>
                                    )}
                                </Form>

                                <div className="mt-4 grid gap-3">
                                    {carrier.services.map((service) => (
                                        <Form key={service.id} {...serviceUpdate.form({ shippingCarrier: carrier.id, shippingService: service.id })} className="grid gap-3 rounded-[16px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-3 md:grid-cols-[1fr_10rem_8rem_8rem_auto] md:items-center" disableWhileProcessing>
                                            {({ processing, errors }) => (
                                                <>
                                                    <input name="name" defaultValue={service.name} className={inputClassName} required />
                                                    <input name="code" defaultValue={service.code ?? ''} className={inputClassName} />
                                                    <input type="number" name="sort_order" min={0} defaultValue={service.sort_order} className={inputClassName} />
                                                    <label className="flex items-center gap-2 text-sm">
                                                        <input type="hidden" name="is_active" value="0" />
                                                        <input type="checkbox" name="is_active" value="1" defaultChecked={service.is_active} />
                                                        Active
                                                    </label>
                                                    <button type="submit" className="rounded-full border border-(--welcome-muted-text) px-4 py-2 text-xs font-semibold tracking-[0.2em] text-(--welcome-muted-text) uppercase disabled:opacity-70" disabled={processing}>
                                                        Save
                                                    </button>
                                                    <InputError message={errors.name ?? errors.code} className="text-xs md:col-span-5" />
                                                </>
                                            )}
                                        </Form>
                                    ))}
                                </div>
                            </div>

                            <div className="mt-3 flex flex-wrap gap-3">
                                <Form {...carrierDestroy.form(carrier.id)} disableWhileProcessing>
                                    {({ processing }) => (
                                        <button type="submit" className="rounded-full border border-red-300 px-5 py-2 text-xs font-semibold tracking-[0.3em] text-red-700 uppercase transition hover:bg-red-600 hover:text-white disabled:opacity-70" disabled={processing}>
                                            Delete carrier
                                        </button>
                                    )}
                                </Form>
                                {carrier.services.map((service) => (
                                    <Form key={service.id} {...serviceDestroy.form({ shippingCarrier: carrier.id, shippingService: service.id })} disableWhileProcessing>
                                        {({ processing }) => (
                                            <button type="submit" className="rounded-full border border-red-200 px-4 py-2 text-xs font-semibold tracking-[0.2em] text-red-700 uppercase transition hover:bg-red-600 hover:text-white disabled:opacity-70" disabled={processing}>
                                                Delete {service.name}
                                            </button>
                                        )}
                                    </Form>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
