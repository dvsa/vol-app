<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\OlcsTest\Builder\BuilderInterface;

class ApplicationOperatingCentreBuilder implements BuilderInterface
{
    /**
     * @var ApplicationOperatingCentre
     */
    protected $instance;

    /**
     * @param int|null $id
     */
    public function __construct(Application $application, ?int $id = null)
    {
        $oc = new OperatingCentre();
        $this->instance = new ApplicationOperatingCentre($application, $oc);
        if ($id) {
            $oc->setId($id ?? 1);
            $this->instance->setId($id ?? 1);
        }
    }

    /**
     * @return self
     */
    public function withVehicleCapacity(int $noOfVehicles): self
    {
        $this->instance->setNoOfVehiclesRequired($noOfVehicles);
        return $this;
    }

    /**
     * @return self
     */
    public function withAction(string $action): self
    {
        $this->instance->setAction($action);
        return $this;
    }

    /**
     * @return ApplicationOperatingCentre
     */
    public function build(): ApplicationOperatingCentre
    {
        return $this->instance;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public static function forApplication(Application $application, ?int $id = null): self
    {
        return new static($application, $id);
    }
}
