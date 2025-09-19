<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\TrafficArea;

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
 * AbstractTrafficArea Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="traffic_area",
 *    indexes={
 *        @ORM\Index(name="ix_traffic_area_contact_details_id", columns={"contact_details_id"}),
 *        @ORM\Index(name="ix_traffic_area_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_traffic_area_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractTrafficArea implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Primary key
     *
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="id", length=1, nullable=false)
     */
    protected $id = '';

    /**
     * Foreign Key to contact_details
     *
     * @var \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails", fetch="LAZY")
     * @ORM\JoinColumn(name="contact_details_id", referencedColumnName="id")
     */
    protected $contactDetails;

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
     * e.g. North Eastern, Wales
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=70, nullable=false)
     */
    protected $name = '';

    /**
     * TransXChange name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="txc_name", length=70, nullable=true)
     */
    protected $txcName;

    /**
     * Is in Scotland.  Affects some business logic with different Scottish regulations
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_scotland", nullable=false, options={"default": 0})
     */
    protected $isScotland = 0;

    /**
     * Is in Wales
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_wales", nullable=false, options={"default": 0})
     */
    protected $isWales = 0;

    /**
     * Is in Northern Ireland
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_ni", nullable=false, options={"default": 0})
     */
    protected $isNi = 0;

    /**
     * Is in England
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_england", nullable=false, options={"default": 0})
     */
    protected $isEngland = 0;

    /**
     * used for fee payments
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sales_person_reference", length=70, nullable=false)
     */
    protected $salesPersonReference = '';

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
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", mappedBy="trafficAreas", fetch="LAZY")
     */
    protected $busRegs;

    /**
     * Recipients
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\Publication\Recipient", mappedBy="trafficAreas", fetch="LAZY")
     */
    protected $recipients;

    /**
     * Documents
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", mappedBy="trafficArea")
     */
    protected $documents;

    /**
     * TrafficAreaEnforcementAreas
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficAreaEnforcementArea", mappedBy="trafficArea")
     */
    protected $trafficAreaEnforcementAreas;

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
        $this->recipients = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->trafficAreaEnforcementAreas = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param string $id new value being set
     *
     * @return TrafficArea
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id
     *
     * @return string     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the contact details
     *
     * @param \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails $contactDetails new value being set
     *
     * @return TrafficArea
     */
    public function setContactDetails($contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * Get the contact details
     *
     * @return \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return TrafficArea
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
     * @return TrafficArea
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
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return TrafficArea
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
     * Set the txc name
     *
     * @param string $txcName new value being set
     *
     * @return TrafficArea
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
     * Set the is scotland
     *
     * @param bool $isScotland new value being set
     *
     * @return TrafficArea
     */
    public function setIsScotland($isScotland)
    {
        $this->isScotland = $isScotland;

        return $this;
    }

    /**
     * Get the is scotland
     *
     * @return bool     */
    public function getIsScotland()
    {
        return $this->isScotland;
    }

    /**
     * Set the is wales
     *
     * @param bool $isWales new value being set
     *
     * @return TrafficArea
     */
    public function setIsWales($isWales)
    {
        $this->isWales = $isWales;

        return $this;
    }

    /**
     * Get the is wales
     *
     * @return bool     */
    public function getIsWales()
    {
        return $this->isWales;
    }

    /**
     * Set the is ni
     *
     * @param bool $isNi new value being set
     *
     * @return TrafficArea
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return bool     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the is england
     *
     * @param bool $isEngland new value being set
     *
     * @return TrafficArea
     */
    public function setIsEngland($isEngland)
    {
        $this->isEngland = $isEngland;

        return $this;
    }

    /**
     * Get the is england
     *
     * @return bool     */
    public function getIsEngland()
    {
        return $this->isEngland;
    }

    /**
     * Set the sales person reference
     *
     * @param string $salesPersonReference new value being set
     *
     * @return TrafficArea
     */
    public function setSalesPersonReference($salesPersonReference)
    {
        $this->salesPersonReference = $salesPersonReference;

        return $this;
    }

    /**
     * Get the sales person reference
     *
     * @return string     */
    public function getSalesPersonReference()
    {
        return $this->salesPersonReference;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TrafficArea
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
     * @return TrafficArea
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
     * @return TrafficArea
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
     * @return TrafficArea
     */
    public function removeBusRegs($busRegs)
    {
        if ($this->busRegs->contains($busRegs)) {
            $this->busRegs->removeElement($busRegs);
        }

        return $this;
    }

    /**
     * Set the recipients
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $recipients collection being set as the value
     *
     * @return TrafficArea
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Get the recipients
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Add a recipients
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $recipients collection being added
     *
     * @return TrafficArea
     */
    public function addRecipients($recipients)
    {
        if ($recipients instanceof ArrayCollection) {
            $this->recipients = new ArrayCollection(
                array_merge(
                    $this->recipients->toArray(),
                    $recipients->toArray()
                )
            );
        } elseif (!$this->recipients->contains($recipients)) {
            $this->recipients->add($recipients);
        }

        return $this;
    }

    /**
     * Remove a recipients
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $recipients collection being removed
     *
     * @return TrafficArea
     */
    public function removeRecipients($recipients)
    {
        if ($this->recipients->contains($recipients)) {
            $this->recipients->removeElement($recipients);
        }

        return $this;
    }

    /**
     * Set the documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being set as the value
     *
     * @return TrafficArea
     */
    public function setDocuments($documents)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get the documents
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Add a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $documents collection being added
     *
     * @return TrafficArea
     */
    public function addDocuments($documents)
    {
        if ($documents instanceof ArrayCollection) {
            $this->documents = new ArrayCollection(
                array_merge(
                    $this->documents->toArray(),
                    $documents->toArray()
                )
            );
        } elseif (!$this->documents->contains($documents)) {
            $this->documents->add($documents);
        }

        return $this;
    }

    /**
     * Remove a documents
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $documents collection being removed
     *
     * @return TrafficArea
     */
    public function removeDocuments($documents)
    {
        if ($this->documents->contains($documents)) {
            $this->documents->removeElement($documents);
        }

        return $this;
    }

    /**
     * Set the traffic area enforcement areas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $trafficAreaEnforcementAreas collection being set as the value
     *
     * @return TrafficArea
     */
    public function setTrafficAreaEnforcementAreas($trafficAreaEnforcementAreas)
    {
        $this->trafficAreaEnforcementAreas = $trafficAreaEnforcementAreas;

        return $this;
    }

    /**
     * Get the traffic area enforcement areas
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTrafficAreaEnforcementAreas()
    {
        return $this->trafficAreaEnforcementAreas;
    }

    /**
     * Add a traffic area enforcement areas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $trafficAreaEnforcementAreas collection being added
     *
     * @return TrafficArea
     */
    public function addTrafficAreaEnforcementAreas($trafficAreaEnforcementAreas)
    {
        if ($trafficAreaEnforcementAreas instanceof ArrayCollection) {
            $this->trafficAreaEnforcementAreas = new ArrayCollection(
                array_merge(
                    $this->trafficAreaEnforcementAreas->toArray(),
                    $trafficAreaEnforcementAreas->toArray()
                )
            );
        } elseif (!$this->trafficAreaEnforcementAreas->contains($trafficAreaEnforcementAreas)) {
            $this->trafficAreaEnforcementAreas->add($trafficAreaEnforcementAreas);
        }

        return $this;
    }

    /**
     * Remove a traffic area enforcement areas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $trafficAreaEnforcementAreas collection being removed
     *
     * @return TrafficArea
     */
    public function removeTrafficAreaEnforcementAreas($trafficAreaEnforcementAreas)
    {
        if ($this->trafficAreaEnforcementAreas->contains($trafficAreaEnforcementAreas)) {
            $this->trafficAreaEnforcementAreas->removeElement($trafficAreaEnforcementAreas);
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