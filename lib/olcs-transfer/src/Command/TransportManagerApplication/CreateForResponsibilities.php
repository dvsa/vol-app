<?php

/**
 * Create Transport Manager Application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/tm-responsibilities/transport-manager-application")
 * @Transfer\Method("POST")
 */
final class CreateForResponsibilities extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $application;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $transportManager;

    /**
     * Get Application ID
     *
     * @return int
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get Transport Manager ID
     *
     * @return int
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }
}
