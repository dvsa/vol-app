<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

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
 * AbstractLetterIssueVersion Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_issue_version",
 *    indexes={
 *        @ORM\Index(name="ix_letter_issue_version_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_letter_issue_version_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_issue_version_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_letter_issue_version_issue_type_id", columns={"letter_issue_type_id"}),
 *        @ORM\Index(name="ix_letter_issue_version_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_issue_version_letter_issue_id", columns={"letter_issue_id"}),
 *        @ORM\Index(name="ix_letter_issue_version_sub_category_id", columns={"sub_category_id"})
 *    }
 * )
 */
abstract class AbstractLetterIssueVersion implements BundleSerializableInterface, JsonSerializable
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
     * LetterIssue
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterIssue
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterIssue", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_issue_id", referencedColumnName="id")
     */
    protected $letterIssue;

    /**
     * LetterIssueType
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterIssueType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterIssueType", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_issue_type_id", referencedColumnName="id", nullable=true)
     */
    protected $letterIssueType;

    /**
     * Category
     *
     * @var \Dvsa\Olcs\Api\Entity\System\Category
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\Category", fetch="LAZY")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * SubCategory
     *
     * @var \Dvsa\Olcs\Api\Entity\System\SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\SubCategory", fetch="LAZY")
     * @ORM\JoinColumn(name="sub_category_id", referencedColumnName="id", nullable=true)
     */
    protected $subCategory;

    /**
     * FK to ref_data lcat_gv or lcat_psv
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id", nullable=true)
     */
    protected $goodsOrPsv;

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
     * Heading
     *
     * @var string
     *
     * @ORM\Column(type="string", name="heading", length=255, nullable=false)
     */
    protected $heading = '';

    /**
     * Editor.js format
     *
     * @var array
     *
     * @ORM\Column(type="json", name="default_body_content", nullable=true)
     */
    protected $defaultBodyContent;

    /**
     * Help text for users
     *
     * @var string
     *
     * @ORM\Column(type="text", name="help_text", nullable=true)
     */
    protected $helpText;

    /**
     * Minimum content length
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="min_length", nullable=true)
     */
    protected $minLength;

    /**
     * Maximum content length
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="max_length", nullable=true)
     */
    protected $maxLength;

    /**
     * Is locked
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_locked", nullable=false, options={"default": 0})
     */
    protected $isLocked = 0;

    /**
     * Issue has placeholders that must be edited
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="requires_input", nullable=false, options={"default": 0})
     */
    protected $requiresInput = 0;

    /**
     * Applicable in NI
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_ni", nullable=false, options={"default": 0})
     */
    protected $isNi = 0;

    /**
     * Embargo until this date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="publish_from", nullable=true)
     */
    protected $publishFrom;

    /**
     * Version number
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="version_number", nullable=false)
     */
    protected $versionNumber = 0;

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
     * @return LetterIssueVersion
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
     * Set the letter issue
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterIssue $letterIssue new value being set
     *
     * @return LetterIssueVersion
     */
    public function setLetterIssue($letterIssue)
    {
        $this->letterIssue = $letterIssue;

        return $this;
    }

    /**
     * Get the letter issue
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterIssue     */
    public function getLetterIssue()
    {
        return $this->letterIssue;
    }

    /**
     * Set the letter issue type
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterIssueType $letterIssueType new value being set
     *
     * @return LetterIssueVersion
     */
    public function setLetterIssueType($letterIssueType)
    {
        $this->letterIssueType = $letterIssueType;

        return $this;
    }

    /**
     * Get the letter issue type
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterIssueType     */
    public function getLetterIssueType()
    {
        return $this->letterIssueType;
    }

    /**
     * Set the category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\Category $category new value being set
     *
     * @return LetterIssueVersion
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get the category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\Category     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set the sub category
     *
     * @param \Dvsa\Olcs\Api\Entity\System\SubCategory $subCategory new value being set
     *
     * @return LetterIssueVersion
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get the sub category
     *
     * @return \Dvsa\Olcs\Api\Entity\System\SubCategory     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv new value being set
     *
     * @return LetterIssueVersion
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterIssueVersion
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
     * @return LetterIssueVersion
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
     * Set the heading
     *
     * @param string $heading new value being set
     *
     * @return LetterIssueVersion
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Get the heading
     *
     * @return string     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Set the default body content
     *
     * @param array $defaultBodyContent new value being set
     *
     * @return LetterIssueVersion
     */
    public function setDefaultBodyContent($defaultBodyContent)
    {
        $this->defaultBodyContent = $defaultBodyContent;

        return $this;
    }

    /**
     * Get the default body content
     *
     * @return array     */
    public function getDefaultBodyContent()
    {
        return $this->defaultBodyContent;
    }

    /**
     * Set the help text
     *
     * @param string $helpText new value being set
     *
     * @return LetterIssueVersion
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;

        return $this;
    }

    /**
     * Get the help text
     *
     * @return string     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * Set the min length
     *
     * @param int $minLength new value being set
     *
     * @return LetterIssueVersion
     */
    public function setMinLength($minLength)
    {
        $this->minLength = $minLength;

        return $this;
    }

    /**
     * Get the min length
     *
     * @return int     */
    public function getMinLength()
    {
        return $this->minLength;
    }

    /**
     * Set the max length
     *
     * @param int $maxLength new value being set
     *
     * @return LetterIssueVersion
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    /**
     * Get the max length
     *
     * @return int     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * Set the is locked
     *
     * @param bool $isLocked new value being set
     *
     * @return LetterIssueVersion
     */
    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    /**
     * Get the is locked
     *
     * @return bool     */
    public function getIsLocked()
    {
        return $this->isLocked;
    }

    /**
     * Set the requires input
     *
     * @param bool $requiresInput new value being set
     *
     * @return LetterIssueVersion
     */
    public function setRequiresInput($requiresInput)
    {
        $this->requiresInput = $requiresInput;

        return $this;
    }

    /**
     * Get the requires input
     *
     * @return bool     */
    public function getRequiresInput()
    {
        return $this->requiresInput;
    }

    /**
     * Set the is ni
     *
     * @param bool $isNi new value being set
     *
     * @return LetterIssueVersion
     */
    public function setIsNi($isNi)
    {
        $this->isNi = $isNi;

        return $this;
    }

    /**
     * Get the is ni
     *
     * @return bool     */
    public function getIsNi()
    {
        return $this->isNi;
    }

    /**
     * Set the publish from
     *
     * @param \DateTime $publishFrom new value being set
     *
     * @return LetterIssueVersion
     */
    public function setPublishFrom($publishFrom)
    {
        $this->publishFrom = $publishFrom;

        return $this;
    }

    /**
     * Get the publish from
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPublishFrom($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->publishFrom);
        }

        return $this->publishFrom;
    }

    /**
     * Set the version number
     *
     * @param int $versionNumber new value being set
     *
     * @return LetterIssueVersion
     */
    public function setVersionNumber($versionNumber)
    {
        $this->versionNumber = $versionNumber;

        return $this;
    }

    /**
     * Get the version number
     *
     * @return int     */
    public function getVersionNumber()
    {
        return $this->versionNumber;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LetterIssueVersion
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