<?php

namespace App\Modules\CustomCasts\ValueObjects;

final readonly class Money
{
    public function __construct(
        public int $amountInCents,
        public string $currency = 'USD',
    ) {}

    public function format(): string
    {
        return sprintf('%s %s', $this->currency, number_format($this->amountInCents / 100, 2));
    }
}
