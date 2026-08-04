<?php

/**
 * Withdraw BusReg
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/decision/withdraw")
 * @Transfer\Method("PUT")
 */
final class WithdrawBusReg extends AbstractCommand
{
    use FieldType\Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"reg_in_error","withdrawn"}})
     */
    public $reason;

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
