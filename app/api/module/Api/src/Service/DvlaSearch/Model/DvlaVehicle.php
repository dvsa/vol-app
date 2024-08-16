<?php

namespace Dvsa\Olcs\Api\Service\DvlaSearch\Model;

/**
 * Class DvlaVehicle
 * @package Dvsa\Olcs\Api\Service\DvlaSearch\Model
 *
 * Object representation of a vehicle returned from DVSA Vehicle Enquiry API
 * https://developer-portal.driver-vehicle-licensing.api.gov.uk/apis/vehicle-enquiry-service/v1.1.0-vehicle-enquiry-service.html#vehicle-enquiry-api
 */
class DvlaVehicle
{
    public function __construct(
        /**
         * Registration number of the vehicle
         */
        private readonly string $registrationNumber,
        /**
         * Tax status of the vehicle
         * Enum:
         *      Not Taxed for on Road Use
         *      SORN
         *      Taxed
         *      Untaxed
         */
        private readonly ?string $taxStatus,
        /**
         * Date of tax liability, used in calculating licence information presented to user
         */
        private readonly ?\DateTime $taxDueDate,
        /**
         * Additional Rate of Tax End Date
         */
        private readonly ?\DateTime $artEndDate,
        /**
         * MOT Status of the vehicle
         * Enum:
         *      No details held by DVLA
         *      No results returned
         *      Not valid
         *      Valid
         */
        private readonly ?string $motStatus,
        /**
         * Mot Expiry Date
         */
        private readonly ?\DateTime $motExpiryDate,
        /**
         * Vehicle make
         */
        private readonly ?string $make,
        /**
         * Month of First DVLA Registration
         */
        private readonly ?\DateTime $monthOfFirstDvlaRegistration,
        /**
         * Month of First Registration
         */
        private readonly ?\DateTime $monthOfFirstRegistration,
        /**
         * Year of Manufacture
         */
        private readonly ?int $yearOfManufacture,
        /**
         * Engine capacity in cubic centimetres
         */
        private readonly ?int $engineCapacity,
        /**
         * Carbon Dioxide emissions in grams per kilometre
         */
        private readonly ?int $co2Emissions,
        /**
         * Fuel type (Method of Propulsion)
         */
        private readonly ?string $fuelType,
        /**
         * True only if vehicle has been export marked
         */
        private readonly ?bool $markedForExport,
        /**
         * Vehicle colour
         */
        private readonly ?string $colour,
        /**
         * Vehicle Type Approval Category
         */
        private readonly ?string $typeApproval,
        /**
         * Vehicle wheel plan
         */
        private readonly ?string $wheelplan,
        /**
         * Revenue weight in kilograms
         */
        private readonly ?int $revenueWeight,
        /**
         * Real Driving Emissions value
         */
        private readonly ?string $realDrivingEmissions,
        /**
         * Date of last V5C issued
         */
        private readonly ?\DateTime $dateOfLastV5CIssued,
        /**
         * Euro Status (Dealer / Customer Provided (new vehicles))
         */
        private readonly ?string $euroStatus
    ) {
    }

    public function getRegistrationNumber(): string
    {
        return $this->registrationNumber;
    }

    public function getTaxStatus(): ?string
    {
        return $this->taxStatus;
    }

    public function getTaxDueDate(): ?\DateTime
    {
        return $this->taxDueDate;
    }

    public function getArtEndDate(): ?\DateTime
    {
        return $this->artEndDate;
    }

    public function getMotStatus(): ?string
    {
        return $this->motStatus;
    }

    public function getMotExpiryDate(): ?\DateTime
    {
        return $this->motExpiryDate;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function getMonthOfFirstDvlaRegistration(): ?\DateTime
    {
        return $this->monthOfFirstDvlaRegistration;
    }

    public function getMonthOfFirstRegistration(): ?\DateTime
    {
        return $this->monthOfFirstRegistration;
    }

    public function getYearOfManufacture(): ?int
    {
        return $this->yearOfManufacture;
    }

    public function getEngineCapacity(): ?int
    {
        return $this->engineCapacity;
    }

    public function getCo2Emissions(): ?int
    {
        return $this->co2Emissions;
    }

    public function getFuelType(): ?string
    {
        return $this->fuelType;
    }

    public function getMarkedForExport(): ?bool
    {
        return $this->markedForExport;
    }

    public function getColour(): ?string
    {
        return $this->colour;
    }

    public function getTypeApproval(): ?string
    {
        return $this->typeApproval;
    }

    public function getWheelplan(): ?string
    {
        return $this->wheelplan;
    }

    public function getRevenueWeight(): ?int
    {
        return $this->revenueWeight;
    }

    public function getRealDrivingEmissions(): ?string
    {
        return $this->realDrivingEmissions;
    }

    public function getDateOfLastV5CIssued(): ?\DateTime
    {
        return $this->dateOfLastV5CIssued;
    }

    public function getEuroStatus(): ?string
    {
        return $this->euroStatus;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'registrationNumber' => $this->getRegistrationNumber(),
            'taxStatus' => $this->getTaxStatus(),
            'taxDueDate' => $this->getTaxDueDate(),
            'artEndDate' => $this->getArtEndDate(),
            'motStatus' => $this->getMotStatus(),
            'motExpiryDate' => $this->getMotExpiryDate(),
            'make' => $this->getMake(),
            'monthOfFirstDvlaRegistration' => $this->getMonthOfFirstDvlaRegistration(),
            'monthOfFirstRegistration' => $this->getMonthOfFirstRegistration(),
            'yearOfManufacture' => $this->getYearOfManufacture(),
            'engineCapacity' => $this->getEngineCapacity(),
            'co2Emissions' => $this->getCo2Emissions(),
            'fuelType' => $this->getFuelType(),
            'markedForExport' => $this->getMarkedForExport(),
            'colour' => $this->getColour(),
            'typeApproval' => $this->getTypeApproval(),
            'wheelplan' => $this->getWheelplan(),
            'revenueWeight' => $this->getRevenueWeight(),
            'realDrivingEmissions' => $this->getRealDrivingEmissions(),
            'dateOfLastV5CIssued' => $this->getDateOfLastV5CIssued(),
            'euroStatus' => $this->getEuroStatus()
        ];
    }
}
