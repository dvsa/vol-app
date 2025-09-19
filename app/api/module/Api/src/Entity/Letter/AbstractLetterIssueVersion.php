<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

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
 * LetterIssueVersion Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_issue_version",
 *    indexes={
 *        @ORM\Index(name="ix_letter_issue_version_letter_issue_id", columns={"letter_issue_id"}),
 *        @ORM\Index(name="ix_letter_issue_version_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_letter_issue_version_sub_category_id", columns={"sub_category_id"}),
 *        @ORM\Index(name="ix_letter_issue_version_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_issue_version_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_letter_issue_version_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractLetterIssueVersion implements BundleSerializableInterface, JsonSerializable
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
     * Default body content
     *
     * @var array
     *
     * @ORM\Column(type="json", name="default_body_content", nullable=true)
     */
    protected $defaultBodyContent;

    /**
     * Goods or PSV
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id", nullable=true)
     */
    protected $goodsOrPsv;

    /**
     * Heading
     *
     * @var string
     *
     * @ORM\Column(type="string", name="heading", length=255, nullable=false)
     */
    protected $heading;

    /**
     * Help text
     *
     * @var string
     *
     * @ORM\Column(type="text", name="help_text", nullable=true)
     */
    protected $helpText;

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
     * Is locked
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_locked", nullable=false, options={"default": 0})
     */
    protected $isLocked = false;

    /**
     * Is NI
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_ni", nullable=false, options={"default": 0})
     */
    protected $isNi = false;

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
     * Letter issue
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterIssue
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterIssue",
     *     fetch="LAZY",
     *     inversedBy="versions"
     * )
     * @ORM\JoinColumn(name="letter_issue_id", referencedColumnName="id", nullable=false)
     */
    protected $letterIssue;

    /**
     * Max length
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="max_length", nullable=true)
     */
    protected $maxLength;

    /**
     * Min length
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="min_length", nullable=true)
     */
    protected $minLength;

    /**
     * Publish from
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="publish_from", nullable=true)
     */
    protected $publishFrom;

    /**
     * Requires input
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="requires_input", nullable=false, options={"default": 0})
     */
    protected $requiresInput = false;

    /**
     * Sub category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\SubCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="sub_category_id", referencedColumnName="id", nullable=true)
     */
    protected $subCategory;

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
     * Version number
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="version_number", nullable=false)
     */
    protected $versionNumber;

    /**
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category entity being set as the value
     *
     * @return self
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
     * @return self
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
     * Set the default body content
     *
     * @param array $defaultBodyContent new value being set
     *
     * @return self
     */
    public function setDefaultBodyContent($defaultBodyContent)
    {
        $this->defaultBodyContent = $defaultBodyContent;

        return $this;
    }

    /**
     * Get the default body content
     *
     * @return array
     */
    public function getDefaultBodyContent()
    {
        return $this->defaultBodyContent;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv entity being set as the value
     *
     * @return self
     */
    public function setGoodsOrPsv($goodsOrPsv)
    {
        $this->goodsOrPsv = $goodsOrPsv;

        return $this;
    }

    /**
     * Get the goods or psv
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }

    /**
     * Set the heading
     *
     * @param string $heading new value being set
     *
     * @return self
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Get the heading
     *
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Set the help text
     *
     * @param string $helpText new value being set
     *
     * @return self
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;

        return $this;
    }

    /**
     * Get the help text
     *
     * @return string
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return self
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
     * Set the is locked
     *
     * @param bool $isLocked new value being set
     *
     * @return self
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Get the is locked
     *
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * Set the is ni
     *
     * @param bool $isNi new value being set
     *
     * @return self
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return bool
     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return self
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
     * Set the letter issue
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterIssue $letterIssue entity being set as the value
     *
     * @return self
     */
    public function setLetterIssue($letterIssue)
    {
        $this->letterIssue = $letterIssue;

        return $this;
    }

    /**
     * Get the letter issue
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterIssue
     */
    public function getLetterIssue()
    {
        return $this->letterIssue;
    }

    /**
     * Set the max length
     *
     * @param int $maxLength new value being set
     *
     * @return self
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * Get the max length
     *
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set the min length
     *
     * @param int $minLength new value being set
     *
     * @return self
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * Get the min length
     *
     * @return int
     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set the publish from
     *
     * @param \DateTime $publishFrom new value being set
     *
     * @return self
     */
    public function setPublishFrom($publishFrom)
    {
        $this->publishFrom = $publishFrom;

        return $this;
    }

    /**
     * Get the publish from
     *
     * @return \DateTime
     */
    public function getPublishFrom()
    {
        return $this->publishFrom;
    }

    /**
     * Set the requires input
     *
     * @param bool $requiresInput new value being set
     *
     * @return self
     */
    public function setRequiresInput($requiresInput)
    {
        $this->requiresInput = $requiresInput;

        return $this;
    }

    /**
     * Get the requires input
     *
     * @return bool
     */
    public function getRequiresInput()
    {
        return $this->requiresInput;
    }

    /**
     * Set the sub category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\SubCategory $subCategory entity being set as the value
     *
     * @return self
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get the sub category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SubCategory
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return self
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
     * Set the version number
     *
     * @param int $versionNumber new value being set
     *
     * @return self
     */
    public function setVersionNumber($versionNumber)
    {
        $this->versionNumber = $versionNumber;

        return $this;
    }

    /**
     * Get the version number
     *
     * @return int
     */
    public function getVersionNumber()
    {
        return $this->versionNumber;
    }
}