<?php

namespace Aegisora\RuleGuardians\RegexRule;

use Aegisora\Guardian\Guardian;

class RegexRuleGuardian
{
    private Guardian $guardian;

    public function __construct(
        Guardian $guardian
    ) {
        $this->guardian = $guardian;
    }
}
