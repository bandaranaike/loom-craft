import { AlertCircleIcon, XIcon } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

type DismissibleStockDelayAlertProps = {
    pageKey: string;
    message: string | null;
    className?: string;
};

const storageKey = (pageKey: string): string => `loomcraft-stock-delay:${pageKey}`;

export default function DismissibleStockDelayAlert({
    pageKey,
    message,
    className,
}: DismissibleStockDelayAlertProps) {
    const messageKey = useMemo(
        () => (message ? `${storageKey(pageKey)}:${message}` : null),
        [message, pageKey],
    );
    const [dismissed, setDismissed] = useState(false);

    useEffect(() => {
        if (typeof window === 'undefined' || messageKey === null) {
            setDismissed(false);

            return;
        }

        setDismissed(window.sessionStorage.getItem(messageKey) === 'dismissed');
    }, [messageKey]);

    if (!message || dismissed) {
        return null;
    }

    return (
        <Alert className={className}>
            <AlertCircleIcon />
            <button
                type="button"
                onClick={() => {
                    if (messageKey !== null && typeof window !== 'undefined') {
                        window.sessionStorage.setItem(messageKey, 'dismissed');
                    }

                    setDismissed(true);
                }}
                className="absolute right-3 top-3 rounded-full p-1 text-(--welcome-muted-text) transition hover:bg-black/5 hover:text-(--welcome-strong)"
                aria-label="Dismiss stock delay warning"
            >
                <XIcon className="h-4 w-4" />
            </button>
            <AlertTitle>Production time notice</AlertTitle>
            <AlertDescription>{message}</AlertDescription>
        </Alert>
    );
}
