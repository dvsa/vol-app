<?php

namespace Common\InputFilter;

class ConditionallyValidatedDateTimeParts extends DateTimeParts
{
    use Traits\ConditionallyValidatesDateParts;
}
