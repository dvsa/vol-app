<?php

/**
 * TransportManagerDeleteDelta
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/variation/single/transport-manager-delete-delta")
 * @Transfer\Method("POST")
 */
final class TransportManagerDeleteDelta extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $transportManagerLicenceIds = [];

    /**
     * Get Application ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Transport Manager Licence IDs
     *
     * @return array
     */
    public function getTransportManagerLicenceIds()
    {
        return $this->transportManagerLicenceIds;
    }
}
