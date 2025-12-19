<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Bus;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractLocalAuthority Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="local_authority",
 *    indexes={
 *        @ORM\Index(name="ix_local_authority_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_local_authority_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_local_authority_traffic_area_id", columns={"traffic_area_id"})
 *    }
 * )
 */
abstract class AbstractLocalAuthority implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * @ORM\Column(type="string", name="description", length=255, nullable=false)
     */
    protected $description = '';

    /**
     * Email address
     *
     * @var string
     *
     * @ORM\Column(type="string", name="email_address", length=255, nullable=true)
     */
    protected $emailAddress;

    /**
     * Authorities name in TransXChange.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="txc_name", length=255, nullable=true)
     */
    protected $txcName;

    /**
     * GB National Public Transport Access Nodes code for authority
     *
     * @var string
     *
     * @ORM\Column(type="string", name="naptan_code", length=3, nullable=true)
     */
    protected $naptanCode;

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
     * BusRegs
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", mappedBy="localAuthoritys", fetch="LAZY")
     */
    protected $busRegs;

    /**
     * Users
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\User\User", mappedBy="localAuthority")
     */
    protected $users;

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
        $this->busRegs = new ArrayCollection();
        $this->users = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return LocalAuthority
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
     * @return LocalAuthority
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
     * @return LocalAuthority
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
     * @return LocalAuthority
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
     * @return LocalAuthority
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
     * Set the email address
     *
     * @param string $emailAddress new value being set
     *
     * @return LocalAuthority
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Get the email address
     *
     * @return string     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Set the txc name
     *
     * @param string $txcName new value being set
     *
     * @return LocalAuthority
     */
    public function setTxcName($txcName)
    {
        $this->txcName = $txcName;

        return $this;
    }

    /**
     * Get the txc name
     *
     * @return string     */
    public function getTxcName()
    {
        return $this->txcName;
    }

    /**
     * Set the naptan code
     *
     * @param string $naptanCode new value being set
     *
     * @return LocalAuthority
     */
    public function setNaptanCode($naptanCode)
    {
        $this->naptanCode = $naptanCode;

        return $this;
    }

    /**
     * Get the naptan code
     *
     * @return string     */
    public function getNaptanCode()
    {
        return $this->naptanCode;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LocalAuthority
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
     * Set the bus regs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $busRegs collection being set as the value
     *
     * @return LocalAuthority
     */
    public function setBusRegs($busRegs)
    {
        $this->busRegs = $busRegs;

        return $this;
    }

    /**
     * Get the bus regs
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBusRegs()
    {
        return $this->busRegs;
    }

    /**
     * Add a bus regs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $busRegs collection being added
     *
     * @return LocalAuthority
     */
    public function addBusRegs($busRegs)
    {
        if ($busRegs instanceof ArrayCollection) {
            $this->busRegs = new ArrayCollection(
                array_merge(
                    $this->busRegs->toArray(),
                    $busRegs->toArray()
                )
            );
        } elseif (!$this->busRegs->contains($busRegs)) {
            $this->busRegs->add($busRegs);
        }

        return $this;
    }

    /**
     * Remove a bus regs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $busRegs collection being removed
     *
     * @return LocalAuthority
     */
    public function removeBusRegs($busRegs)
    {
        if ($this->busRegs->contains($busRegs)) {
            $this->busRegs->removeElement($busRegs);
        }

        return $this;
    }

    /**
     * Set the users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $users collection being set as the value
     *
     * @return LocalAuthority
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get the users
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add a users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $users collection being added
     *
     * @return LocalAuthority
     */
    public function addUsers($users)
    {
        if ($users instanceof ArrayCollection) {
            $this->users = new ArrayCollection(
                array_merge(
                    $this->users->toArray(),
                    $users->toArray()
                )
            );
        } elseif (!$this->users->contains($users)) {
            $this->users->add($users);
        }

        return $this;
    }

    /**
     * Remove a users
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $users collection being removed
     *
     * @return LocalAuthority
     */
    public function removeUsers($users)
    {
        if ($this->users->contains($users)) {
            $this->users->removeElement($users);
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