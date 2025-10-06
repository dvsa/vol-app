<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity;

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
 * AbstractDataRetentionRule Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="data_retention_rule",
 *    indexes={
 *        @ORM\Index(name="fk_data_retention_rule_action_type_ref_data_id", columns={"action_type"}),
 *        @ORM\Index(name="fk_data_retention_rule_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_data_retention_rule_last_modified_by_user_id", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractDataRetentionRule implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id", nullable=false)
     */
    protected $id = 0;

    /**
     * ActionType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="action_type", referencedColumnName="id")
     */
    protected $actionType;

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
     * Primary Record Retention Period (in MONTHS) following record closure
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="retention_period", nullable=false)
     */
    protected $retentionPeriod = 0;

    /**
     * max rows of population dataset.
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="max_data_set", nullable=true)
     */
    protected $maxDataSet;

    /**
     * Is enabled
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_enabled", nullable=false, options={"default": 0})
     */
    protected $isEnabled = 0;

    /**
     * Is custom rule
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_custom_rule", nullable=false, options={"default": 0})
     */
    protected $isCustomRule = 0;

    /**
     * procedure to populate rule data set
     *
     * @var string
     *
     * @ORM\Column(type="string", name="populate_procedure", length=64, nullable=false)
     */
    protected $populateProcedure = '';

    /**
     * Custom procedure
     *
     * @var string
     *
     * @ORM\Column(type="string", name="custom_procedure", length=64, nullable=true)
     */
    protected $customProcedure;

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
     * @return DataRetentionRule
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
     * Set the action type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $actionType new value being set
     *
     * @return DataRetentionRule
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;

        return $this;
    }

    /**
     * Get the action type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return DataRetentionRule
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
     * @return DataRetentionRule
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
     * @return DataRetentionRule
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
     * Set the retention period
     *
     * @param int $retentionPeriod new value being set
     *
     * @return DataRetentionRule
     */
    public function setRetentionPeriod($retentionPeriod)
    {
        $this->retentionPeriod = $retentionPeriod;

        return $this;
    }

    /**
     * Get the retention period
     *
     * @return int     */
    public function getRetentionPeriod()
    {
        return $this->retentionPeriod;
    }

    /**
     * Set the max data set
     *
     * @param int $maxDataSet new value being set
     *
     * @return DataRetentionRule
     */
    public function setMaxDataSet($maxDataSet)
    {
        $this->maxDataSet = $maxDataSet;

        return $this;
    }

    /**
     * Get the max data set
     *
     * @return int     */
    public function getMaxDataSet()
    {
        return $this->maxDataSet;
    }

    /**
     * Set the is enabled
     *
     * @param bool $isEnabled new value being set
     *
     * @return DataRetentionRule
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get the is enabled
     *
     * @return bool     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set the is custom rule
     *
     * @param bool $isCustomRule new value being set
     *
     * @return DataRetentionRule
     */
    public function setIsCustomRule($isCustomRule)
    {
        $this->isCustomRule = $isCustomRule;

        return $this;
    }

    /**
     * Get the is custom rule
     *
     * @return bool     */
    public function getIsCustomRule()
    {
        return $this->isCustomRule;
    }

    /**
     * Set the populate procedure
     *
     * @param string $populateProcedure new value being set
     *
     * @return DataRetentionRule
     */
    public function setPopulateProcedure($populateProcedure)
    {
        $this->populateProcedure = $populateProcedure;

        return $this;
    }

    /**
     * Get the populate procedure
     *
     * @return string     */
    public function getPopulateProcedure()
    {
        return $this->populateProcedure;
    }

    /**
     * Set the custom procedure
     *
     * @param string $customProcedure new value being set
     *
     * @return DataRetentionRule
     */
    public function setCustomProcedure($customProcedure)
    {
        $this->customProcedure = $customProcedure;

        return $this;
    }

    /**
     * Get the custom procedure
     *
     * @return string     */
    public function getCustomProcedure()
    {
        return $this->customProcedure;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}