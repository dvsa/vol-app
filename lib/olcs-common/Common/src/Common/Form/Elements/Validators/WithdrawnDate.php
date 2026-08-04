<?php

/**
 * Custom validator for isWithdrawn.
 * If the checkbox is ticked, the datefield will be validated
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator as LaminasValidator;

/**
 * Custom validator for isWithdrawn.
 * If the checkbox is ticked, the date field will be validated
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class WithdrawnDate extends LaminasValidator\AbstractValidator
{
    public const DATE_NOT_VALID = 'dateNotValid';

    public const DATE_IN_FUTURE = 'dateInFuture';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::DATE_NOT_VALID   => "Withdrawn date is not valid",
        self::DATE_IN_FUTURE    => "Withdrawn date can't be in the future",
    ];

    #[\Override]
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if ($context['isWithdrawn'] == 'Y') {
            $withdrawnDate = $context['withdrawnDate']['year'] .
                '-' . $context['withdrawnDate']['month'] .
                '-' . $context['withdrawnDate']['day'];

            $dateValidator = new Date();

            if (!$dateValidator->isValid($withdrawnDate)) {
                $this->error(self::DATE_NOT_VALID);
                return false;
            }

            $notInFutureValidator = new DateNotInFuture();

            if (!$notInFutureValidator->isValid($withdrawnDate)) {
                $this->error(self::DATE_IN_FUTURE);
                return false;
            }
        }

        return true;
    }
}
