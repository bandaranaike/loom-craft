import type { FormEvent } from 'react';
import InputError from '@/components/input-error';

type PaymentProof = {
    url: string;
    original_name: string;
    mime_type: string;
    uploaded_at: string | null;
};

type OrderBankTransferSlipPanelProps = {
    canUploadPaymentProof: boolean;
    orderId: number;
    orderPublicId: string | null;
    paymentProof: PaymentProof | null;
    proofIsImage: boolean;
    processing: boolean;
    slipError?: string;
    onFileChange: (file: File | null) => void;
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    className?: string;
    buttonClassName?: string;
    description?: string;
};

export default function OrderBankTransferSlipPanel({
    canUploadPaymentProof,
    orderId,
    orderPublicId,
    paymentProof,
    proofIsImage,
    processing,
    slipError,
    onFileChange,
    onSubmit,
    className = 'rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-3) p-6 text-sm',
    buttonClassName = 'inline-flex items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-2 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:opacity-70',
    description,
}: OrderBankTransferSlipPanelProps) {
    const panelDescription =
        description ??
        `Upload the final transfer slip for ${orderPublicId ?? `order #${orderId}`}. Keep the amount and reference visible in the document if possible.`;

    return (
        <div className={className}>
            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                Final bank transfer slip
            </p>
            <p className="mt-3 text-sm text-(--welcome-body-text)">
                {panelDescription}
            </p>
            {paymentProof && (
                <div className="mt-4 space-y-3">
                    <a
                        href={paymentProof.url}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex text-sm text-(--welcome-strong) underline"
                    >
                        {paymentProof.original_name}
                    </a>
                    <p className="text-xs text-(--welcome-body-text)">
                        Uploaded {paymentProof.uploaded_at ?? 'recently'}
                    </p>
                    {proofIsImage && (
                        <img
                            src={paymentProof.url}
                            alt={paymentProof.original_name}
                            className="rounded-[20px] border border-(--welcome-border) object-cover"
                        />
                    )}
                </div>
            )}
            {canUploadPaymentProof ? (
                <form onSubmit={onSubmit} className="mt-4 space-y-3">
                    <input
                        type="file"
                        accept=".pdf,image/*"
                        onChange={(event) =>
                            onFileChange(event.target.files?.[0] ?? null)
                        }
                        className="block w-full text-sm text-(--welcome-body-text)"
                    />
                    <InputError message={slipError} />
                    <button
                        type="submit"
                        disabled={processing}
                        className={buttonClassName}
                    >
                        {processing
                            ? 'Uploading...'
                            : paymentProof
                              ? 'Replace slip'
                              : 'Upload slip'}
                    </button>
                </form>
            ) : (
                <p className="mt-4 text-sm text-(--welcome-body-text)">
                    Proof upload is available only to the order owner or the
                    original guest checkout session.
                </p>
            )}
        </div>
    );
}
