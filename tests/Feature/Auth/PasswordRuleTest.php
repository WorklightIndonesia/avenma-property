<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

class PasswordRuleTest extends TestCase
{
    public function test_default_rule_requires_mixed_case_number_and_symbol(): void
    {
        $rules = ['password' => ['required', Password::defaults()]];

        $this->assertTrue(Validator::make(['password' => 'Agenbud1!'], $rules)->passes());
        $this->assertTrue(Validator::make(['password' => 'agenbudi'], $rules)->fails());
        $this->assertTrue(Validator::make(['password' => 'Agenbud'], $rules)->fails());
    }
}
