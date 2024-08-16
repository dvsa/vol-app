<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\RulesValidator;

use Laminas\Validator\AbstractValidator;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;

/**
 * Class EndDate
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class EndDate extends AbstractValidator
{
    public const END_DATE_CANCELLATION_ERROR = 'end-date-cancellation-error';

    /**
     * @var array
     */
    protected $messageTemplates = [
        self::END_DATE_CANCELLATION_ERROR => 'Cancellations should not have an end date'
    ];

    /**
     * Validates the endDate field. Right now, we only check that it's empty for cancellations
     *
     * @param array $value input value
     *
     * @return bool
     */
    public function isValid($value)
    {
        if (!empty($value['endDate']) && $value['txcAppType'] === BusRegEntity::TXC_APP_CANCEL) {
            $this->error(self::END_DATE_CANCELLATION_ERROR);
            return false;
        }

        return true;
    }
}
