<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Cases;

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
 * AbstractConditionUndertaking Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="condition_undertaking",
 *    indexes={
 *        @ORM\Index(name="ix_condition_undertaking_added_via", columns={"added_via"}),
 *        @ORM\Index(name="ix_condition_undertaking_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_approval_user_id", columns={"approval_user_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_attached_to", columns={"attached_to"}),
 *        @ORM\Index(name="ix_condition_undertaking_case_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_condition_category", columns={"condition_category"}),
 *        @ORM\Index(name="ix_condition_undertaking_condition_type", columns={"condition_type"}),
 *        @ORM\Index(name="ix_condition_undertaking_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_condition_undertaking_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_condition_undertaking_lic_condition_variation_id", columns={"lic_condition_variation_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_operating_centre_id", columns={"operating_centre_id"}),
 *        @ORM\Index(name="ix_condition_undertaking_s4_id", columns={"s4_id"}),
 *        @ORM\Index(name="uk_condition_undertaking_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_condition_undertaking_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    }
 * )
 */
abstract class AbstractConditionUndertaking implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true)
     */
    protected $application;

    /**
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=true)
     */
    protected $licence;

    /**
     * Foreign Key to operating_centre
     *
     * @var \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre", fetch="LAZY")
     * @ORM\JoinColumn(name="operating_centre_id", referencedColumnName="id", nullable=true)
     */
    protected $operatingCentre;

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
     * The condition on linked to the licence that is being changed by the application condition. Changes applied when application is granted.
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking", fetch="LAZY")
     * @ORM\JoinColumn(name="lic_condition_variation_id", referencedColumnName="id", nullable=true)
     */
    protected $licConditionVariation;

    /**
     * Condition or Undertaking
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="condition_type", referencedColumnName="id")
     */
    protected $conditionType;

    /**
     * ConditionCategory
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="condition_category", referencedColumnName="id", nullable=true)
     */
    protected $conditionCategory;

    /**
     * Foreign Key to s4
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\S4
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\S4", fetch="LAZY")
     * @ORM\JoinColumn(name="s4_id", referencedColumnName="id", nullable=true)
     */
    protected $s4;

    /**
     * Episode, Application or Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="added_via", referencedColumnName="id", nullable=true)
     */
    protected $addedVia;

    /**
     * Licence or Operating Centre
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="attached_to", referencedColumnName="id", nullable=true)
     */
    protected $attachedTo;

    /**
     * ApprovalUser
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="approval_user_id", referencedColumnName="id", nullable=true)
     */
    protected $approvalUser;

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
     * For application conditions A for add and U for update, if updating a licence condition via an app.
     *
     * @var string
     *
     * @ORM\Column(type="string", name="action", length=1, nullable=true)
     */
    protected $action;

    /**
     * isDraft
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_draft", nullable=false, options={"default": 0})
     */
    protected $isDraft = 0;

    /**
     * isFulfilled
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_fulfilled", nullable=false, options={"default": 0})
     */
    protected $isFulfilled = 0;

    /**
     * Notes
     *
     * @var string
     *
     * @ORM\Column(type="string", name="notes", length=8000, nullable=true)
     */
    protected $notes;

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
     * used to differntiate source of data during ETL when one OLCS table relates to many OLBS. Can be dropped when fully live
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * VariationRecords
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking", mappedBy="licConditionVariation")
     */
    protected $variationRecords;

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
        $this->variationRecords = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return ConditionUndertaking
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
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
     *
     * @return ConditionUndertaking
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return ConditionUndertaking
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
     * Set the operating centre
     *
     * @param \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre $operatingCentre new value being set
     *
     * @return ConditionUndertaking
     */
    public function setOperatingCentre($operatingCentre)
    {
        $this->operatingCentre = $operatingCentre;

        return $this;
    }

    /**
     * Get the operating centre
     *
     * @return \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * Set the case
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Cases $case new value being set
     *
     * @return ConditionUndertaking
     */
    public function setCase($case)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * Get the case
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * Set the lic condition variation
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking $licConditionVariation new value being set
     *
     * @return ConditionUndertaking
     */
    public function setLicConditionVariation($licConditionVariation)
    {
        $this->licConditionVariation = $licConditionVariation;

        return $this;
    }

    /**
     * Get the lic condition variation
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking     */
    public function getLicConditionVariation()
    {
        return $this->licConditionVariation;
    }

    /**
     * Set the condition type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $conditionType new value being set
     *
     * @return ConditionUndertaking
     */
    public function setConditionType($conditionType)
    {
        $this->conditionType = $conditionType;

        return $this;
    }

    /**
     * Get the condition type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getConditionType()
    {
        return $this->conditionType;
    }

    /**
     * Set the condition category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $conditionCategory new value being set
     *
     * @return ConditionUndertaking
     */
    public function setConditionCategory($conditionCategory)
    {
        $this->conditionCategory = $conditionCategory;

        return $this;
    }

    /**
     * Get the condition category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getConditionCategory()
    {
        return $this->conditionCategory;
    }

    /**
     * Set the s4
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\S4 $s4 new value being set
     *
     * @return ConditionUndertaking
     */
    public function setS4($s4)
    {
        $this->s4 = $s4;

        return $this;
    }

    /**
     * Get the s4
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\S4     */
    public function getS4()
    {
        return $this->s4;
    }

    /**
     * Set the added via
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $addedVia new value being set
     *
     * @return ConditionUndertaking
     */
    public function setAddedVia($addedVia)
    {
        $this->addedVia = $addedVia;

        return $this;
    }

    /**
     * Get the added via
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getAddedVia()
    {
        return $this->addedVia;
    }

    /**
     * Set the attached to
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $attachedTo new value being set
     *
     * @return ConditionUndertaking
     */
    public function setAttachedTo($attachedTo)
    {
        $this->attachedTo = $attachedTo;

        return $this;
    }

    /**
     * Get the attached to
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getAttachedTo()
    {
        return $this->attachedTo;
    }

    /**
     * Set the approval user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $approvalUser new value being set
     *
     * @return ConditionUndertaking
     */
    public function setApprovalUser($approvalUser)
    {
        $this->approvalUser = $approvalUser;

        return $this;
    }

    /**
     * Get the approval user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getApprovalUser()
    {
        return $this->approvalUser;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return ConditionUndertaking
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
     * @return ConditionUndertaking
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
     * Set the action
     *
     * @param string $action new value being set
     *
     * @return ConditionUndertaking
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the action
     *
     * @return string     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the is draft
     *
     * @param string $isDraft new value being set
     *
     * @return ConditionUndertaking
     */
    public function setIsDraft($isDraft)
    {
        $this->isDraft = $isDraft;

        return $this;
    }

    /**
     * Get the is draft
     *
     * @return string     */
    public function getIsDraft()
    {
        return $this->isDraft;
    }

    /**
     * Set the is fulfilled
     *
     * @param string $isFulfilled new value being set
     *
     * @return ConditionUndertaking
     */
    public function setIsFulfilled($isFulfilled)
    {
        $this->isFulfilled = $isFulfilled;

        return $this;
    }

    /**
     * Get the is fulfilled
     *
     * @return string     */
    public function getIsFulfilled()
    {
        return $this->isFulfilled;
    }

    /**
     * Set the notes
     *
     * @param string $notes new value being set
     *
     * @return ConditionUndertaking
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the notes
     *
     * @return string     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ConditionUndertaking
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
     * @return ConditionUndertaking
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
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return ConditionUndertaking
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the variation records
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $variationRecords collection being set as the value
     *
     * @return ConditionUndertaking
     */
    public function setVariationRecords($variationRecords)
    {
        $this->variationRecords = $variationRecords;

        return $this;
    }

    /**
     * Get the variation records
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getVariationRecords()
    {
        return $this->variationRecords;
    }

    /**
     * Add a variation records
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $variationRecords collection being added
     *
     * @return ConditionUndertaking
     */
    public function addVariationRecords($variationRecords)
    {
        if ($variationRecords instanceof ArrayCollection) {
            $this->variationRecords = new ArrayCollection(
                array_merge(
                    $this->variationRecords->toArray(),
                    $variationRecords->toArray()
                )
            );
        } elseif (!$this->variationRecords->contains($variationRecords)) {
            $this->variationRecords->add($variationRecords);
        }

        return $this;
    }

    /**
     * Remove a variation records
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $variationRecords collection being removed
     *
     * @return ConditionUndertaking
     */
    public function removeVariationRecords($variationRecords)
    {
        if ($this->variationRecords->contains($variationRecords)) {
            $this->variationRecords->removeElement($variationRecords);
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
