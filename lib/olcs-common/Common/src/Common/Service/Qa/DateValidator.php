<?php

namespace Common\Service\Qa;

use Laminas\Validator\Date as LaminasDateValidator;

class DateValidator extends LaminasDateValidator
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function error($messageKey, $value = null)
    {
        // suppress the creation of the FALSEFORMAT error message to prevent an invalid date from generating two
        // error messages

        if ($messageKey == LaminasDateValidator::FALSEFORMAT) {
            return;
        }

        parent::error($messageKey, $value);
    }
}
