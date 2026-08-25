<?php

namespace Aegisora\RuleGuardians\RegexRule;

use Aegisora\Guardian\Exceptions\GuardianExecutingRuleException;
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\Guardian\Guardian;
use Aegisora\Rules\RegexRule;
use Throwable;

class RegexRuleGuardian
{
    private Guardian $guardian;

    public function __construct(
        Guardian $guardian
    ) {
        $this->guardian = $guardian;
    }

    /**
     * @param mixed $value
     * @throws GuardianExecutingRuleException
     * @throws GuardianValidationException
     * @throws Throwable
     */
    public function check(
        $value,
        string $pattern,
        ?Throwable $exception = null
    ): void {
        $this->guardian->check($value, RegexRule::create($pattern), $exception);
    }
}
