<?php

/**
 * Curtail Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType;

/**
 * @Transfer\RouteName("backend/licence/single/decisions/curtail")
 * @Transfer\Method("POST")
 */
final class CurtailLicence extends AbstractCommand
{
    use FieldType\Traits\DecisionsOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     */
    protected $deleteLicenceStatusRules = true;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getDeleteLicenceStatusRules()
    {
        return $this->deleteLicenceStatusRules;
    }
}
