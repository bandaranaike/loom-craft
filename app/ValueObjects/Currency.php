<?php

namespace App\ValueObjects;

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

    /**
     * @return list<'USD'|'EUR'|'LKR'>
     */
    public static function supported(): array
    {
        return ['USD', 'EUR', 'LKR'];
    }
}
