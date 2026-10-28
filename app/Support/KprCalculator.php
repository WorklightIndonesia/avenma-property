<?php

namespace App\Support;

class KprCalculator
{
    /**
     * Fixed-rate annuity: the monthly payment that clears principal plus
     * interest over the term. Zero interest splits the principal evenly.
     */
    public static function monthly(int $principal, float $annualRate, int $months): float
    {
        if ($principal <= 0 || $months <= 0) {
            return 0.0;
        }

        $rate = $annualRate / 1200;

        if ($rate <= 0) {
            return $principal / $months;
        }

        return $principal * $rate / (1 - (1 + $rate) ** -$months);
    }
}
