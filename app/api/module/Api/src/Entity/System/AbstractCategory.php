<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

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
 * AbstractCategory Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="category",
 *    indexes={
 *        @ORM\Index(name="ix_category_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_category_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_category_task_allocation_type", columns={"task_allocation_type"})
 *    }
 * )
 */
abstract class AbstractCategory implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Tasks of this category are allocated based upon TA, a single team or complex rules for icence type, TA, MLH.
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="task_allocation_type", referencedColumnName="id", nullable=true)
     */
    protected $taskAllocationType;

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
     * e.g. Compliance, Environmental
     *
     * @var string
     *
     * @ORM\Column(type="string", name="description", length=255, nullable=false)
     */
    protected $description = '';

    /**
     * Documents can have this category
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_doc_category", nullable=false, options={"default": 1})
     */
    protected $isDocCategory = 1;

    /**
     * Tasks can have this category
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="is_task_category", nullable=false, options={"default": 1})
     */
    protected $isTaskCategory = 1;

    /**
     * Is a category that can be applied to scanned documents.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_scan_category", nullable=false, options={"default": 1})
     */
    protected $isScanCategory = 1;

    /**
     * Is messaging
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_messaging", nullable=false, options={"default": 0})
     */
    protected $isMessaging = 0;

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
     * @return Category
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
     * Set the task allocation type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $taskAllocationType new value being set
     *
     * @return Category
     */
    public function setTaskAllocationType($taskAllocationType)
    {
        $this->taskAllocationType = $taskAllocationType;

        return $this;
    }

    /**
     * Get the task allocation type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getTaskAllocationType()
    {
        return $this->taskAllocationType;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Category
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
     * @return Category
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
     * @return Category
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
     * Set the is doc category
     *
     * @param string $isDocCategory new value being set
     *
     * @return Category
     */
    public function setIsDocCategory($isDocCategory)
    {
        $this->isDocCategory = $isDocCategory;

        return $this;
    }

    /**
     * Get the is doc category
     *
     * @return string     */
    public function getIsDocCategory()
    {
        return $this->isDocCategory;
    }

    /**
     * Set the is task category
     *
     * @param string $isTaskCategory new value being set
     *
     * @return Category
     */
    public function setIsTaskCategory($isTaskCategory)
    {
        $this->isTaskCategory = $isTaskCategory;

        return $this;
    }

    /**
     * Get the is task category
     *
     * @return string     */
    public function getIsTaskCategory()
    {
        return $this->isTaskCategory;
    }

    /**
     * Set the is scan category
     *
     * @param bool $isScanCategory new value being set
     *
     * @return Category
     */
    public function setIsScanCategory($isScanCategory)
    {
        $this->isScanCategory = $isScanCategory;

        return $this;
    }

    /**
     * Get the is scan category
     *
     * @return bool     */
    public function getIsScanCategory()
    {
        return $this->isScanCategory;
    }

    /**
     * Set the is messaging
     *
     * @param bool $isMessaging new value being set
     *
     * @return Category
     */
    public function setIsMessaging($isMessaging)
    {
        $this->isMessaging = $isMessaging;

        return $this;
    }

    /**
     * Get the is messaging
     *
     * @return bool     */
    public function getIsMessaging()
    {
        return $this->isMessaging;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Category
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
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}