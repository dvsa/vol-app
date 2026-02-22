<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Builder\BuilderInterface;
use Dvsa\OlcsTest\Api\Entity\Licence\LicenceBuilder;
use Doctrine\Common\Collections\ArrayCollection;

class ApplicationBuilder implements BuilderInterface
{
    protected const AN_ID = 1;
    protected const ANOTHER_ID = 2;

    /**
     * @var Application
     */
    protected $instance;

    /**
     * @param int|null $id
     */
    public function __construct(Licence $licence, ?int $id = null, bool $isVariation = false)
    {
        $this->instance = new Application($licence, new RefData(Application::APPLICATION_STATUS_NOT_SUBMITTED), $isVariation);
        $this->instance->setId($id ?? self::AN_ID);
        $this->instance->copyInformationFromLicence($licence);
    }

    /**
     * @return $this
     */
    public function withCompletionShowingUpdatedOperatingCentres(): self
    {
        $this->instance->setApplicationCompletion(
            ApplicationCompletionBuilder::forApplication($this->instance)->withUpdatedOperatingCentresSection()->build()
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function withApplicationCompletion(): self
    {
        $this->instance->setApplicationCompletion(ApplicationCompletionBuilder::forApplication($this->instance)->build());
        return $this;
    }

    /**
     * @return $this
     */
    public function authorizedFor(int $hgvCount = 0, int $lgvCount = 0): self
    {
        $this->instance->updateTotAuthHgvVehicles($hgvCount);
        $this->instance->updateTotAuthLgvVehicles($lgvCount);
        return $this;
    }

    /**
     * @return $this
     */
    public function forMixedVehicleType(): self
    {
        $this->instance->setVehicleType(new RefData(RefData::APP_VEHICLE_TYPE_MIXED));
        return $this;
    }

    /**
     * @return $this
     */
    public function withValidAuthorizations(): self
    {
        $operatingCentre = ApplicationOperatingCentreBuilder::forApplication($this->instance, static::AN_ID)->build();
        $operatingCentre->setNoOfVehiclesRequired($this->instance->getTotAuthHgvVehicles());
        $this->instance->setOperatingCentres(new ArrayCollection([$operatingCentre]));
        return $this;
    }

    /**
     * @return $this
     */
    public function withExtraOperatingCentreCapacityFor(int $extraVehicles): self
    {
        $operatingCentre1 = ApplicationOperatingCentreBuilder::forApplication($this->instance, static::AN_ID)->build();
        $operatingCentre1->setNoOfVehiclesRequired($this->instance->getTotAuthHgvVehicles());

        $operatingCentre2 = ApplicationOperatingCentreBuilder::forApplication($this->instance, static::ANOTHER_ID)->build();
        $operatingCentre2->setNoOfVehiclesRequired($extraVehicles);

        $this->instance->setOperatingCentres(new ArrayCollection([$operatingCentre1, $operatingCentre2]));
        return $this;
    }

    /**
     * @param array[] $operatingCentresCapacities Arrays of operating centre capacities in the format [hgvs]
     * @return $this
     */
    public function withOperatingCentresWithCapacitiesFor(array $operatingCentresCapacities): self
    {
        $operatingCentres = [];
        foreach (array_values($operatingCentresCapacities) as $i => $operatingCentreCapacities) {
            $operatingCentres[] = ApplicationOperatingCentreBuilder::forApplication($this->instance, $i)
                ->withVehicleCapacity(...$operatingCentreCapacities)
                ->build();
        }
        $this->instance->setOperatingCentres(new ArrayCollection($operatingCentres));
        return $this;
    }


    /**
     * @return Application
     */
    public function build(): Application
    {
        return $this->instance;
    }

    /**
     * @param Licence|LicenceBuilder $licence
     * @param int|null $id
     * @return self
     */
    public static function applicationForLicence(mixed $licence, ?int $id = null): self
    {
        if ($licence instanceof LicenceBuilder) {
            $licence = $licence->build();
        }
        return new static($licence, $id, false);
    }

    /**
     * @param int|null $id
     * @return self
     */
    public static function application(?int $id = null): self
    {
        return static::applicationForLicence(LicenceBuilder::aLicence($id));
    }

    /**
     * @param Licence|LicenceBuilder $licence
     * @param int|null $id
     * @return self
     */
    public static function variationForLicence(mixed $licence, ?int $id = null): self
    {
        if ($licence instanceof LicenceBuilder) {
            $licence = $licence->build();
        }
        return new static($licence, $id, true);
    }

    /**
     * @param int|null $id
     * @return self
     */
    public static function variation(?int $id = null): self
    {
        return static::variationForLicence(LicenceBuilder::aLicence($id));
    }
}
