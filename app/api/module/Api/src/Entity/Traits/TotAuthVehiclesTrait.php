<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Traits;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use RuntimeException;

/**
 * @see \Dvsa\OlcsTest\Api\Entity\Traits\TotAuthVehiclesTraitTest
 */
trait TotAuthVehiclesTrait
{
    /**
     * Update the total number of hgv vehicles authorized and refresh the property containing the total of hgv and lgv
     * vehicles authorized.
     *
     * @return self
     */
    public function updateTotAuthHgvVehicles(?int $totAuthHgvVehicles): self
    {
        parent::setTotAuthHgvVehicles($totAuthHgvVehicles);
        return $this->updateTotAuthVehicles();
    }

    /**
     * Update the total number of lgv vehicles authorized and refresh the property containing the total of hgv and lgv
     * vehicles authorized.
     *
     * @return self
     */
    public function updateTotAuthLgvVehicles(?int $totAuthLgvVehicles): self
    {
        parent::setTotAuthLgvVehicles($totAuthLgvVehicles);
        return $this->updateTotAuthVehicles();
    }

    /**
     * Refresh the property containing the total of hgv and lgv authorized vehicles
     */
    private function updateTotAuthVehicles(): self
    {
        $this->totAuthVehicles = ($this->totAuthHgvVehicles ?? 0) + ($this->totAuthLgvVehicles ?? 0);
        return $this;
    }

    /**
     * Return the total HGV vehicle authorisation if not null, or zero if the value is null
     *
     * @return int
     */
    public function getTotAuthHgvVehiclesZeroCoalesced()
    {
        return $this->getTotAuthHgvVehicles() ?? 0;
    }

    /**
     * Return the total LGV vehicle authorisation if not null, or zero if the value is null
     *
     * @return int
     */
    public function getTotAuthLgvVehiclesZeroCoalesced()
    {
        return $this->getTotAuthLgvVehicles() ?? 0;
    }

    /**
     * Can this entity have LGV
     *
     * @return bool
     */
    public function canHaveLgv(): bool
    {
        return in_array(
            (string)$this->vehicleType,
            [
                RefData::APP_VEHICLE_TYPE_LGV,
                RefData::APP_VEHICLE_TYPE_MIXED,
            ]
        );
    }

    /**
     * Must this entity have LGV
     *
     * @return bool
     */
    public function mustHaveLgv(): bool
    {
        return $this->isLgv();
    }

    /**
     * Is this LGV only entity
     *
     * @return bool
     */
    public function isLgv(): bool
    {
        return (RefData::APP_VEHICLE_TYPE_LGV === (string)$this->vehicleType);
    }

    /**
     * Can this entity have HGV
     *
     * @return bool
     */
    public function canHaveHgv(): bool
    {
        return in_array(
            (string)$this->vehicleType,
            [
                RefData::APP_VEHICLE_TYPE_HGV,
                RefData::APP_VEHICLE_TYPE_MIXED,
                RefData::APP_VEHICLE_TYPE_PSV,
            ]
        );
    }

    /**
     * Can this entity have operating centre
     *
     * @return bool
     */
    public function canHaveOperatingCentre(): bool
    {
        // if it can have HGV it can have an Operating Centre
        return $this->canHaveHgv();
    }

    /**
     * Must this entity have operating centre
     *
     * @return bool
     */
    public function mustHaveOperatingCentre(): bool
    {
        // if it can have HGV it must have an Operating Centre
        return $this->canHaveHgv();
    }

    /**
     * Can this entity have trailer
     *
     * @return bool
     */
    public function canHaveTrailer(): bool
    {
        return in_array(
            (string)$this->vehicleType,
            [
                RefData::APP_VEHICLE_TYPE_HGV,
                RefData::APP_VEHICLE_TYPE_MIXED,
            ]
        );
    }

    /**
     * Get the list of authorisation properties applicable to this licence/application
     *
     * @return array
     */
    public function getApplicableAuthProperties()
    {
        if (is_null($this->vehicleType)) {
            return [];
        }

        if ((string)$this->licenceType == Licence::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return [];
        }

        if (
            (string)$this->vehicleType == RefData::APP_VEHICLE_TYPE_MIXED &&
            is_null($this->totAuthLgvVehicles)
        ) {
            return [
                'totAuthVehicles',
                'totAuthTrailers'
            ];
        }

        $typeMappings = [
            RefData::APP_VEHICLE_TYPE_PSV => [
                'totAuthVehicles',
            ],
            RefData::APP_VEHICLE_TYPE_HGV => [
                'totAuthVehicles',
                'totAuthTrailers',
            ],
            RefData::APP_VEHICLE_TYPE_MIXED => [
                'totAuthHgvVehicles',
                'totAuthLgvVehicles',
                'totAuthTrailers',
            ],
            RefData::APP_VEHICLE_TYPE_LGV => [
                'totAuthLgvVehicles',
            ],
        ];

        $vehicleTypeId = $this->vehicleType->getId();
        if (!isset($typeMappings[$vehicleTypeId])) {
            throw new RuntimeException('Unrecognised vehicle type id: ' . $vehicleTypeId);
        }

        return $typeMappings[$vehicleTypeId];
    }

    /**
     * Whether this licence/application has a mixed vehicle type and a non-null lgv authorisation
     *
     * @return bool
     */
    public function isVehicleTypeMixedWithLgv()
    {
        return (string)$this->vehicleType == RefData::APP_VEHICLE_TYPE_MIXED && !is_null($this->totAuthLgvVehicles);
    }
}
