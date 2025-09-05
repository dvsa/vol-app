<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SubCategory Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="sub_category",
 *    indexes={
 *        @ORM\Index(name="ix_sub_category_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_sub_category_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_sub_category_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractSubCategory implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Category
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Category", fetch="LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    protected $category;

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
     * Is doc
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_doc", nullable=false, options={"default": 0})
     */
    protected $isDoc = 0;

    /**
     * Is free text
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_free_text", nullable=false, options={"default": 0})
     */
    protected $isFreeText = 0;

    /**
     * Is messaging
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_messaging", nullable=false, options={"default": 0})
     */
    protected $isMessaging = 0;

    /**
     * Is Case SLA Exception
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_case_sla_exception", nullable=false, options={"default": 0})
     */
    protected $isCaseSlaException = 0;

    /**
     * Is scan
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_scan", nullable=false, options={"default": 0})
     */
    protected $isScan = 0;

    /**
     * Is task
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_task", nullable=false, options={"default": 0})
     */
    protected $isTask = 0;

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
     * Sub category name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="sub_category_name", length=64, nullable=false)
     */
    protected $subCategoryName;

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
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category entity being set as the value
     *
     * @return SubCategory
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return SubCategory
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
     * @return SubCategory
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
     * Set the is doc
     *
     * @param boolean $isDoc new value being set
     *
     * @return SubCategory
     */
    public function setIsDoc($isDoc)
    {
        $this->isDoc = $isDoc;

        return $this;
    }

    /**
     * Get the is doc
     *
     * @return boolean
     */
    public function getIsDoc()
    {
        return $this->isDoc;
    }

    /**
     * Set the is free text
     *
     * @param boolean $isFreeText new value being set
     *
     * @return SubCategory
     */
    public function setIsFreeText($isFreeText)
    {
        $this->isFreeText = $isFreeText;

        return $this;
    }

    /**
     * Get the is free text
     *
     * @return boolean
     */
    public function getIsFreeText()
    {
        return $this->isFreeText;
    }

    /**
     * Set the is messaging
     *
     * @param boolean $isMessaging new value being set
     *
     * @return SubCategory
     */
    public function setIsMessaging($isMessaging)
    {
        $this->isMessaging = $isMessaging;

        return $this;
    }

    /**
     * Get the is messaging
     *
     * @return boolean
     */
    public function getIsMessaging()
    {
        return $this->isMessaging;
    }

    /**
     * Set the is scan
     *
     * @param boolean $isScan new value being set
     *
     * @return SubCategory
     */
    public function setIsScan($isScan)
    {
        $this->isScan = $isScan;

        return $this;
    }

    /**
     * Get the is scan
     *
     * @return boolean
     */
    public function getIsScan()
    {
        return $this->isScan;
    }

    /**
     * Set the is task
     *
     * @param boolean $isTask new value being set
     *
     * @return SubCategory
     */
    public function setIsTask($isTask)
    {
        $this->isTask = $isTask;

        return $this;
    }

    /**
     * Get the is task
     *
     * @return boolean
     */
    public function getIsTask()
    {
        return $this->isTask;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return SubCategory
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
     * Set the sub category name
     *
     * @param string $subCategoryName new value being set
     *
     * @return SubCategory
     */
    public function setSubCategoryName($subCategoryName)
    {
        $this->subCategoryName = $subCategoryName;

        return $this;
    }

    /**
     * Get the sub category name
     *
     * @return string
     */
    public function getSubCategoryName()
    {
        return $this->subCategoryName;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return SubCategory
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
}
