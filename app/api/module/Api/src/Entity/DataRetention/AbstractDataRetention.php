<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\DataRetention;

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
 * AbstractDataRetention Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="data_retention",
 *    indexes={
 *        @ORM\Index(name="ix_assigned_to", columns={"assigned_to"}),
 *        @ORM\Index(name="ix_data_retention_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_data_retention_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_data_retention_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_data_retention_rule_id", columns={"data_retention_rule_id"}),
 *        @ORM\Index(name="ix_delete_confirmation", columns={"action_confirmation"}),
 *        @ORM\Index(name="ix_deleted_date", columns={"deleted_date"}),
 *        @ORM\Index(name="ix_entity_name", columns={"entity_name"}),
 *        @ORM\Index(name="ix_entity_name_entity_pk", columns={"entity_name", "entity_pk"}),
 *        @ORM\Index(name="ix_lic_no", columns={"lic_no"}),
 *        @ORM\Index(name="ix_organisation_id", columns={"organisation_id"})
 *    }
 * )
 */
abstract class AbstractDataRetention implements BundleSerializableInterface, JsonSerializable
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
     * AssignedTo
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="assigned_to", referencedColumnName="id", nullable=true)
     */
    protected $assignedTo;

    /**
     * GoodsOrPsv
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id", nullable=true)
     */
    protected $goodsOrPsv;

    /**
     * FK to data retention rule
     *
     * @var \Dvsa\Olcs\Api\Entity\DataRetentionRule
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DataRetentionRule", fetch="LAZY")
     * @ORM\JoinColumn(name="data_retention_rule_id", referencedColumnName="id")
     */
    protected $dataRetentionRule;

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
     * Entity name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="entity_name", length=64, nullable=false)
     */
    protected $entityName = '';

    /**
     * Entity pk
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="entity_pk", nullable=false)
     */
    protected $entityPk = 0;

    /**
     * Organisation name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="organisation_name", length=160, nullable=true)
     */
    protected $organisationName;

    /**
     * Organisation id
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="organisation_id", nullable=true)
     */
    protected $organisationId;

    /**
     * Licence id
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="licence_id", nullable=true)
     */
    protected $licenceId;

    /**
     * Lic no
     *
     * @var string
     *
     * @ORM\Column(type="string", name="lic_no", length=18, nullable=true)
     */
    protected $licNo;

    /**
     * Action confirmation
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="action_confirmation", nullable=false)
     */
    protected $actionConfirmation = 0;

    /**
     * Next review date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="next_review_date", nullable=true)
     */
    protected $nextReviewDate;

    /**
     * Actioned date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="actioned_date", nullable=true)
     */
    protected $actionedDate;

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
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return DataRetention
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
     * Set the assigned to
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $assignedTo new value being set
     *
     * @return DataRetention
     */
    public function setAssignedTo($assignedTo)
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * Get the assigned to
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv new value being set
     *
     * @return DataRetention
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        return $this;
    }

    /**
     * Get the goods or psv
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * Set the data retention rule
     *
     * @param \Dvsa\Olcs\Api\Entity\DataRetentionRule $dataRetentionRule new value being set
     *
     * @return DataRetention
     */
    public function setDataRetentionRule($dataRetentionRule)
    {
        $this->dataRetentionRule = $dataRetentionRule;

        return $this;
    }

    /**
     * Get the data retention rule
     *
     * @return \Dvsa\Olcs\Api\Entity\DataRetentionRule     */
    public function getDataRetentionRule()
    {
        return $this->dataRetentionRule;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return DataRetention
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
     * @return DataRetention
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
     * Set the entity name
     *
     * @param string $entityName new value being set
     *
     * @return DataRetention
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get the entity name
     *
     * @return string     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Set the entity pk
     *
     * @param int $entityPk new value being set
     *
     * @return DataRetention
     */
    public function setEntityPk($entityPk)
    {
        $this->entityPk = $entityPk;

        return $this;
    }

    /**
     * Get the entity pk
     *
     * @return int     */
    public function getEntityPk()
    {
        return $this->entityPk;
    }

    /**
     * Set the organisation name
     *
     * @param string $organisationName new value being set
     *
     * @return DataRetention
     */
    public function setOrganisationName($organisationName)
    {
        $this->organisationName = $organisationName;

        return $this;
    }

    /**
     * Get the organisation name
     *
     * @return string     */
    public function getOrganisationName()
    {
        return $this->organisationName;
    }

    /**
     * Set the organisation id
     *
     * @param int $organisationId new value being set
     *
     * @return DataRetention
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = $organisationId;

        return $this;
    }

    /**
     * Get the organisation id
     *
     * @return int     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * Set the licence id
     *
     * @param int $licenceId new value being set
     *
     * @return DataRetention
     */
    public function setLicenceId($licenceId)
    {
        $this->licenceId = $licenceId;

        return $this;
    }

    /**
     * Get the licence id
     *
     * @return int     */
    public function getLicenceId()
    {
        return $this->licenceId;
    }

    /**
     * Set the lic no
     *
     * @param string $licNo new value being set
     *
     * @return DataRetention
     */
    public function setLicNo($licNo)
    {
        $this->licNo = $licNo;

        return $this;
    }

    /**
     * Get the lic no
     *
     * @return string     */
    public function getLicNo()
    {
        return $this->licNo;
    }

    /**
     * Set the action confirmation
     *
     * @param bool $actionConfirmation new value being set
     *
     * @return DataRetention
     */
    public function setActionConfirmation($actionConfirmation)
    {
        $this->actionConfirmation = $actionConfirmation;

        return $this;
    }

    /**
     * Get the action confirmation
     *
     * @return bool     */
    public function getActionConfirmation()
    {
        return $this->actionConfirmation;
    }

    /**
     * Set the next review date
     *
     * @param \DateTime $nextReviewDate new value being set
     *
     * @return DataRetention
     */
    public function setNextReviewDate($nextReviewDate)
    {
        $this->nextReviewDate = $nextReviewDate;

        return $this;
    }

    /**
     * Get the next review date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getNextReviewDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->nextReviewDate);
        }

        return $this->nextReviewDate;
    }

    /**
     * Set the actioned date
     *
     * @param \DateTime $actionedDate new value being set
     *
     * @return DataRetention
     */
    public function setActionedDate($actionedDate)
    {
        $this->actionedDate = $actionedDate;

        return $this;
    }

    /**
     * Get the actioned date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getActionedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->actionedDate);
        }

        return $this->actionedDate;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}