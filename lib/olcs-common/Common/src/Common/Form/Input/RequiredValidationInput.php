<?php

namespace Common\Form\Input;

use Laminas\InputFilter\Input;
use Laminas\Validator\NotEmpty;

class RequiredValidationInput extends Input
{
    protected $isEmptyMessage = "Value is required and can't be empty";

    #[\Override]
    protected function prepareRequiredValidationFailureMessage()
    {
        return [
            NotEmpty::IS_EMPTY => $this->isEmptyMessage
        ];
    }
}
