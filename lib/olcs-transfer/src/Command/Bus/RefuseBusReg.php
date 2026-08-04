<?php

/**
 * Refuse BusReg
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/decision/refuse")
 * @Transfer\Method("PUT")
 */
final class RefuseBusReg extends AbstractCommand
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
