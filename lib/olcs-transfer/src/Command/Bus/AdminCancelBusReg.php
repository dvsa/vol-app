<?php

/**
 * Admin Cancel BusReg
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/decision/admin-cancel")
 * @Transfer\Method("PUT")
 */
final class AdminCancelBusReg extends AbstractCommand
{
    use FieldType\Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
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
