<?php

namespace App\Modules\SemanticSearch\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/** @implements CastsAttributes<array<int, float>, array<int, float>> */
final class VectorCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        return array_values(unpack('g*', $value));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value) || $value === []) {
            throw new InvalidArgumentException('The embedding attribute must be a non-empty array of floats.');
        }

        return pack('g*', ...array_map(floatval(...), $value));
    }
}
