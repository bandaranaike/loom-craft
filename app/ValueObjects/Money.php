<?php

namespace App\ValueObjects;

final readonly class Money
{
    public function __construct(public string $amount) {}

    public static function fromString(string $amount): self
    {
        return new self(self::normalize($amount));
    }

    public function addPercentage(string $rate): self
    {
        $base = (float) $this->amount;
        $percentage = (float) $rate;
        $result = $base + ($base * ($percentage / 100));

        return new self(self::normalize((string) $result));
    }

    private static function normalize(string $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
