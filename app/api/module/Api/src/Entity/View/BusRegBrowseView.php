<?php

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use JsonSerializable;

/**
 * Bus Reg Browse View
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="bus_reg_browse_view")
 */
class BusRegBrowseView implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;

    /**
     * Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     */
    protected $id;

    /**
     * Traffic area id
     *
     * @var string
     *
     * @ORM\Column(type="string", name="traffic_area_id")
     */
    protected $trafficAreaId;

    /**
     * Traffic area name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="traffic_area_name")
     */
    protected $trafficAreaName;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name")
     */
    protected $name;

    /**
     * Address
     *
     * @var string
     *
     * @ORM\Column(type="string", name="address")
     */
    protected $address;

    /**
     * Licence Number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_no")
     */
    protected $licNo;

    /**
     * Licence Status
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_status")
     */
    protected $licStatus;

    /**
     * Reg No
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reg_no")
     */
    protected $regNo;

    /**
     * Bus reg status
     *
     * @var string
     *
     * @ORM\Column(type="string", name="br_status")
     */
    protected $brStatus;

    /**
     * Bus service type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="bus_service_type")
     */
    protected $busServiceType;

    /**
     * Variation Number
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="variation_no")
     */
    protected $variationNo;

    /**
     * Received date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="received_date")
     */
    protected $receivedDate;

    /**
     * Effective date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="effective_date")
     */
    protected $effectiveDate;

    /**
     * End date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="end_date")
     */
    protected $endDate;

    /**
     * Service number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="service_no")
     */
    protected $serviceNo;

    /**
     * Is short notice
     *
     * @var bool
     *
     * @ORM\Column(type="string", name="is_short_notice")
     */
    protected $isShortNotice;

    /**
     * Start point
     *
     * @var string
     *
     * @ORM\Column(type="string", name="start_point")
     */
    protected $startPoint;

    /**
     * Finish point
     *
     * @var string
     *
     * @ORM\Column(type="string", name="finish_point")
     */
    protected $finishPoint;

    /**
     * Via
     *
     * @var string
     *
     * @ORM\Column(type="string", name="via")
     */
    protected $via;

    /**
     * Other details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="other_details")
     */
    protected $otherDetails;

    /**
     * Accepted date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="accepted_date")
     */
    protected $acceptedDate;

    /**
     * Event description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="event_description")
     */
    protected $eventDescription;

    /**
     * Event registration status
     *
     * @var string
     *
     * @ORM\Column(type="string", name="event_registration_status")
     */
    protected $eventRegistrationStatus;

    /**
     * Status
     *
     * @var string
     *
     * @ORM\Column(type="string", name="status")
     */
    protected $status;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get traffic area id
     *
     * @return string
     */
    public function getTrafficAreaId()
    {
        return $this->trafficAreaId;
    }

    /**
     * Get traffic area name
     *
     * @return string
     */
    public function getTrafficAreaName()
    {
        return $this->trafficAreaName;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get lic no
     *
     * @return string
     */
    public function getLicNo()
    {
        return $this->licNo;
    }

    /**
     * Get lic status
     *
     * @return string
     */
    public function getLicStatus()
    {
        return $this->licStatus;
    }

    /**
     * Get reg no
     *
     * @return string
     */
    public function getRegNo()
    {
        return $this->regNo;
    }

    /**
     * Get Bus Reg status
     *
     * @return string
     */
    public function getBrStatus()
    {
        return $this->brStatus;
    }

    /**
     * Get Bus Service type
     *
     * @return string
     */
    public function getBusServiceType()
    {
        return $this->busServiceType;
    }

    /**
     * Get variation no
     *
     * @return int
     */
    public function getVariationNo()
    {
        return $this->variationNo;
    }

    /**
     * Get received date
     *
     * @return \DateTime
     */
    public function getReceivedDate()
    {
        return $this->receivedDate;
    }

    /**
     * Get effective date
     *
     * @return \DateTime
     */
    public function getEffectiveDate()
    {
        return $this->effectiveDate;
    }

    /**
     * Get end date
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

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
     * Is short notice
     *
     * @return bool
     */
    public function isShortNotice()
    {
        return $this->isShortNotice;
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
     * Get other details
     *
     * @return string
     */
    public function getOtherDetails()
    {
        return $this->otherDetails;
    }

    /**
     * Get accepted date
     *
     * @return \DateTime
     */
    public function getAcceptedDate()
    {
        return $this->acceptedDate;
    }

    /**
     * Get event description
     *
     * @return string
     */
    public function getEventDescription()
    {
        return $this->eventDescription;
    }

    /**
     * Get event registration status
     *
     * @return string
     */
    public function getEventRegistrationStatus()
    {
        return $this->eventRegistrationStatus;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
