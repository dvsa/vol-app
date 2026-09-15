<?php

/**
 * Create Unlicensed Operator Licence Vehicle
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\LicenceVehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/operator-unlicensed/licence-vehicle")
 * @Transfer\Method("POST")
 */
final class CreateUnlicensedOperatorLicenceVehicle extends AbstractCommand
{
    use Organisation;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":20})
     * @Transfer\Optional
     */
    protected $vrm;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 999999})
     * @Transfer\Optional
     */
    protected $platedWeight;

    /**
     * Gets the value of vrm.
     *
     * @return mixed
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Gets the value of platedWeight.
     *
     * @return mixed
     */
    public function getPlatedWeight()
    {
        return $this->platedWeight;
    }
}
