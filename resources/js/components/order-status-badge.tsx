import { AlertTriangle, Banknote, CheckCircle2, CircleDashed, ClipboardCheck, CreditCard, Package, PackageCheck, ShieldCheck, Truck, XCircle, type LucideIcon } from 'lucide-react';
import { cn } from '@/lib/utils';

export type StatusDomain = 'order' | 'payment' | 'shipment' | 'generic';

type StatusTone = 'danger' | 'info' | 'neutral' | 'success' | 'warning';

type StatusConfig = {
    label: string;
    tone: StatusTone;
    icon: LucideIcon;
};

type OrderStatusBadgeProps = {
    status: string;
    domain?: StatusDomain;
    className?: string;
};

const orderStatuses: Record<string, StatusConfig> = {
    cancelled: { label: 'Cancelled', tone: 'danger', icon: XCircle },
    closed: { label: 'Closed', tone: 'neutral', icon: PackageCheck },
    confirmed: { label: 'Confirmed', tone: 'info', icon: ClipboardCheck },
    fulfilled: { label: 'Delivered', tone: 'success', icon: PackageCheck },
    paid: { label: 'Paid', tone: 'success', icon: CheckCircle2 },
    pending: { label: 'Pending', tone: 'warning', icon: CircleDashed },
};

const paymentStatuses: Record<string, StatusConfig> = {
    collection_pending: { label: 'Cash pending', tone: 'warning', icon: Banknote },
    failed: { label: 'Failed', tone: 'danger', icon: XCircle },
    paid: { label: 'Paid', tone: 'success', icon: CreditCard },
    pending: { label: 'Pending', tone: 'warning', icon: CircleDashed },
};

const shipmentStatuses: Record<string, StatusConfig> = {
    admin_received: { label: 'Admin received', tone: 'info', icon: Package },
    delivered: { label: 'Delivered', tone: 'success', icon: PackageCheck },
    delivery_failed: { label: 'Delivery failed', tone: 'danger', icon: AlertTriangle },
    dispatched: { label: 'Dispatched', tone: 'info', icon: Truck },
    in_transit: { label: 'In transit', tone: 'info', icon: Truck },
    packed: { label: 'Packed', tone: 'info', icon: Package },
    pending: { label: 'Pending', tone: 'warning', icon: CircleDashed },
    quality_checked: { label: 'Quality checked', tone: 'success', icon: ShieldCheck },
    ready_for_dispatch: { label: 'Ready to dispatch', tone: 'info', icon: Truck },
    ready_for_packing: { label: 'Ready to pack', tone: 'info', icon: Package },
    return_to_sender: { label: 'Returning', tone: 'danger', icon: AlertTriangle },
    returned: { label: 'Returned', tone: 'neutral', icon: Package },
    vendor_handed_to_admin: { label: 'Sent to admin', tone: 'info', icon: Truck },
    vendor_preparing: { label: 'Preparing', tone: 'warning', icon: Package },
};

const toneClasses: Record<StatusTone, string> = {
    danger: 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-200',
    info: 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-800/60 dark:bg-sky-950/40 dark:text-sky-200',
    neutral: 'border-stone-200 bg-stone-50 text-stone-700 dark:border-stone-700/70 dark:bg-stone-900/50 dark:text-stone-200',
    success: 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200',
    warning: 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-200',
};

const titleCaseStatus = (status: string): string =>
    status
        .split('_')
        .filter(Boolean)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join(' ');

const statusConfig = (status: string, domain: StatusDomain): StatusConfig => {
    if (domain === 'order' && orderStatuses[status]) {
        return orderStatuses[status];
    }

    if (domain === 'payment' && paymentStatuses[status]) {
        return paymentStatuses[status];
    }

    if (domain === 'shipment' && shipmentStatuses[status]) {
        return shipmentStatuses[status];
    }

    return {
        label: titleCaseStatus(status),
        tone: 'neutral',
        icon: CircleDashed,
    };
};

export const statusLabel = (status: string, domain: StatusDomain = 'generic'): string => statusConfig(status, domain).label;

export default function OrderStatusBadge({ status, domain = 'generic', className }: OrderStatusBadgeProps) {
    const config = statusConfig(status, domain);
    const Icon = config.icon;

    return (
        <span className={cn('inline-flex w-fit items-center gap-1.5 rounded-full border px-1.5 py-0.5 text-xs font-semibold', toneClasses[config.tone], className)}>
            <Icon className="size-3.5" aria-hidden="true" />
            {config.label}
        </span>
    );
}
