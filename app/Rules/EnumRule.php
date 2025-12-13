<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use ValueError;

class EnumRule implements Rule
{
    /**
     * @param class-string $enumClass
     * @param string $attributeName
     */
    public function __construct(
        protected string $enumClass,
        protected string $attributeName = 'Value'
    ) {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if ($value === null) {
            return false;
        }

        try {
            $this->enumClass::from($value);
            return true;
        } catch (ValueError $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        $enumName = class_basename($this->enumClass);
        return "The {$this->attributeName} must be a valid {$enumName} value.";
    }
}

