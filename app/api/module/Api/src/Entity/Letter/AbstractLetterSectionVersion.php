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
 * LetterSectionVersion Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_section_version",
 *    indexes={
 *        @ORM\Index(name="ix_letter_section_version_letter_section_id", columns={"letter_section_id"}),
 *        @ORM\Index(name="ix_letter_section_version_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_section_version_type_goods_or_psv", columns={"section_type", "goods_or_psv"}),
 *        @ORM\Index(name="ix_letter_section_version_section_type", columns={"section_type"}),
 *        @ORM\Index(name="ix_letter_section_version_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_letter_section_version_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractLetterSectionVersion implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

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
     * Default content
     *
     * @var array
     *
     * @ORM\Column(type="json", name="default_content", nullable=true)
     */
    protected $defaultContent;

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
     * Letter section
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterSection
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterSection",
     *     fetch="LAZY",
     *     inversedBy="versions"
     * )
     * @ORM\JoinColumn(name="letter_section_id", referencedColumnName="id", nullable=false)
     */
    protected $letterSection;

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
     * Name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=255, nullable=false)
     */
    protected $name;

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
     * Section type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="section_type", referencedColumnName="id", nullable=false)
     */
    protected $sectionType;

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
     * Set the default content
     *
     * @param array $defaultContent new value being set
     *
     * @return self
     */
    public function setDefaultContent($defaultContent)
    {
        $this->defaultContent = $defaultContent;

        return $this;
    }

    /**
     * Get the default content
     *
     * @return array
     */
    public function getDefaultContent()
    {
        return $this->defaultContent;
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
     * Set the letter section
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterSection $letterSection entity being set as the value
     *
     * @return self
     */
    public function setLetterSection($letterSection)
    {
        $this->letterSection = $letterSection;

        return $this;
    }

    /**
     * Get the letter section
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterSection
     */
    public function getLetterSection()
    {
        return $this->letterSection;
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
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set the section type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $sectionType entity being set as the value
     *
     * @return self
     */
    public function setSectionType($sectionType)
    {
        $this->sectionType = $sectionType;

        return $this;
    }

    /**
     * Get the section type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getSectionType()
    {
        return $this->sectionType;
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