<?php

/**
 * Bus stops
 */

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/service-details")
 * @Transfer\Method("PUT")
 */
final class UpdateServiceDetails extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":70})
     * @Transfer\Optional
     */
    public $serviceNo;

    /**
     * @Transfer\ArrayInput
     * @Transfer\Optional
     */
    public $otherServices = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":100})
     * @Transfer\Optional
     */
    public $startPoint;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":100})
     * @Transfer\Optional
     */
    public $finishPoint;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":255})
     * @Transfer\Optional
     */
    public $via;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $busServiceTypes = [];

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"max":800})
     * @Transfer\Optional
     */
    public $otherDetails;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    public $receivedDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    public $effectiveDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    public $endDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     * @Transfer\Optional
     */
    public ?string $applicationCompleteDate = null;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $busNoticePeriod;

    /**
     * Get service no
     *
     * @return string
     */
    public function getServiceNo()
    {
        return $this->serviceNo;
    }

    /**
     * Get other services
     *
     * @return array
     */
    public function getOtherServices()
    {
        return $this->otherServices;
    }

    /**
     * Get start point
     *
     * @return string
     */
    public function getStartPoint()
    {
        return $this->startPoint;
    }

    /**
     * Get finish point
     *
     * @return string
     */
    public function getFinishPoint()
    {
        return $this->finishPoint;
    }

    /**
     * Get via
     *
     * @return string
     */
    public function getVia()
    {
        return $this->via;
    }

    /**
     * Get bus service types
     *
     * @return array
     */
    public function getBusServiceTypes()
    {
        return $this->busServiceTypes;
    }

    /**
     * Get other details
     *
     * @return string
     */
    public function getOtherDetails()
    {
        return $this->otherDetails;
    }

    /**
     * Get received date
     *
     * @return string
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * Get effective date
     *
     * @return string
     */
    public function getEffectiveDate()
    {
        return $this->effectiveDate;
    }

    /**
     * Get end date
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    public function getApplicationCompleteDate(): ?string
    {
        return $this->applicationCompleteDate;
    }

    /**
     * Get bus notice period
     *
     * @return int
     */
    public function getBusNoticePeriod()
    {
        return $this->busNoticePeriod;
    }
}
