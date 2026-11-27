<?php

namespace Common\InputFilter;

use Laminas\InputFilter\Input;

class DateParts extends Input
{
    private array $dateMessages = [];

    #[\Override]
    public function isValid($context = null)
    {
        $this->dateMessages = [];
        $value = $this->getRawValue();

        if (!is_array($value) || !$this->shouldValidateDateParts($context)) {
            return parent::isValid($context);
        }

        $parts = [];
        $missing = [];
        foreach (['day', 'month', 'year'] as $part) {
            $parts[$part] = trim((string) ($value[$part] ?? ''));
            if ($parts[$part] === '') {
                $missing[] = $part;
            }
        }

        $hasTime = $this->hasTimeParts($value);
        if (count($missing) === 3 && !$hasTime && (!$this->isRequired() || $this->allowEmpty())) {
            return true;
        }

        if (count($missing) === 3 && !$hasTime) {
            $this->dateMessages['dateMissing'] = 'Enter {{fieldLabel}}';
        } elseif ($missing !== []) {
            $last = array_pop($missing);
            $missingParts = $missing === [] ? $last : implode(', ', $missing) . ' and ' . $last;
            $this->dateMessages['dateIncomplete'] = '{{fieldLabel}} must include a ' . $missingParts;
        } elseif (
            !ctype_digit($parts['day'])
            || !ctype_digit($parts['month'])
            || !preg_match('/^[0-9]{4}$/D', $parts['year'])
            || (int) $parts['year'] < 1000
            || !checkdate((int) $parts['month'], (int) $parts['day'], (int) $parts['year'])
        ) {
            $this->dateMessages['dateInvalid'] = '{{fieldLabel}} must be a real date';
        }

        return $this->dateMessages === [] && parent::isValid($context);
    }

    #[\Override]
    public function getMessages()
    {
        return $this->dateMessages !== [] ? $this->dateMessages : parent::getMessages();
    }

    protected function shouldValidateDateParts($context): bool
    {
        return true;
    }

    protected function hasTimeParts(array $value): bool
    {
        return false;
    }
}
