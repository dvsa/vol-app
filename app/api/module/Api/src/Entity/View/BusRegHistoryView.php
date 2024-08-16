<?php

/**
 * Bus Reg History View
 *
 * @NOTE: This walks and talks like an entity but be warned, it is backed
 * by a view. As such it is is nicely readable and searchable, but writes
 * are a no go.
 *
 * You'll notice that the entity has no setters; this is intentional to
 * try and prevent accidental writes. It's marked as readOnly too to
 * prevent doctrine including it in any flushes
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Entity\View;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistory;
use Dvsa\Olcs\Api\Entity\User\User;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;

/**
 * Bus Reg History View
 *
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="bus_reg_history_view")
 */
class BusRegHistoryView implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;

    /**
     * id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     */
    protected $id;

    /**
     * busReg
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="bus_reg_id")
     */
    protected $busReg;

    /**
     * Change made by
     *
     * @var string
     *
     * @ORM\Column(type="string", name="change_made_by", nullable=true)
     */
    protected $changeMadeBy;

    /**
     * eventDatetime
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="event_datetime")
     */
    protected $eventDatetime;

    /**
     * Event data
     *
     * @var string
     *
     * @ORM\Column(type="string", name="event_data", length=255, nullable=true)
     */
    protected $eventData;

    /**
     * Event history type
     *
     * @var EventHistory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType")
     * @ORM\JoinColumn(name="event_history_type_id", referencedColumnName="id", nullable=false)
     */
    protected $eventHistoryType;

    /**
     * User
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * Event description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="event_description", length=255, nullable=true)
     */
    protected $eventDescription;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * @return string
     */
    public function getChangeMadeBy()
    {
        return $this->changeMadeBy;
    }

    /**
     * @return \DateTime
     */
    public function getEventDatetime()
    {
        return $this->eventDatetime;
    }

    /**
     * @return string
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * @return EventHistory
     */
    public function getEventHistoryType()
    {
        return $this->eventHistoryType;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getEventDescription()
    {
        return $this->eventDescription;
    }
}
