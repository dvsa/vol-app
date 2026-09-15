<?php

/**
 * Update Psv Licence Vehicle
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\LicenceVehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/licence-vehicle/single/psv")
 * @Transfer\Method("PUT")
 */
final class UpdatePsvLicenceVehicle extends AbstractCommand
{
    use Identity;
    use Version;
    use ApplicationOptional;
    use LicenceOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":2,"max":100})
     * @Transfer\Optional
     */
    protected $makeModel;

    /**
     * @Transfer\Optional
     */
    protected $receivedDate;

    /**
     * @Transfer\Optional
     */
    protected $removalDate;

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
    public function getRemovalDate()
    {
        return $this->removalDate;
    }

    /**
     * @return mixed
     */
    public function getSpecifiedDate()
    {
        return $this->specifiedDate;
    }
}
