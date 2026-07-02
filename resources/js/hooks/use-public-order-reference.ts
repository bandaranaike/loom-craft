import { useClipboard } from '@/hooks/use-clipboard';
import { truncatePublicId } from '@/lib/utils';

type UsePublicOrderReferenceOptions = {
    id: number;
    publicId: string | null;
    orderNumber: string | null;
};

type UsePublicOrderReferenceReturn = {
    copied: boolean;
    copyPublicOrderReference: () => Promise<boolean>;
    publicOrderReference: string;
    truncatedPublicOrderReference: string;
};

export function usePublicOrderReference({
    id,
    orderNumber,
}: UsePublicOrderReferenceOptions): UsePublicOrderReferenceReturn {
    const [copiedText, copy] = useClipboard({ resetAfterMs: 2000 });
    const publicOrderReference = orderNumber ?? `Order #${id}`;
    const truncatedPublicOrderReference = truncatePublicId(
        orderNumber,
        publicOrderReference,
    );

    return {
        copied: copiedText === publicOrderReference,
        copyPublicOrderReference: () => copy(publicOrderReference),
        publicOrderReference,
        truncatedPublicOrderReference,
    };
}
