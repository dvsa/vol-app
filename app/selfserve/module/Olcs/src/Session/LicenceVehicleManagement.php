<?php

namespace Olcs\Session;

/**
 * Class LicenceVehicleManagement
 *
 * @template-extends \Laminas\Session\Container<string, mixed>
 */
class LicenceVehicleManagement extends \Laminas\Session\Container
{
    public const SESSION_NAME = 'LicenceVehicleManagement';
    protected const TRANSFER_TO_LICENSE_ID = 'transferToLicenceId';

    /**
     * LicenceVehicleManagement constructor.
     */
    public function __construct()
    {
        parent::__construct(self::SESSION_NAME);
    }

    /**
     * @return bool
     */
    public function hasVrm(): bool
    {
        return $this->offsetExists('vrm');
    }

    /**
     * @return string
     */
    public function getVrm()
    {
        return $this->offsetGet('vrm');
    }

    /**
     * @return $this
     */
    public function setVrm(string $vrm): LicenceVehicleManagement
    {
        $this->offsetSet('vrm', $vrm);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasVrms(): bool
    {
        return $this->offsetExists('vrms');
    }

    /**
     * @return array
     */
    public function getVrms()
    {
        return $this->offsetGet('vrms');
    }

    /**
     * @return $this
     */
    public function setVrms(array $vrm): LicenceVehicleManagement
    {
        $this->offsetSet('vrms', $vrm);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasVehicleData(): bool
    {
        return $this->offsetExists('vehicleData');
    }

    /**
     * @return array|null
     */
    public function getVehicleData()
    {
        return $this->offsetGet('vehicleData');
    }

    /**
     * @return $this
     */
    public function setVehicleData(array $vehicleData): LicenceVehicleManagement
    {
        $this->offsetSet('vehicleData', $vehicleData);
        return $this;
    }

    /**
     * Determines whether a session has a licence id set for which a related set of vehicles should be transferred to.
     *
     * @return bool
     */
    public function hasDestinationLicenceId(): bool
    {
        return $this->offsetExists(static::TRANSFER_TO_LICENSE_ID);
    }

    /**
     * Gets a licence id for which a related set of vehicles should be transferred to.
     *
     * @return int|null
     */
    public function getDestinationLicenceId(): ?int
    {
        return $this->offsetGet(static::TRANSFER_TO_LICENSE_ID) ?: null;
    }

    /**
     * Sets a licence id for which a related set of vehicles should be transferred to.
     *
     * @return $this
     */
    public function setDestinationLicenceId(int $id)
    {
        $this->offsetSet(static::TRANSFER_TO_LICENSE_ID, $id);
        return $this;
    }

    /**
     * Destroys session container
     */
    public function destroy(): void
    {
        $this->getManager()->getStorage()->clear(static::SESSION_NAME);
    }
}
