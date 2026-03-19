// Credit: https://usehooks-ts.com/
import { useCallback, useEffect, useRef, useState } from 'react';

export type CopiedValue = string | null;
export type CopyFn = (text: string) => Promise<boolean>;
export type UseClipboardReturn = [CopiedValue, CopyFn];

type UseClipboardOptions = {
    resetAfterMs?: number;
};

export function useClipboard({
    resetAfterMs,
}: UseClipboardOptions = {}): UseClipboardReturn {
    const [copiedText, setCopiedText] = useState<CopiedValue>(null);
    const timeoutRef = useRef<number | null>(null);

    useEffect(() => {
        return () => {
            if (timeoutRef.current !== null) {
                window.clearTimeout(timeoutRef.current);
            }
        };
    }, []);

    const copy: CopyFn = useCallback(async (text) => {
        if (!navigator?.clipboard) {
            console.warn('Clipboard not supported');

            return false;
        }

        try {
            await navigator.clipboard.writeText(text);
            setCopiedText(text);

            if (timeoutRef.current !== null) {
                window.clearTimeout(timeoutRef.current);
            }

            if (resetAfterMs !== undefined) {
                timeoutRef.current = window.setTimeout(() => {
                    setCopiedText(null);
                    timeoutRef.current = null;
                }, resetAfterMs);
            }

            return true;
        } catch (error) {
            console.warn('Copy failed', error);
            setCopiedText(null);

            return false;
        }
    }, [resetAfterMs]);

    return [copiedText, copy];
}
