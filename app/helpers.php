<?php

if (!function_exists('trans_common')) {
    /**
     * Translate common UI strings
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    function trans_common(string $key, array $replace = [], ?string $locale = null): string
    {
        return __('common.' . $key, $replace, $locale);
    }
}

if (!function_exists('currency_format')) {
    /**
     * Format currency amount
     *
     * @param float|int $amount
     * @param string $currency
     * @param int $decimals
     * @return string
     */
    function currency_format(float|int $amount, string $currency = 'BDT', int $decimals = 2): string
    {
        $symbols = [
            'BDT' => '৳',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        
        return $symbol . number_format($amount, $decimals);
    }
}

if (!function_exists('format_address')) {
    /**
     * Format address from components
     *
     * @param string|null $street
     * @param string|null $city
     * @param string|null $state
     * @param string|null $postalCode
     * @param string|null $country
     * @return string
     */
    function format_address(
        ?string $street = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postalCode = null,
        ?string $country = null
    ): string {
        $parts = array_filter([$street, $city, $state, $postalCode, $country]);
        return implode(', ', $parts);
    }
}

