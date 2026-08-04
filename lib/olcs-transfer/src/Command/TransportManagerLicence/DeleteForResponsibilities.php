<?php

namespace Dvsa\Olcs\Transfer\Command\TransportManagerLicence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Delete Inspection Request
 *
 * @Transfer\RouteName("backend/tm-responsibilities/transport-manager-licence")
 * @Transfer\Method("DELETE")
 */
class DeleteForResponsibilities extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * @return mixed
     */
    public function getIds()
    {
        return $this->ids;
    }
}
