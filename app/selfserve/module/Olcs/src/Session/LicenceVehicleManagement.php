<?php


namespace Olcs\Session;

class LicenceVehicleManagement extends \Zend\Session\Container
{
    const SESSION_NAME = 'LicenceVehicleManagement';


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
     * @param string $vrm
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
     * @param array $vrm
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
     * @param array $vehicleData
     * @return $this
     */
    public function setVehicleData(array $vehicleData): LicenceVehicleManagement
    {
        $this->offsetSet('vehicleData', $vehicleData);
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
