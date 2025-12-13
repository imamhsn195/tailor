<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use ValueError;

class SafeEnumCast implements CastsAttributes
{
    /**
     * @param class-string $enumClass
     */
    public function __construct(
        protected string $enumClass
    ) {
    }

    /**
     * Transform the attribute from the underlying model values.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        try {
            return $this->enumClass::from($value);
        } catch (ValueError $e) {
            // Return null for invalid enum values instead of throwing exception
            return null;
        }
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof $this->enumClass) {
            return $value->value;
        }

        // Try to create enum from value
        try {
            return $this->enumClass::from($value)->value;
        } catch (ValueError $e) {
            return null;
        }
    }
}

