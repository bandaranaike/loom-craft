import type { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function truncatePublicId(
    value: string | null,
    fallback: string,
): string {
    if (value === null || value.length <= 20) {
        return value ?? fallback;
    }

    return `${value.slice(0, 12)}...${value.slice(-6)}`;
}
