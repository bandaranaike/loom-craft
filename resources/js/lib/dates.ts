const dateTimeWithoutTimezonePattern = /^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?$/;

const timezoneSuffixPattern = /(?:Z|[+-]\d{2}:?\d{2})$/i;

const localDateTimeFormatter = new Intl.DateTimeFormat(undefined, {
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    month: 'short',
    timeZoneName: 'short',
    year: 'numeric',
});

export function parseUtcDateTime(value: string | null): Date | null {
    if (value === null || value.trim() === '') {
        return null;
    }

    const trimmedValue = value.trim();
    const normalizedValue = dateTimeWithoutTimezonePattern.test(trimmedValue) && !timezoneSuffixPattern.test(trimmedValue) ? `${trimmedValue.replace(' ', 'T')}Z` : trimmedValue;

    const date = new Date(normalizedValue);

    return Number.isNaN(date.getTime()) ? null : date;
}

export function formatLocalDateTime(value: string | null, fallback = 'Pending'): string {
    const date = parseUtcDateTime(value);

    return date === null ? fallback : localDateTimeFormatter.format(date);
}
