import type { ReactNode } from 'react';
import { formatMoney } from '@/lib/currency';

type OrderSummaryCardProps = {
    copiedReference: string;
    onCopyReference: () => void;
    orderCurrency: string;
    orderStatus: string;
    orderSubtotal: string;
    orderTotal: string;
    paymentAmount: string | null;
    paymentCurrency: string | null;
    paymentMethod: string;
    paymentOriginalAmount: string | null;
    paymentOriginalCurrency: string | null;
    paymentRecordedInDifferentCurrency: boolean;
    paymentStatus: string;
    shippingResponsibility: string;
    truncatedReference: string;
    className?: string;
    titleClassName?: string;
    bodyClassName?: string;
    labelClassName?: string;
    valueClassName?: string;
    referenceButtonClassName?: string;
    showOrderStatus?: boolean;
    footer?: ReactNode;
};

export default function OrderSummaryCard({
    copiedReference,
    onCopyReference,
    orderCurrency,
    orderStatus,
    orderSubtotal,
    orderTotal,
    paymentAmount,
    paymentCurrency,
    paymentMethod,
    paymentOriginalAmount,
    paymentOriginalCurrency,
    paymentRecordedInDifferentCurrency,
    paymentStatus,
    shippingResponsibility,
    truncatedReference,
    className = 'rounded-4xl border border-(--welcome-border) bg-(--welcome-surface-1) p-6 shadow-[0_30px_80px_-45px_var(--welcome-shadow)]',
    titleClassName = 'text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)',
    bodyClassName = 'mt-4 space-y-3 text-sm',
    labelClassName = 'text-(--welcome-body-text)',
    valueClassName = 'text-(--welcome-strong)',
    referenceButtonClassName = 'max-w-48 cursor-pointer truncate text-right underline decoration-(--welcome-border) underline-offset-4',
    showOrderStatus = false,
    footer,
}: OrderSummaryCardProps) {
    return (
        <aside className={className}>
            <p className={titleClassName}>Summary</p>
            <div className={bodyClassName}>
                <div className="flex items-center justify-between">
                    <span className={labelClassName}>Reference</span>
                    <button
                        type="button"
                        onClick={onCopyReference}
                        className={referenceButtonClassName}
                        title={`Copy ${copiedReference}`}
                    >
                        {truncatedReference}
                    </button>
                </div>
                <div className="flex items-center justify-between">
                    <span className={labelClassName}>Subtotal</span>
                    <span className={valueClassName}>
                        {formatMoney(orderSubtotal, orderCurrency)}
                    </span>
                </div>
                <div className="flex items-center justify-between">
                    <span className={labelClassName}>Shipping</span>
                    <span className={valueClassName}>{shippingResponsibility}</span>
                </div>
                {showOrderStatus && (
                    <div className="flex items-center justify-between">
                        <span className={labelClassName}>Order status</span>
                        <span className={valueClassName}>{orderStatus}</span>
                    </div>
                )}
                <div className="flex items-center justify-between">
                    <span className={labelClassName}>Total</span>
                    <span className={valueClassName}>
                        {formatMoney(orderTotal, orderCurrency)}
                    </span>
                </div>
                <div className="flex items-center justify-between">
                    <span className={labelClassName}>Payment</span>
                    <span className={valueClassName}>
                        {paymentMethod} ({paymentStatus})
                    </span>
                </div>
                {paymentAmount && paymentCurrency && (
                    <div className="flex items-center justify-between">
                        <span className={labelClassName}>Recorded</span>
                        <span className={valueClassName}>
                            {formatMoney(paymentAmount, paymentCurrency)}
                        </span>
                    </div>
                )}
                {paymentRecordedInDifferentCurrency &&
                    paymentOriginalAmount &&
                    paymentOriginalCurrency && (
                        <p className="rounded-[20px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) px-4 py-3 text-xs text-(--welcome-body-text)">
                            This payment was processed in {paymentCurrency}. The
                            original order total remains{' '}
                            {formatMoney(
                                paymentOriginalAmount,
                                paymentOriginalCurrency,
                            )}
                            .
                        </p>
                    )}
            </div>
            {footer && <div className="mt-6">{footer}</div>}
        </aside>
    );
}
