<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\User;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractTeam Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="team",
 *    indexes={
 *        @ORM\Index(name="ix_team_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_team_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_team_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="uk_team_name", columns={"name"}),
 *        @ORM\Index(name="uk_team_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_team_name", columns={"name"}),
 *        @ORM\UniqueConstraint(name="uk_team_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractTeam implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Primary key.  Auto incremented if numeric.
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * Foreign Key to traffic_area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id", nullable=true)
     */
    protected $trafficArea;

    /**
     * Created by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * Last modified by
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="last_modified_by", referencedColumnName="id", nullable=true)
     * @Gedmo\Blameable(on="update")
     */
    protected $lastModifiedBy;

    /**
     * Description
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=true)
     */
    protected $description;

    /**
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=70, nullable=false)
     */
    protected $name = '';

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=false, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Used to map FKs during ETL. Can be dropped safely when OLBS decommissioned
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Tasks
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Task\Task", mappedBy="assignedToTeam")
     */
    protected $tasks;

    /**
     * TaskAllocationRules
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule", mappedBy="team")
     */
    protected $taskAllocationRules;

    /**
     * TeamPrinters
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter", mappedBy="team", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $teamPrinters;

    /**
     * Initialise the collections
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise collections
     */
    public function initCollections(): void
    {
        $this->tasks = new ArrayCollection();
        $this->taskAllocationRules = new ArrayCollection();
        $this->teamPrinters = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Team
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea new value being set
     *
     * @return Team
     */
    public function setTrafficArea($trafficArea)
    {
        $this->trafficArea = $trafficArea;

        return $this;
    }

    /**
     * Get the traffic area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Team
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return Team
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return Team
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return Team
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Team
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Team
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tasks collection being set as the value
     *
     * @return Team
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;

        return $this;
    }

    /**
     * Get the tasks
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * Add a tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $tasks collection being added
     *
     * @return Team
     */
    public function addTasks($tasks)
    {
        if ($tasks instanceof ArrayCollection) {
            $this->tasks = new ArrayCollection(
                array_merge(
                    $this->tasks->toArray(),
                    $tasks->toArray()
                )
            );
        } elseif (!$this->tasks->contains($tasks)) {
            $this->tasks->add($tasks);
        }

        return $this;
    }

    /**
     * Remove a tasks
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $tasks collection being removed
     *
     * @return Team
     */
    public function removeTasks($tasks)
    {
        if ($this->tasks->contains($tasks)) {
            $this->tasks->removeElement($tasks);
        }

        return $this;
    }

    /**
     * Set the task allocation rules
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $taskAllocationRules collection being set as the value
     *
     * @return Team
     */
    public function setTaskAllocationRules($taskAllocationRules)
    {
        $this->taskAllocationRules = $taskAllocationRules;

        return $this;
    }

    /**
     * Get the task allocation rules
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTaskAllocationRules()
    {
        return $this->taskAllocationRules;
    }

    /**
     * Add a task allocation rules
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $taskAllocationRules collection being added
     *
     * @return Team
     */
    public function addTaskAllocationRules($taskAllocationRules)
    {
        if ($taskAllocationRules instanceof ArrayCollection) {
            $this->taskAllocationRules = new ArrayCollection(
                array_merge(
                    $this->taskAllocationRules->toArray(),
                    $taskAllocationRules->toArray()
                )
            );
        } elseif (!$this->taskAllocationRules->contains($taskAllocationRules)) {
            $this->taskAllocationRules->add($taskAllocationRules);
        }

        return $this;
    }

    /**
     * Remove a task allocation rules
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $taskAllocationRules collection being removed
     *
     * @return Team
     */
    public function removeTaskAllocationRules($taskAllocationRules)
    {
        if ($this->taskAllocationRules->contains($taskAllocationRules)) {
            $this->taskAllocationRules->removeElement($taskAllocationRules);
        }

        return $this;
    }

    /**
     * Set the team printers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $teamPrinters collection being set as the value
     *
     * @return Team
     */
    public function setTeamPrinters($teamPrinters)
    {
        $this->teamPrinters = $teamPrinters;

        return $this;
    }

    /**
     * Get the team printers
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTeamPrinters()
    {
        return $this->teamPrinters;
    }

    /**
     * Add a team printers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $teamPrinters collection being added
     *
     * @return Team
     */
    public function addTeamPrinters($teamPrinters)
    {
        if ($teamPrinters instanceof ArrayCollection) {
            $this->teamPrinters = new ArrayCollection(
                array_merge(
                    $this->teamPrinters->toArray(),
                    $teamPrinters->toArray()
                )
            );
        } elseif (!$this->teamPrinters->contains($teamPrinters)) {
            $this->teamPrinters->add($teamPrinters);
        }

        return $this;
    }

    /**
     * Remove a team printers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $teamPrinters collection being removed
     *
     * @return Team
     */
    public function removeTeamPrinters($teamPrinters)
    {
        if ($this->teamPrinters->contains($teamPrinters)) {
            $this->teamPrinters->removeElement($teamPrinters);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}