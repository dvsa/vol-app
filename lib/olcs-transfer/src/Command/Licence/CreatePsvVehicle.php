<?php

/**
 * Create Psv Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\FieldType\Traits\Vrm;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/licence/named-single/psv-vehicles")
 * @Transfer\Method("POST")
 */
final class CreatePsvVehicle extends AbstractCommand
{
    use Licence;
    use Vrm;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":100})
     * @Transfer\Optional
     */
    protected $makeModel;

    /**
     * @Transfer\Optional
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $receivedDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\DateTimeFormatter")
     * @Transfer\Validator("Date", options={"format": \DateTime::ISO8601})
     */
    protected $specifiedDate;

    /**
     * @return mixed
     */
    public function getMakeModel()
    {
        return $this->makeModel;
    }

    /**
     * @return mixed
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * @return mixed
     */
    public function getSpecifiedDate()
    {
        return $this->specifiedDate;
    }
}
