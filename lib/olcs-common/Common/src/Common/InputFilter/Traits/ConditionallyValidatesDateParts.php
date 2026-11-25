<?php

namespace Common\InputFilter\Traits;

trait ConditionallyValidatesDateParts
{
    private bool $validateDateParts = true;

    public function setValidateDateParts(bool $validate): void
    {
        $this->validateDateParts = $validate;
    }

    protected function shouldValidateDateParts($context): bool
    {
        return $this->validateDateParts;
    }
}
