<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalOrderService
{
    public function isConfigured(): bool
    {
        return $this->clientId() !== '' && $this->clientSecret() !== '';
    }

    public function sdkClientId(): string
    {
        return $this->clientId();
    }

    /**
     * @return array{order_id: string, approve_url: string}
     */
    public function createOrder(
        string $currencyCode,
        string $amount,
        string $returnUrl,
        string $cancelUrl,
    ): array {
        $response = $this->createOrderPayload(
            $currencyCode,
            $amount,
            $returnUrl,
            $cancelUrl,
        );

        $orderId = $response['id'] ?? null;
        $approveUrl = $this->extractLink($response, 'approve');

        if (! is_string($orderId) || $orderId === '' || $approveUrl === null) {
            throw new RuntimeException('PayPal create order response is invalid.');
        }

        return [
            'order_id' => $orderId,
            'approve_url' => $approveUrl,
        ];
    }

    public function createCardOrder(string $currencyCode, string $amount): string
    {
        $response = $this->createOrderPayload($currencyCode, $amount);
        $orderId = $response['id'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            throw new RuntimeException('PayPal create order response is invalid.');
        }

        return $orderId;
    }

    /**
     * @return array{capture_id: string, status: string}
     */
    public function captureOrder(string $orderId): array
    {
        $response = $this->api()
            ->post("/v2/checkout/orders/{$orderId}/capture")
            ->throw()
            ->json();

        $status = $response['status'] ?? null;
        $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

        if (! is_string($status) || ! is_string($captureId) || $captureId === '') {
            throw new RuntimeException('PayPal capture response is invalid.');
        }

        return [
            'capture_id' => $captureId,
            'status' => $status,
        ];
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->acceptJson()
            ->withToken($this->accessToken())
            ->timeout(15);
    }

    /**
     * @return array<string, mixed>
     */
    private function createOrderPayload(
        string $currencyCode,
        string $amount,
        ?string $returnUrl = null,
        ?string $cancelUrl = null,
    ): array {
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => strtoupper($currencyCode),
                        'value' => $amount,
                    ],
                ],
            ],
        ];

        if ($returnUrl !== null && $cancelUrl !== null) {
            $payload['application_context'] = [
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
                'user_action' => 'PAY_NOW',
            ];
        }

        return $this->api()
            ->post('/v2/checkout/orders', $payload)
            ->throw()
            ->json();
    }

    private function accessToken(): string
    {
        $response = Http::baseUrl($this->baseUrl())
            ->asForm()
            ->acceptJson()
            ->withBasicAuth($this->clientId(), $this->clientSecret())
            ->post('/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ])
            ->throw()
            ->json();

        $token = $response['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayPal access token response is invalid.');
        }

        return $token;
    }

    private function baseUrl(): string
    {
        $baseUrl = config('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');

        return is_string($baseUrl) ? rtrim($baseUrl, '/') : 'https://api-m.sandbox.paypal.com';
    }

    private function clientId(): string
    {
        $clientId = config('services.paypal.client_id');

        return is_string($clientId) ? trim($clientId) : '';
    }

    private function clientSecret(): string
    {
        $clientSecret = config('services.paypal.client_secret');

        return is_string($clientSecret) ? trim($clientSecret) : '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractLink(array $payload, string $rel): ?string
    {
        $links = $payload['links'] ?? null;

        if (! is_array($links)) {
            return null;
        }

        foreach ($links as $link) {
            if (! is_array($link)) {
                continue;
            }

            $linkRel = $link['rel'] ?? null;
            $href = $link['href'] ?? null;

            if ($linkRel === $rel && is_string($href) && $href !== '') {
                return $href;
            }
        }

        return null;
    }
}
