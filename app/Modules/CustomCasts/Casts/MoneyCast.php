<?php

namespace App\Modules\CustomCasts\Casts;

use App\Modules\CustomCasts\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/** @implements CastsAttributes<Money, Money> */
final class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): Money
    {
        return new Money((int) $attributes['price_amount'], (string) $attributes['price_currency']);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (! $value instanceof Money) {
            throw new InvalidArgumentException('The price attribute must be set to a '.Money::class.' instance.');
        }

        return [
            'price_amount' => $value->amountInCents,
            'price_currency' => $value->currency,
        ];
    }
}
