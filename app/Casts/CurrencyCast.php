<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class CurrencyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return null;
        }
        return number_format($value, 0, '.', ',');
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }
        $clean = preg_replace('/\D/', '', $value);
        return $clean !== '' ? (int) $clean : null;
    }
}
