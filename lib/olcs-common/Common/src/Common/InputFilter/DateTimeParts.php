<?php

namespace Common\InputFilter;

class DateTimeParts extends DateParts
{
    #[\Override]
    protected function hasTimeParts(array $value): bool
    {
        foreach (['hour', 'minute', 'second'] as $part) {
            if (isset($value[$part]) && trim((string) $value[$part]) !== '') {
                return true;
            }
        }

        return false;
    }
}
