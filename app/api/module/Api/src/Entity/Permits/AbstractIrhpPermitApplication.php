<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Permits;

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
 * AbstractIrhpPermitApplication Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="irhp_permit_application",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permit_application_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_application_irhp_application1", columns={"irhp_application_id"}),
 *        @ORM\Index(name="fk_irhp_permit_application_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_irhp_permit_application_sectors_id1_idx", columns={"sectors_id"}),
 *        @ORM\Index(name="fk_irhp_permit_applications_irhp_permit_windows1_idx", columns={"irhp_permit_window_id"}),
 *        @ORM\Index(name="fk_irhp_permit_applications_licence1_idx", columns={"licence_id"}),
 *        @ORM\Index(name="irhp_permit_type_ref_data_status_id_fk", columns={"status"})
 *    }
 * )
 */
abstract class AbstractIrhpPermitApplication implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * IrhpPermitWindow
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_permit_window_id", referencedColumnName="id")
     */
    protected $irhpPermitWindow;

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
     * IrhpApplication
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpApplication", fetch="LAZY")
     * @ORM\JoinColumn(name="irhp_application_id", referencedColumnName="id", nullable=true)
     */
    protected $irhpApplication;

    /**
     * Sectors
     *
     * @var \Dvsa\Olcs\Api\Entity\Permits\Sectors
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Permits\Sectors", fetch="LAZY")
     * @ORM\JoinColumn(name="sectors_id", referencedColumnName="id", nullable=true)
     */
    protected $sectors;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=true)
     */
    protected $status;

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
     * Start date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="start_date", nullable=true)
     */
    protected $startDate;

    /**
     * Checked answers
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="checked_answers", nullable=false, options={"default": 0})
     */
    protected $checkedAnswers = 0;

    /**
     * Permits required
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="permits_required", nullable=true)
     */
    protected $permitsRequired;

    /**
     * Required euro5
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="required_euro5", nullable=true)
     */
    protected $requiredEuro5;

    /**
     * Required euro6
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="required_euro6", nullable=true)
     */
    protected $requiredEuro6;

    /**
     * Required standard
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="required_standard", nullable=true)
     */
    protected $requiredStandard;

    /**
     * Required cabotage
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="required_cabotage", nullable=true)
     */
    protected $requiredCabotage;

    /**
     * Properties
     *
     * @var string
     *
     * @ORM\Column(type="text", name="properties", nullable=true)
     */
    protected $properties;

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
     * Answers
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Generic\Answer", mappedBy="irhpPermitApplication", indexBy="question_text_id")
     */
    protected $answers;

    /**
     * Fees
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Fee\Fee", mappedBy="irhpPermitApplication")
     */
    protected $fees;

    /**
     * IrhpCandidatePermits
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit", mappedBy="irhpPermitApplication")
     */
    protected $irhpCandidatePermits;

    /**
     * IrhpPermits
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermit", mappedBy="irhpPermitApplication")
     */
    protected $irhpPermits;

    /**
     * IrhpPermitRequests
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRequest", mappedBy="irhpPermitApplication")
     */
    protected $irhpPermitRequests;

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
        $this->answers = new ArrayCollection();
        $this->fees = new ArrayCollection();
        $this->irhpCandidatePermits = new ArrayCollection();
        $this->irhpPermits = new ArrayCollection();
        $this->irhpPermitRequests = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return IrhpPermitApplication
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
     * Set the irhp permit window
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow $irhpPermitWindow new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setIrhpPermitWindow($irhpPermitWindow)
    {
        $this->irhpPermitWindow = $irhpPermitWindow;

        return $this;
    }

    /**
     * Get the irhp permit window
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow     */
    public function getIrhpPermitWindow()
    {
        return $this->irhpPermitWindow;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return IrhpPermitApplication
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
     * Set the irhp application
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication $irhpApplication new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setIrhpApplication($irhpApplication)
    {
        $this->irhpApplication = $irhpApplication;

        return $this;
    }

    /**
     * Get the irhp application
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\IrhpApplication     */
    public function getIrhpApplication()
    {
        return $this->irhpApplication;
    }

    /**
     * Set the sectors
     *
     * @param \Dvsa\Olcs\Api\Entity\Permits\Sectors $sectors new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setSectors($sectors)
    {
        $this->sectors = $sectors;

        return $this;
    }

    /**
     * Get the sectors
     *
     * @return \Dvsa\Olcs\Api\Entity\Permits\Sectors     */
    public function getSectors()
    {
        return $this->sectors;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return IrhpPermitApplication
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
     * @return IrhpPermitApplication
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
     * Set the start date
     *
     * @param \DateTime $startDate new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get the start date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getStartDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->startDate);
        }

        return $this->startDate;
    }

    /**
     * Set the checked answers
     *
     * @param bool $checkedAnswers new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setCheckedAnswers($checkedAnswers)
    {
        $this->checkedAnswers = $checkedAnswers;

        return $this;
    }

    /**
     * Get the checked answers
     *
     * @return bool     */
    public function getCheckedAnswers()
    {
        return $this->checkedAnswers;
    }

    /**
     * Set the permits required
     *
     * @param int $permitsRequired new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setPermitsRequired($permitsRequired)
    {
        $this->permitsRequired = $permitsRequired;

        return $this;
    }

    /**
     * Get the permits required
     *
     * @return int     */
    public function getPermitsRequired()
    {
        return $this->permitsRequired;
    }

    /**
     * Set the required euro5
     *
     * @param int $requiredEuro5 new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setRequiredEuro5($requiredEuro5)
    {
        $this->requiredEuro5 = $requiredEuro5;

        return $this;
    }

    /**
     * Get the required euro5
     *
     * @return int     */
    public function getRequiredEuro5()
    {
        return $this->requiredEuro5;
    }

    /**
     * Set the required euro6
     *
     * @param int $requiredEuro6 new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setRequiredEuro6($requiredEuro6)
    {
        $this->requiredEuro6 = $requiredEuro6;

        return $this;
    }

    /**
     * Get the required euro6
     *
     * @return int     */
    public function getRequiredEuro6()
    {
        return $this->requiredEuro6;
    }

    /**
     * Set the required standard
     *
     * @param int $requiredStandard new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setRequiredStandard($requiredStandard)
    {
        $this->requiredStandard = $requiredStandard;

        return $this;
    }

    /**
     * Get the required standard
     *
     * @return int     */
    public function getRequiredStandard()
    {
        return $this->requiredStandard;
    }

    /**
     * Set the required cabotage
     *
     * @param int $requiredCabotage new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setRequiredCabotage($requiredCabotage)
    {
        $this->requiredCabotage = $requiredCabotage;

        return $this;
    }

    /**
     * Get the required cabotage
     *
     * @return int     */
    public function getRequiredCabotage()
    {
        return $this->requiredCabotage;
    }

    /**
     * Set the properties
     *
     * @param string $properties new value being set
     *
     * @return IrhpPermitApplication
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Get the properties
     *
     * @return string     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return IrhpPermitApplication
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
     * Set the answers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $answers collection being set as the value
     *
     * @return IrhpPermitApplication
     */
    public function setAnswers($answers)
    {
        $this->answers = $answers;

        return $this;
    }

    /**
     * Get the answers
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Add a answers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $answers collection being added
     *
     * @return IrhpPermitApplication
     */
    public function addAnswers($answers)
    {
        if ($answers instanceof ArrayCollection) {
            $this->answers = new ArrayCollection(
                array_merge(
                    $this->answers->toArray(),
                    $answers->toArray()
                )
            );
        } elseif (!$this->answers->contains($answers)) {
            $this->answers->add($answers);
        }

        return $this;
    }

    /**
     * Remove a answers
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $answers collection being removed
     *
     * @return IrhpPermitApplication
     */
    public function removeAnswers($answers)
    {
        if ($this->answers->contains($answers)) {
            $this->answers->removeElement($answers);
        }

        return $this;
    }

    /**
     * Set the fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being set as the value
     *
     * @return IrhpPermitApplication
     */
    public function setFees($fees)
    {
        $this->fees = $fees;

        return $this;
    }

    /**
     * Get the fees
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Add a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $fees collection being added
     *
     * @return IrhpPermitApplication
     */
    public function addFees($fees)
    {
        if ($fees instanceof ArrayCollection) {
            $this->fees = new ArrayCollection(
                array_merge(
                    $this->fees->toArray(),
                    $fees->toArray()
                )
            );
        } elseif (!$this->fees->contains($fees)) {
            $this->fees->add($fees);
        }

        return $this;
    }

    /**
     * Remove a fees
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $fees collection being removed
     *
     * @return IrhpPermitApplication
     */
    public function removeFees($fees)
    {
        if ($this->fees->contains($fees)) {
            $this->fees->removeElement($fees);
        }

        return $this;
    }

    /**
     * Set the irhp candidate permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpCandidatePermits collection being set as the value
     *
     * @return IrhpPermitApplication
     */
    public function setIrhpCandidatePermits($irhpCandidatePermits)
    {
        $this->irhpCandidatePermits = $irhpCandidatePermits;

        return $this;
    }

    /**
     * Get the irhp candidate permits
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpCandidatePermits()
    {
        return $this->irhpCandidatePermits;
    }

    /**
     * Add a irhp candidate permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpCandidatePermits collection being added
     *
     * @return IrhpPermitApplication
     */
    public function addIrhpCandidatePermits($irhpCandidatePermits)
    {
        if ($irhpCandidatePermits instanceof ArrayCollection) {
            $this->irhpCandidatePermits = new ArrayCollection(
                array_merge(
                    $this->irhpCandidatePermits->toArray(),
                    $irhpCandidatePermits->toArray()
                )
            );
        } elseif (!$this->irhpCandidatePermits->contains($irhpCandidatePermits)) {
            $this->irhpCandidatePermits->add($irhpCandidatePermits);
        }

        return $this;
    }

    /**
     * Remove a irhp candidate permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpCandidatePermits collection being removed
     *
     * @return IrhpPermitApplication
     */
    public function removeIrhpCandidatePermits($irhpCandidatePermits)
    {
        if ($this->irhpCandidatePermits->contains($irhpCandidatePermits)) {
            $this->irhpCandidatePermits->removeElement($irhpCandidatePermits);
        }

        return $this;
    }

    /**
     * Set the irhp permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermits collection being set as the value
     *
     * @return IrhpPermitApplication
     */
    public function setIrhpPermits($irhpPermits)
    {
        $this->irhpPermits = $irhpPermits;

        return $this;
    }

    /**
     * Get the irhp permits
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermits()
    {
        return $this->irhpPermits;
    }

    /**
     * Add a irhp permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermits collection being added
     *
     * @return IrhpPermitApplication
     */
    public function addIrhpPermits($irhpPermits)
    {
        if ($irhpPermits instanceof ArrayCollection) {
            $this->irhpPermits = new ArrayCollection(
                array_merge(
                    $this->irhpPermits->toArray(),
                    $irhpPermits->toArray()
                )
            );
        } elseif (!$this->irhpPermits->contains($irhpPermits)) {
            $this->irhpPermits->add($irhpPermits);
        }

        return $this;
    }

    /**
     * Remove a irhp permits
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermits collection being removed
     *
     * @return IrhpPermitApplication
     */
    public function removeIrhpPermits($irhpPermits)
    {
        if ($this->irhpPermits->contains($irhpPermits)) {
            $this->irhpPermits->removeElement($irhpPermits);
        }

        return $this;
    }

    /**
     * Set the irhp permit requests
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRequests collection being set as the value
     *
     * @return IrhpPermitApplication
     */
    public function setIrhpPermitRequests($irhpPermitRequests)
    {
        $this->irhpPermitRequests = $irhpPermitRequests;

        return $this;
    }

    /**
     * Get the irhp permit requests
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getIrhpPermitRequests()
    {
        return $this->irhpPermitRequests;
    }

    /**
     * Add a irhp permit requests
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $irhpPermitRequests collection being added
     *
     * @return IrhpPermitApplication
     */
    public function addIrhpPermitRequests($irhpPermitRequests)
    {
        if ($irhpPermitRequests instanceof ArrayCollection) {
            $this->irhpPermitRequests = new ArrayCollection(
                array_merge(
                    $this->irhpPermitRequests->toArray(),
                    $irhpPermitRequests->toArray()
                )
            );
        } elseif (!$this->irhpPermitRequests->contains($irhpPermitRequests)) {
            $this->irhpPermitRequests->add($irhpPermitRequests);
        }

        return $this;
    }

    /**
     * Remove a irhp permit requests
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $irhpPermitRequests collection being removed
     *
     * @return IrhpPermitApplication
     */
    public function removeIrhpPermitRequests($irhpPermitRequests)
    {
        if ($this->irhpPermitRequests->contains($irhpPermitRequests)) {
            $this->irhpPermitRequests->removeElement($irhpPermitRequests);
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
