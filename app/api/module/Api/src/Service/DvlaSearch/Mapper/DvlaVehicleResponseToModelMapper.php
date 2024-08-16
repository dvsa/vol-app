<?php

namespace Dvsa\Olcs\Api\Service\DvlaSearch\Mapper;

use Dvsa\Olcs\Api\Service\DvlaSearch\Exception\BadResponseException;
use Dvsa\Olcs\Api\Service\DvlaSearch\Model\DvlaVehicle;

final class DvlaVehicleResponseToModelMapper
{
    /**
     * Maps the vehicle response (as array) to a DvlaVehicle object
     *
     * @param array<mixed> $vehicleArray
     * @return DvlaVehicle
     * @throws BadResponseException
     */
    public function map(array $vehicleArray): DvlaVehicle
    {
        if (!array_key_exists('vehicle', $vehicleArray)) {
            throw new BadResponseException("'vehicle' node missing from response");
        }
        $vehicleArray = $vehicleArray['vehicle'];

        return new DvlaVehicle(
            $this->getFromArray('registrationNumber', $vehicleArray),
            $this->getFromArray('taxStatus', $vehicleArray),
            $this->parseDate($this->getFromArray('taxDueDate', $vehicleArray)),
            $this->parseDate($this->getFromArray('artEndDate', $vehicleArray)),
            $this->getFromArray('motStatus', $vehicleArray),
            $this->parseDate($this->getFromArray('motExpiryDate', $vehicleArray)),
            $this->getFromArray('make', $vehicleArray),
            $this->parseDate($this->getFromArray('monthOfFirstDvlaRegistration', $vehicleArray), true),
            $this->parseDate($this->getFromArray('monthOfFirstRegistration', $vehicleArray), true),
            $this->getFromArray('yearOfManufacture', $vehicleArray),
            $this->getFromArray('engineCapacity', $vehicleArray),
            $this->getFromArray('co2Emissions', $vehicleArray),
            $this->getFromArray('fuelType', $vehicleArray),
            $this->getFromArray('markedForExport', $vehicleArray),
            $this->getFromArray('colour', $vehicleArray),
            $this->getFromArray('typeApproval', $vehicleArray),
            $this->getFromArray('wheelplan', $vehicleArray),
            $this->getFromArray('revenueWeight', $vehicleArray),
            $this->getFromArray('realDrivingEmissions', $vehicleArray),
            $this->parseDate($this->getFromArray('dateOfLastV5cIssued', $vehicleArray)),
            $this->getFromArray('euroStatus', $vehicleArray)
        );
    }

    /**
     * @param array<mixed> $array
     * @param null $default
     * @return mixed|null
     */
    private function getFromArray(string $key, array $array, $default = null)
    {
        return $array[$key] ?? $default;
    }

    /**
     * @return \DateTime|null
     */
    private function parseDate(?string $date, bool $ignoreDay = false): ?\DateTime
    {
        if (empty($date)) {
            return null;
        }
        $format = $ignoreDay ? 'Y-m|' : 'Y-m-d|';
        $dt = \DateTime::createFromFormat($format, $date);

        return $dt ?: null;
    }
}
