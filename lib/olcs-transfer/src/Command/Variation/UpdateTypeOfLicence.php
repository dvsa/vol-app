<?php

/**
 * Type Of Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\LgvDeclarationConfirmation;
use Dvsa\Olcs\Transfer\FieldType\Traits\VehicleType;

/**
 * @Transfer\RouteName("backend/variation/single/type-of-licence")
 * @Transfer\Method("PUT")
 */
final class UpdateTypeOfLicence extends AbstractCommand
{
    use LgvDeclarationConfirmation;
    use VehicleType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={"haystack": {"ltyp_r","ltyp_sn","ltyp_si","ltyp_sr"}}
     * )
     */
    protected $licenceType;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $confirm = false;

    public function getId()
    {
        return $this->id;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getLicenceType()
    {
        return $this->licenceType;
    }

    public function getConfirm()
    {
        return $this->confirm;
    }
}
