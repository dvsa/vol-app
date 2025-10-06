<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Publication;

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
 * AbstractRecipient Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="recipient",
 *    indexes={
 *        @ORM\Index(name="ix_recipient_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_recipient_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_recipient_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_recipient_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractRecipient implements BundleSerializableInterface, JsonSerializable
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
     * Recipient registered for AD, Applications and Decisions, publications
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="send_app_decision", nullable=false, options={"default": 0})
     */
    protected $sendAppDecision = 0;

    /**
     * Recipient registered for NP, Notices and Procedures, publications
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="send_notices_procs", nullable=false, options={"default": 0})
     */
    protected $sendNoticesProcs = 0;

    /**
     * Recipient receives extra sensitive info. DOBs for people in publication.
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_police", nullable=false, options={"default": 0})
     */
    protected $isPolice = 0;

    /**
     * Is objector or representor
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_objector", nullable=false, options={"default": 0})
     */
    protected $isObjector = 0;

    /**
     * Contact name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="contact_name", length=100, nullable=true)
     */
    protected $contactName;

    /**
     * Email address
     *
     * @var string
     *
     * @ORM\Column(type="string", name="email_address", length=255, nullable=true)
     */
    protected $emailAddress;

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
     * TrafficAreas
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", inversedBy="recipients", fetch="LAZY")
     * @ORM\JoinTable(name="recipient_traffic_area",
     *     joinColumns={
     *         @ORM\JoinColumn(name="recipient_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $trafficAreas;

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
        $this->trafficAreas = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Recipient
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Recipient
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
     * @return Recipient
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
     * Set the send app decision
     *
     * @param string $sendAppDecision new value being set
     *
     * @return Recipient
     */
    public function setSendAppDecision($sendAppDecision)
    {
        $this->sendAppDecision = $sendAppDecision;

        return $this;
    }

    /**
     * Get the send app decision
     *
     * @return string     */
    public function getSendAppDecision()
    {
        return $this->sendAppDecision;
    }

    /**
     * Set the send notices procs
     *
     * @param string $sendNoticesProcs new value being set
     *
     * @return Recipient
     */
    public function setSendNoticesProcs($sendNoticesProcs)
    {
        $this->sendNoticesProcs = $sendNoticesProcs;

        return $this;
    }

    /**
     * Get the send notices procs
     *
     * @return string     */
    public function getSendNoticesProcs()
    {
        return $this->sendNoticesProcs;
    }

    /**
     * Set the is police
     *
     * @param string $isPolice new value being set
     *
     * @return Recipient
     */
    public function setIsPolice($isPolice)
    {
        $this->isPolice = $isPolice;

        return $this;
    }

    /**
     * Get the is police
     *
     * @return string     */
    public function getIsPolice()
    {
        return $this->isPolice;
    }

    /**
     * Set the is objector
     *
     * @param string $isObjector new value being set
     *
     * @return Recipient
     */
    public function setIsObjector($isObjector)
    {
        $this->isObjector = $isObjector;

        return $this;
    }

    /**
     * Get the is objector
     *
     * @return string     */
    public function getIsObjector()
    {
        return $this->isObjector;
    }

    /**
     * Set the contact name
     *
     * @param string $contactName new value being set
     *
     * @return Recipient
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Get the contact name
     *
     * @return string     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set the email address
     *
     * @param string $emailAddress new value being set
     *
     * @return Recipient
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Recipient
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
     * @return Recipient
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
     * Set the traffic areas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $trafficAreas collection being set as the value
     *
     * @return Recipient
     */
    public function setTrafficAreas($trafficAreas)
    {
        $this->trafficAreas = $trafficAreas;

        return $this;
    }

    /**
     * Get the traffic areas
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTrafficAreas()
    {
        return $this->trafficAreas;
    }

    /**
     * Add a traffic areas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $trafficAreas collection being added
     *
     * @return Recipient
     */
    public function addTrafficAreas($trafficAreas)
    {
        if ($trafficAreas instanceof ArrayCollection) {
            $this->trafficAreas = new ArrayCollection(
                array_merge(
                    $this->trafficAreas->toArray(),
                    $trafficAreas->toArray()
                )
            );
        } elseif (!$this->trafficAreas->contains($trafficAreas)) {
            $this->trafficAreas->add($trafficAreas);
        }

        return $this;
    }

    /**
     * Remove a traffic areas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $trafficAreas collection being removed
     *
     * @return Recipient
     */
    public function removeTrafficAreas($trafficAreas)
    {
        if ($this->trafficAreas->contains($trafficAreas)) {
            $this->trafficAreas->removeElement($trafficAreas);
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