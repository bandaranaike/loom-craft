export const csrfHeaders = (): Record<string, string> => {
    if (typeof document === 'undefined') {
        return {};
    }

    const xsrfCookie = document.cookie
        .split('; ')
        .find((cookie) => cookie.startsWith('XSRF-TOKEN='));

    if (xsrfCookie) {
        return {
            'X-XSRF-TOKEN': decodeURIComponent(
                xsrfCookie.slice('XSRF-TOKEN='.length),
            ),
        };
    }

    const metaToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

    return metaToken === '' ? {} : { 'X-CSRF-TOKEN': metaToken };
};
