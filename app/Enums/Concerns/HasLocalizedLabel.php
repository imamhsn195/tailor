<?php

namespace App\Enums\Concerns;

trait HasLocalizedLabel
{
    /**
     * Get localized label for enum case
     *
     * @param string|null $locale
     * @return string
     */
    public function label(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $enumName = $this->getEnumName();
        $key = "enums.{$enumName}.{$this->value}";
        
        $translation = __($key, [], $locale);
        
        // If translation doesn't exist, return formatted value
        if ($translation === $key) {
            return ucwords(str_replace('_', ' ', $this->value));
        }
        
        return $translation;
    }

    /**
     * Get enum class name in snake_case
     *
     * @return string
     */
    protected function getEnumName(): string
    {
        $className = class_basename(static::class);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }
}

