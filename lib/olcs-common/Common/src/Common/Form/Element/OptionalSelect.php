<?php

namespace Common\Form\Element;

use Laminas\Form\Element\Select;

/**
 * This class exists as the default Select class is marked as required by default.
 */
class OptionalSelect extends Select
{
    #[\Override]
    public function getInputSpecification(): array
    {
        $spec = parent::getInputSpecification();

        $spec['required'] = false;

        return $spec;
    }
}
