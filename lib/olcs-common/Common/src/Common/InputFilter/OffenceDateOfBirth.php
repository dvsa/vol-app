<?php

namespace Common\InputFilter;

class OffenceDateOfBirth extends PersonDateOfBirth
{
    #[\Override]
    protected function shouldValidateDateParts($context): bool
    {
        return !is_array($context) || !in_array($context['defendantType'] ?? null, ['def_t_op', ''], true);
    }
}
