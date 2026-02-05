<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\OlcsTest\Builder\BuilderInterface;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\Licence;

class LicenceOperatingCentreBuilder implements BuilderInterface
{
    protected const AN_ID = 1;

    /**
     * @var LicenceOperatingCentre
     */
    protected $instance;

    public function __construct(Licence $licence, ?int $id = self::AN_ID)
    {
        $oc = new OperatingCentre();
        $oc->setId($id);
        $this->instance = new LicenceOperatingCentre($licence, $oc);
        $this->instance->setId($id);
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
     * @return LicenceOperatingCentre
     */
    public function build(): LicenceOperatingCentre
    {
        return $this->instance;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public static function forLicence(Licence $licence, ?int $id = null): self
    {
        return new static($licence, $id);
    }
}
