<?php

/**
 * Update Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/vehicles")
 * @Transfer\Method("PUT")
 */
final class UpdateVehicles extends AbstractCommand
{
    use Identity;
    use Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToUpper")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y","N"}})
     */
    protected $hasEnteredReg;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $partial = false;

    /**
     * @return mixed
     */
    public function getHasEnteredReg()
    {
        return $this->hasEnteredReg;
    }

    /**
     * @return mixed
     */
    public function getPartial()
    {
        return $this->partial;
    }
}
