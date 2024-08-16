<?php

/**
 * CreateApplicationOperatingCentre.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Application;

/**
 * Class CreateApplicationOperatingCentre
 *
 * @package Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class CreateApplicationOperatingCentre extends AbstractCommand
{
    use Application;

    protected $operatingCentre;

    protected $action;

    protected $adPlaced;

    protected $noOfVehiclesRequired;

    protected $noOfTrailersRequired;

    protected $s4;

    /**
     * @return mixed
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getAdPlaced()
    {
        return $this->adPlaced;
    }

    /**
     * @return mixed
     */
    public function getNoOfVehiclesRequired()
    {
        return $this->noOfVehiclesRequired;
    }

    /**
     * @return mixed
     */
    public function getNoOfTrailersRequired()
    {
        return $this->noOfTrailersRequired;
    }

    /**
     * @return mixed
     */
    public function getS4()
    {
        return $this->s4;
    }
}
