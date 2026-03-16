import { useEffect, useRef, useState } from 'react';

declare global {
    interface Window {
        paypal?: {
            CardFields?: (config: {
                createOrder: () => Promise<string>;
                onApprove: (data: { orderID: string }) => Promise<void>;
                onError: (error: unknown) => void;
            }) => {
                isEligible: () => boolean;
                NumberField: () => { render: (selector: string) => Promise<void> };
                CVVField: () => { render: (selector: string) => Promise<void> };
                ExpiryField: () => { render: (selector: string) => Promise<void> };
                submit: () => Promise<void>;
            };
        };
    }
}

type ValidationErrors = Record<string, string | string[]>;

type PayPalCardFieldsProps = {
    enabled: boolean;
    clientId: string;
    createOrderUrl: string;
    captureOrderUrl: string;
    csrfToken: string;
    payload: Record<string, unknown>;
    onValidationErrors: (errors: ValidationErrors) => void;
    onSuccess: (redirectUrl: string) => void;
};

export default function PayPalCardFields({
    enabled,
    clientId,
    createOrderUrl,
    captureOrderUrl,
    csrfToken,
    payload,
    onValidationErrors,
    onSuccess,
}: PayPalCardFieldsProps) {
    const [isReady, setIsReady] = useState(false);
    const [isEligible, setIsEligible] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);
    const cardFieldsRef = useRef<ReturnType<NonNullable<typeof window.paypal>['CardFields']> | null>(null);
    const payloadRef = useRef(payload);

    payloadRef.current = payload;

    useEffect(() => {
        if (!enabled || clientId === '') {
            setIsReady(false);
            setErrorMessage(null);

            return;
        }

        let cancelled = false;

        const loadSdk = async (): Promise<void> => {
            const existingScript = document.getElementById('paypal-card-sdk');

            if (existingScript instanceof HTMLScriptElement) {
                if (existingScript.dataset.clientId === clientId && window.paypal?.CardFields) {
                    return;
                }

                existingScript.remove();
            }

            await new Promise<void>((resolve, reject) => {
                const script = document.createElement('script');
                script.id = 'paypal-card-sdk';
                script.dataset.clientId = clientId;
                script.src = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(
                    clientId,
                )}&components=card-fields&currency=USD&intent=capture`;
                script.async = true;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('PayPal card SDK failed to load.'));
                document.head.appendChild(script);
            });
        };

        const initialize = async (): Promise<void> => {
            try {
                await loadSdk();

                if (cancelled || !window.paypal?.CardFields) {
                    return;
                }

                const cardFields = window.paypal.CardFields({
                    createOrder: async () => {
                        setErrorMessage(null);

                        const response = await fetch(createOrderUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify(payloadRef.current),
                        });

                        if (response.status === 422) {
                            const validationPayload = (await response.json()) as {
                                errors?: ValidationErrors;
                            };

                            onValidationErrors(validationPayload.errors ?? {});
                            throw new Error('Validation failed.');
                        }

                        if (!response.ok) {
                            throw new Error('PayPal card order creation failed.');
                        }

                        const createPayload = (await response.json()) as {
                            order_id?: string;
                        };

                        if (!createPayload.order_id) {
                            throw new Error('PayPal card order ID is missing.');
                        }

                        return createPayload.order_id;
                    },
                    onApprove: async ({ orderID }) => {
                        const response = await fetch(captureOrderUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ order_id: orderID }),
                        });

                        if (response.status === 422) {
                            const validationPayload = (await response.json()) as {
                                errors?: ValidationErrors;
                            };

                            onValidationErrors(validationPayload.errors ?? {});
                            throw new Error('PayPal card capture failed.');
                        }

                        if (!response.ok) {
                            throw new Error('PayPal card capture failed.');
                        }

                        const capturePayload = (await response.json()) as {
                            redirect_url?: string;
                        };

                        if (!capturePayload.redirect_url) {
                            throw new Error('Confirmation URL is missing.');
                        }

                        onSuccess(capturePayload.redirect_url);
                    },
                    onError: () => {
                        setIsSubmitting(false);
                        setErrorMessage(
                            'PayPal card payment could not be completed. Please check the card details and try again.',
                        );
                    },
                });

                if (!cardFields.isEligible()) {
                    setIsEligible(false);
                    setIsReady(false);

                    return;
                }

                cardFieldsRef.current = cardFields;

                await Promise.all([
                    cardFields.NumberField().render('#paypal-card-number'),
                    cardFields.CVVField().render('#paypal-card-cvv'),
                    cardFields.ExpiryField().render('#paypal-card-expiry'),
                ]);

                if (!cancelled) {
                    setIsEligible(true);
                    setIsReady(true);
                }
            } catch (error) {
                if (!cancelled) {
                    setErrorMessage(
                        error instanceof Error
                            ? error.message
                            : 'PayPal card payment is unavailable right now.',
                    );
                }
            }
        };

        void initialize();

        return () => {
            cancelled = true;
        };
    }, [enabled, clientId, createOrderUrl, captureOrderUrl, csrfToken, onSuccess, onValidationErrors]);

    if (!enabled) {
        return null;
    }

    return (
        <div className="rounded-[28px] border border-(--welcome-border-soft) bg-(--welcome-surface-1) p-5">
            <p className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                Card payment
            </p>
            <div className="mt-4 grid gap-4 md:grid-cols-3">
                <div className="space-y-2 md:col-span-2">
                    <label className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                        Card number
                    </label>
                    <div
                        id="paypal-card-number"
                        className="min-h-11 rounded-full border border-(--welcome-border) bg-white px-4 py-3"
                    />
                </div>
                <div className="space-y-2">
                    <label className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                        Expiry
                    </label>
                    <div
                        id="paypal-card-expiry"
                        className="min-h-11 rounded-full border border-(--welcome-border) bg-white px-4 py-3"
                    />
                </div>
            </div>
            <div className="mt-4 max-w-48 space-y-2">
                <label className="text-xs uppercase tracking-[0.3em] text-(--welcome-muted-text)">
                    Security code
                </label>
                <div
                    id="paypal-card-cvv"
                    className="min-h-11 rounded-full border border-(--welcome-border) bg-white px-4 py-3"
                />
            </div>
            {!isEligible ? (
                <p className="mt-4 text-xs text-(--welcome-danger)">
                    PayPal card payments are not available for this merchant account right now.
                </p>
            ) : null}
            {errorMessage ? (
                <p className="mt-4 text-xs text-(--welcome-danger)">
                    {errorMessage}
                </p>
            ) : null}
            <button
                type="button"
                disabled={!isReady || isSubmitting || !isEligible}
                onClick={async () => {
                    if (cardFieldsRef.current === null) {
                        return;
                    }

                    setIsSubmitting(true);
                    setErrorMessage(null);

                    try {
                        await cardFieldsRef.current.submit();
                    } catch (error) {
                        if (error instanceof Error && error.message !== 'Validation failed.') {
                            setErrorMessage(error.message);
                        }
                    } finally {
                        setIsSubmitting(false);
                    }
                }}
                className="mt-6 inline-flex w-full items-center justify-center rounded-full border border-(--welcome-strong) px-4 py-3 text-xs font-semibold uppercase tracking-[0.3em] text-(--welcome-strong) transition hover:bg-(--welcome-strong) hover:text-(--welcome-on-strong) disabled:cursor-not-allowed disabled:opacity-70"
            >
                {isSubmitting ? 'Processing card...' : 'Pay with card'}
            </button>
        </div>
    );
}
