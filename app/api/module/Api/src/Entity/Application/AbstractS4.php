<?php

namespace Dvsa\Olcs\Api\Entity\Application;

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
 * S4 Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="s4",
 *    indexes={
 *        @ORM\Index(name="ix_s4_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_s4_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_s4_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_s4_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_s4_outcome", columns={"outcome"})
 *    }
 * )
 */
abstract class AbstractS4 implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Agreed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="agreed_date", nullable=true)
     */
    protected $agreedDate;

    /**
     * Application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\Application",
     *     fetch="LAZY",
     *     inversedBy="s4s"
     * )
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true)
     */
    protected $application;

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
     * Is true s4
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_true_s4", nullable=false, options={"default": 0})
     */
    protected $isTrueS4 = 0;

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
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=false)
     */
    protected $licence;

    /**
     * Outcome
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="outcome", referencedColumnName="id", nullable=true)
     */
    protected $outcome;

    /**
     * Received date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="received_date", nullable=false)
     */
    protected $receivedDate;

    /**
     * Surrender licence
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="surrender_licence", nullable=false, options={"default": 0})
     */
    protected $surrenderLicence = 0;

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
     * Aoc
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre",
     *     mappedBy="s4"
     * )
     */
    protected $aocs;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->aocs = new ArrayCollection();
    }

    /**
     * Set the agreed date
     *
     * @param \DateTime $agreedDate new value being set
     *
     * @return S4
     */
    public function setAgreedDate($agreedDate)
    {
        $this->agreedDate = $agreedDate;

        return $this;
    }

    /**
     * Get the agreed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getAgreedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->agreedDate);
        }

        return $this->agreedDate;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application entity being set as the value
     *
     * @return S4
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return S4
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get the created by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return S4
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
     * Set the is true s4
     *
     * @param string $isTrueS4 new value being set
     *
     * @return S4
     */
    public function setIsTrueS4($isTrueS4)
    {
        $this->isTrueS4 = $isTrueS4;

        return $this;
    }

    /**
     * Get the is true s4
     *
     * @return string
     */
    public function getIsTrueS4()
    {
        return $this->isTrueS4;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return S4
     */
    public function setLastModifiedBy($lastModifiedBy)
    {
        $this->lastModifiedBy = $lastModifiedBy;

        return $this;
    }

    /**
     * Get the last modified by
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence entity being set as the value
     *
     * @return S4
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
     * Set the outcome
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $outcome entity being set as the value
     *
     * @return S4
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;

        return $this;
    }

    /**
     * Get the outcome
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * Set the received date
     *
     * @param \DateTime $receivedDate new value being set
     *
     * @return S4
     */
    public function setReceivedDate($receivedDate)
    {
        $this->receivedDate = $receivedDate;

        return $this;
    }

    /**
     * Get the received date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getReceivedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->receivedDate);
        }

        return $this->receivedDate;
    }

    /**
     * Set the surrender licence
     *
     * @param string $surrenderLicence new value being set
     *
     * @return S4
     */
    public function setSurrenderLicence($surrenderLicence)
    {
        $this->surrenderLicence = $surrenderLicence;

        return $this;
    }

    /**
     * Get the surrender licence
     *
     * @return string
     */
    public function getSurrenderLicence()
    {
        return $this->surrenderLicence;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return S4
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the version
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the aoc
     *
     * @param ArrayCollection $aocs collection being set as the value
     *
     * @return S4
     */
    public function setAocs($aocs)
    {
        $this->aocs = $aocs;

        return $this;
    }

    /**
     * Get the aocs
     *
     * @return ArrayCollection
     */
    public function getAocs()
    {
        return $this->aocs;
    }

    /**
     * Add a aocs
     *
     * @param ArrayCollection|mixed $aocs collection being added
     *
     * @return S4
     */
    public function addAocs($aocs)
    {
        if ($aocs instanceof ArrayCollection) {
            $this->aocs = new ArrayCollection(
                array_merge(
                    $this->aocs->toArray(),
                    $aocs->toArray()
                )
            );
        } elseif (!$this->aocs->contains($aocs)) {
            $this->aocs->add($aocs);
        }

        return $this;
    }

    /**
     * Remove a aocs
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $aocs collection being removed
     *
     * @return S4
     */
    public function removeAocs($aocs)
    {
        if ($this->aocs->contains($aocs)) {
            $this->aocs->removeElement($aocs);
        }

        return $this;
    }
}
