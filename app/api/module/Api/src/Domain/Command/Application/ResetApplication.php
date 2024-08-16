<?php

/**
 * Reset Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Reset Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ResetApplication extends AbstractCommand
{
    protected $id;

    protected $operatorType;

    protected $licenceType;

    protected $vehicleType;

    protected $lgvDeclarationConfirmation;

    protected $niFlag;

    protected $confirm = false;

    public function getId()
    {
        return $this->id;
    }

    public function getOperatorType()
    {
        return $this->operatorType;
    }

    public function getLicenceType()
    {
        return $this->licenceType;
    }

    public function getVehicleType()
    {
        return $this->vehicleType;
    }

    public function getLgvDeclarationConfirmation()
    {
        return $this->lgvDeclarationConfirmation;
    }

    public function getNiFlag()
    {
        return $this->niFlag;
    }

    public function getConfirm()
    {
        return $this->confirm;
    }
}
