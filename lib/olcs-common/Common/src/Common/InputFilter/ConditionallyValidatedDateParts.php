<?php

namespace Common\InputFilter;

class ConditionallyValidatedDateParts extends DateParts
{
    use Traits\ConditionallyValidatesDateParts;
}
