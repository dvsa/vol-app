<?php

namespace Dvsa\Olcs\Api\Entity\EventHistory;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventHistory Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="event_history",
 *    indexes={
 *        @ORM\Index(name="ix_event_history_account_id", columns={"account_id"}),
 *        @ORM\Index(name="ix_event_history_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_event_history_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_event_history_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_event_history_entity_pk", columns={"entity_pk"}),
 *        @ORM\Index(name="ix_event_history_entity_type", columns={"entity_type"}),
 *        @ORM\Index(name="ix_event_history_event_history_type_id", columns={"event_history_type_id"}),
 *        @ORM\Index(name="ix_event_history_irhp_application_id", columns={"irhp_application_id"}),
 *        @ORM\Index(name="ix_event_history_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_event_history_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_event_history_task_id", columns={"task_id"}),
 *        @ORM\Index(name="ix_event_history_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_event_history_user_id", columns={"user_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_event_history_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
abstract class AbstractEventHistory implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;

    /**
     * Account
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=true)
     */
    protected $account;

    /**
     * Application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true)
     */
    protected $application;

    /**
     * Bus reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", fetch="LAZY")
     * @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id", nullable=true)
     */
    protected $busReg;

    /**
     * Case
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Cases
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Cases", fetch="LAZY")
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", nullable=true)
     */
    protected $case;

    /**
     * Change made by
     *
     * @var string
     *
     * @ORM\Column(type="string", name="change_made_by", length=80, nullable=true)
     */
    protected $changeMadeBy;

    /**
     * Entity pk
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="entity_pk", nullable=true)
     */
    protected $entityPk;

    /**
     * Entity type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="entity_type", length=45, nullable=true)
     */
    protected $entityType;

    /**
     * Entity version
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="entity_version", nullable=true)
     */
    protected $entityVersion;

    /**
     * Event data
     *
     * @var string
     *
     * @ORM\Column(type="string", name="event_data", length=255, nullable=true)
     */
    protected $eventData;

    /**
     * Event datetime
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="event_datetime", nullable=false)
     */
    protected $eventDatetime;

    /**
     * Event history type
     *
     * @var \Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType", fetch="LAZY")
     * @ORM\JoinColumn(name="event_history_type_id", referencedColumnName="id", nullable=false)
     */
    protected $eventHistoryType;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Irhp application
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpApplication", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_application_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpApplication;

    /**
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=true)
     */
    protected $licence;

    /**
     * Member of organisation
     *
     * @var string
     *
     * @ORM\Column(type="string", name="member_of_organisation", length=160, nullable=true)
     */
    protected $memberOfOrganisation;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Olbs type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=45, nullable=true)
     */
    protected $olbsType;

    /**
     * Organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation", fetch="LAZY")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $organisation;

    /**
     * Task
     *
     * @var \Dvsa\Olcs\Api\Entity\Task\Task
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", fetch="LAZY")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=true)
     */
    protected $task;

    /**
     * Transport manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManager;

    /**
     * User
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * Set the account
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $account entity being set as the value
     *
     * @return EventHistory
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get the account
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application entity being set as the value
     *
     * @return EventHistory
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg entity being set as the value
     *
     * @return EventHistory
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;

        return $this;
    }

    /**
     * Get the bus reg
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case entity being set as the value
     *
     * @return EventHistory
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the change made by
     *
     * @param string $changeMadeBy new value being set
     *
     * @return EventHistory
     */
    public function setChangeMadeBy($changeMadeBy)
    {
        $this->changeMadeBy = $changeMadeBy;

        return $this;
    }

    /**
     * Get the change made by
     *
     * @return string
     */
    public function getChangeMadeBy()
    {
        return $this->changeMadeBy;
    }

    /**
     * Set the entity pk
     *
     * @param int $entityPk new value being set
     *
     * @return EventHistory
     */
    public function setEntityPk($entityPk)
    {
        $this->entityPk = $entityPk;

        return $this;
    }

    /**
     * Get the entity pk
     *
     * @return int
     */
    public function getEntityPk()
    {
        return $this->entityPk;
    }

    /**
     * Set the entity type
     *
     * @param string $entityType new value being set
     *
     * @return EventHistory
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get the entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Set the entity version
     *
     * @param int $entityVersion new value being set
     *
     * @return EventHistory
     */
    public function setEntityVersion($entityVersion)
    {
        $this->entityVersion = $entityVersion;

        return $this;
    }

    /**
     * Get the entity version
     *
     * @return int
     */
    public function getEntityVersion()
    {
        return $this->entityVersion;
    }

    /**
     * Set the event data
     *
     * @param string $eventData new value being set
     *
     * @return EventHistory
     */
    public function setEventData($eventData)
    {
        $this->eventData = $eventData;

        return $this;
    }

    /**
     * Get the event data
     *
     * @return string
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * Set the event datetime
     *
     * @param \DateTime $eventDatetime new value being set
     *
     * @return EventHistory
     */
    public function setEventDatetime($eventDatetime)
    {
        $this->eventDatetime = $eventDatetime;

        return $this;
    }

    /**
     * Get the event datetime
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getEventDatetime($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->eventDatetime);
        }

        return $this->eventDatetime;
    }

    /**
     * Set the event history type
     *
     * @param \Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType $eventHistoryType entity being set as the value
     *
     * @return EventHistory
     */
    public function setEventHistoryType($eventHistoryType)
    {
        $this->eventHistoryType = $eventHistoryType;

        return $this;
    }

    /**
     * Get the event history type
     *
     * @return \Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType
     */
    public function getEventHistoryType()
    {
        return $this->eventHistoryType;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return EventHistory
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the irhp application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication entity being set as the value
     *
     * @return EventHistory
     */
    public function setIrhpApplication($irhpApplication)
    {
        $this->irhpApplication = $irhpApplication;

        return $this;
    }

    /**
     * Get the irhp application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence entity being set as the value
     *
     * @return EventHistory
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the member of organisation
     *
     * @param string $memberOfOrganisation new value being set
     *
     * @return EventHistory
     */
    public function setMemberOfOrganisation($memberOfOrganisation)
    {
        $this->memberOfOrganisation = $memberOfOrganisation;

        return $this;
    }

    /**
     * Get the member of organisation
     *
     * @return string
     */
    public function getMemberOfOrganisation()
    {
        return $this->memberOfOrganisation;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return EventHistory
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return EventHistory
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string
     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation entity being set as the value
     *
     * @return EventHistory
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get the organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set the task
     *
     * @param \Dvsa\Olcs\Api\Entity\Task\Task $task entity being set as the value
     *
     * @return EventHistory
     */
    public function setTask($task)
    {
        $this->task = $task;

        return $this;
    }

    /**
     * Get the task
     *
     * @return \Dvsa\Olcs\Api\Entity\Task\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager entity being set as the value
     *
     * @return EventHistory
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $user entity being set as the value
     *
     * @return EventHistory
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
