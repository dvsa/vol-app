<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Tm;

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
 * AbstractTransportManagerLicence Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="transport_manager_licence",
 *    indexes={
 *        @ORM\Index(name="ix_transport_manager_licence_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_transport_manager_licence_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_transport_manager_licence_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_transport_manager_licence_tm_type", columns={"tm_type"}),
 *        @ORM\Index(name="ix_transport_manager_licence_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="uk_transport_manager_licence_olbs_key", columns={"olbs_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_transport_manager_licence_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
abstract class AbstractTransportManagerLicence implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to transport_manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id")
     */
    protected $transportManager;

    /**
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
     */
    protected $licence;

    /**
     * TmType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="tm_type", referencedColumnName="id", nullable=true)
     */
    protected $tmType;

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
     * isOwner
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="is_owner", nullable=true)
     */
    protected $isOwner;

    /**
     * Hours mon
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_mon", nullable=true)
     */
    protected $hoursMon;

    /**
     * Hours tue
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_tue", nullable=true)
     */
    protected $hoursTue;

    /**
     * Hours wed
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_wed", nullable=true)
     */
    protected $hoursWed;

    /**
     * Hours thu
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_thu", nullable=true)
     */
    protected $hoursThu;

    /**
     * Hours fri
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_fri", nullable=true)
     */
    protected $hoursFri;

    /**
     * Hours sat
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_sat", nullable=true)
     */
    protected $hoursSat;

    /**
     * Hours sun
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="hours_sun", nullable=true)
     */
    protected $hoursSun;

    /**
     * Additional information
     *
     * @var string
     *
     * @ORM\Column(type="string", name="additional_information", length=4000, nullable=true)
     */
    protected $additionalInformation;

    /**
     * Last tm letter date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="last_tm_letter_date", nullable=true)
     */
    protected $lastTmLetterDate;

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
     * OtherLicences
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence", mappedBy="transportManagerLicence")
     */
    protected $otherLicences;

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
        $this->otherLicences = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return TransportManagerLicence
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
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return TransportManagerLicence
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return TransportManagerLicence
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the tm type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $tmType new value being set
     *
     * @return TransportManagerLicence
     */
    public function setTmType($tmType)
    {
        $this->tmType = $tmType;

        return $this;
    }

    /**
     * Get the tm type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getTmType()
    {
        return $this->tmType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return TransportManagerLicence
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
     * @return TransportManagerLicence
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
     * Set the is owner
     *
     * @param string $isOwner new value being set
     *
     * @return TransportManagerLicence
     */
    public function setIsOwner($isOwner)
    {
        $this->isOwner = $isOwner;

        return $this;
    }

    /**
     * Get the is owner
     *
     * @return string     */
    public function getIsOwner()
    {
        return $this->isOwner;
    }

    /**
     * Set the hours mon
     *
     * @param string $hoursMon new value being set
     *
     * @return TransportManagerLicence
     */
    public function setHoursMon($hoursMon)
    {
        $this->hoursMon = $hoursMon;

        return $this;
    }

    /**
     * Get the hours mon
     *
     * @return string     */
    public function getHoursMon()
    {
        return $this->hoursMon;
    }

    /**
     * Set the hours tue
     *
     * @param string $hoursTue new value being set
     *
     * @return TransportManagerLicence
     */
    public function setHoursTue($hoursTue)
    {
        $this->hoursTue = $hoursTue;

        return $this;
    }

    /**
     * Get the hours tue
     *
     * @return string     */
    public function getHoursTue()
    {
        return $this->hoursTue;
    }

    /**
     * Set the hours wed
     *
     * @param string $hoursWed new value being set
     *
     * @return TransportManagerLicence
     */
    public function setHoursWed($hoursWed)
    {
        $this->hoursWed = $hoursWed;

        return $this;
    }

    /**
     * Get the hours wed
     *
     * @return string     */
    public function getHoursWed()
    {
        return $this->hoursWed;
    }

    /**
     * Set the hours thu
     *
     * @param string $hoursThu new value being set
     *
     * @return TransportManagerLicence
     */
    public function setHoursThu($hoursThu)
    {
        $this->hoursThu = $hoursThu;

        return $this;
    }

    /**
     * Get the hours thu
     *
     * @return string     */
    public function getHoursThu()
    {
        return $this->hoursThu;
    }

    /**
     * Set the hours fri
     *
     * @param string $hoursFri new value being set
     *
     * @return TransportManagerLicence
     */
    public function setHoursFri($hoursFri)
    {
        $this->hoursFri = $hoursFri;

        return $this;
    }

    /**
     * Get the hours fri
     *
     * @return string     */
    public function getHoursFri()
    {
        return $this->hoursFri;
    }

    /**
     * Set the hours sat
     *
     * @param string $hoursSat new value being set
     *
     * @return TransportManagerLicence
     */
    public function setHoursSat($hoursSat)
    {
        $this->hoursSat = $hoursSat;

        return $this;
    }

    /**
     * Get the hours sat
     *
     * @return string     */
    public function getHoursSat()
    {
        return $this->hoursSat;
    }

    /**
     * Set the hours sun
     *
     * @param string $hoursSun new value being set
     *
     * @return TransportManagerLicence
     */
    public function setHoursSun($hoursSun)
    {
        $this->hoursSun = $hoursSun;

        return $this;
    }

    /**
     * Get the hours sun
     *
     * @return string     */
    public function getHoursSun()
    {
        return $this->hoursSun;
    }

    /**
     * Set the additional information
     *
     * @param string $additionalInformation new value being set
     *
     * @return TransportManagerLicence
     */
    public function setAdditionalInformation($additionalInformation)
    {
        $this->additionalInformation = $additionalInformation;

        return $this;
    }

    /**
     * Get the additional information
     *
     * @return string     */
    public function getAdditionalInformation()
    {
        return $this->additionalInformation;
    }

    /**
     * Set the last tm letter date
     *
     * @param \DateTime $lastTmLetterDate new value being set
     *
     * @return TransportManagerLicence
     */
    public function setLastTmLetterDate($lastTmLetterDate)
    {
        $this->lastTmLetterDate = $lastTmLetterDate;

        return $this;
    }

    /**
     * Get the last tm letter date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getLastTmLetterDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->lastTmLetterDate);
        }

        return $this->lastTmLetterDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TransportManagerLicence
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
     * @return TransportManagerLicence
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
     * Set the other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherLicences collection being set as the value
     *
     * @return TransportManagerLicence
     */
    public function setOtherLicences($otherLicences)
    {
        $this->otherLicences = $otherLicences;

        return $this;
    }

    /**
     * Get the other licences
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getOtherLicences()
    {
        return $this->otherLicences;
    }

    /**
     * Add a other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $otherLicences collection being added
     *
     * @return TransportManagerLicence
     */
    public function addOtherLicences($otherLicences)
    {
        if ($otherLicences instanceof ArrayCollection) {
            $this->otherLicences = new ArrayCollection(
                array_merge(
                    $this->otherLicences->toArray(),
                    $otherLicences->toArray()
                )
            );
        } elseif (!$this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->add($otherLicences);
        }

        return $this;
    }

    /**
     * Remove a other licences
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $otherLicences collection being removed
     *
     * @return TransportManagerLicence
     */
    public function removeOtherLicences($otherLicences)
    {
        if ($this->otherLicences->contains($otherLicences)) {
            $this->otherLicences->removeElement($otherLicences);
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
