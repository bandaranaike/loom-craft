<?php

namespace App\ValueObjects;

use RuntimeException;

final readonly class Currency
{
    /**
     * @param  'USD'|'EUR'|'LKR'  $code
     */
    public function __construct(public string $code) {}

    public static function fromString(string $code): self
    {
        $normalized = strtoupper(trim($code));

        if (! in_array($normalized, self::supported(), true)) {
            throw new \InvalidArgumentException('Unsupported currency.');
        }

        return new self($normalized);
    }

    public static function default(): self
    {
        $configured = config('commerce.base_currency', 'LKR');

        if (! is_string($configured)) {
            throw new RuntimeException('Configured base currency is invalid.');
        }

        return self::fromString($configured);
    }

    /**
     * @return list<'LKR'>
     */
    public static function supported(): array
    {
        return ['LKR'];
    }
}
