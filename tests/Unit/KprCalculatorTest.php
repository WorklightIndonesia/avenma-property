<?php

namespace Tests\Unit;

use App\Support\KprCalculator;
use PHPUnit\Framework\TestCase;

class KprCalculatorTest extends TestCase
{
    public function test_zero_interest_splits_the_principal_evenly(): void
    {
        $this->assertEqualsWithDelta(1_000_000, KprCalculator::monthly(120_000_000, 0, 120), 0.01);
    }

    public function test_monthly_payment_follows_the_annuity_formula(): void
    {
        $this->assertEqualsWithDelta(7_173_547.42, KprCalculator::monthly(500_000_000, 12, 120), 0.01);
        $this->assertEqualsWithDelta(9_496_141.53, KprCalculator::monthly(800_000_000, 7.5, 120), 0.01);
    }

    public function test_shorter_terms_cost_more_per_month(): void
    {
        $this->assertGreaterThan(
            KprCalculator::monthly(500_000_000, 7.5, 240),
            KprCalculator::monthly(500_000_000, 7.5, 120)
        );
    }

    public function test_empty_principal_or_term_is_zero(): void
    {
        $this->assertSame(0.0, KprCalculator::monthly(0, 7.5, 120));
        $this->assertSame(0.0, KprCalculator::monthly(500_000_000, 7.5, 0));
    }
}
