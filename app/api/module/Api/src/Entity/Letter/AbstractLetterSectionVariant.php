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
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractLetterSectionVariant Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="letter_section_variant",
 *    indexes={
 *        @ORM\Index(name="ix_letter_section_variant_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_section_variant_current_version_id", columns={"current_version_id"}),
 *        @ORM\Index(name="ix_letter_section_variant_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_letter_section_variant_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_section_variant_letter_choice_id", columns={"letter_choice_id"}),
 *        @ORM\Index(name="ix_letter_section_variant_letter_section_id", columns={"letter_section_id"}),
 *        @ORM\Index(name="ix_letter_section_variant_organisation_type", columns={"organisation_type"}),
 *        @ORM\Index(name="uk_letter_section_variant_conditions", columns={"letter_section_id", "goods_or_psv", "is_variation", "is_ni", "organisation_type", "letter_choice_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_letter_section_variant_conditions", columns={"letter_section_id", "goods_or_psv", "is_variation", "is_ni", "organisation_type", "letter_choice_id"})
 *    }
 * )
 */
abstract class AbstractLetterSectionVariant implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * LetterSection
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterSection
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterSection", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_section_id", referencedColumnName="id")
     */
    protected $letterSection;

    /**
     * Points to latest version for this variant
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion", fetch="LAZY")
     * @ORM\JoinColumn(name="current_version_id", referencedColumnName="id", nullable=true)
     */
    protected $currentVersion;

    /**
     * FK to ref_data lcat_gv/lcat_psv. NULL = any
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="goods_or_psv", referencedColumnName="id", nullable=true)
     */
    protected $goodsOrPsv;

    /**
     * FK to ref_data org_t_st/org_t_rc/org_t_llp/org_t_p. NULL = any
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="organisation_type", referencedColumnName="id", nullable=true)
     */
    protected $organisationType;

    /**
     * FK to letter_choice. NULL = not conditional on a choice
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterChoice
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterChoice", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_choice_id", referencedColumnName="id", nullable=true)
     */
    protected $letterChoice;

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
     * NULL = any, 0 = new application, 1 = variation
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_variation", nullable=true)
     */
    protected $isVariation;

    /**
     * NULL = any, 0 = GB, 1 = NI
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_ni", nullable=true)
     */
    protected $isNi;

    /**
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false, options={"default": 0})
     */
    protected $displayOrder = 0;

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
     * @return LetterSectionVariant
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
     * Set the letter section
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterSection $letterSection new value being set
     *
     * @return LetterSectionVariant
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
     * Set the current version
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion $currentVersion new value being set
     *
     * @return LetterSectionVariant
     */
    public function setCurrentVersion($currentVersion)
    {
        $this->currentVersion = $currentVersion;

        return $this;
    }

    /**
     * Get the current version
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion
     */
    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    /**
     * Set the goods or psv
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $goodsOrPsv new value being set
     *
     * @return LetterSectionVariant
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
     * Set the organisation type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $organisationType new value being set
     *
     * @return LetterSectionVariant
     */
    public function setOrganisationType($organisationType)
    {
        $this->organisationType = $organisationType;

        return $this;
    }

    /**
     * Get the organisation type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getOrganisationType()
    {
        return $this->organisationType;
    }

    /**
     * Set the letter choice
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterChoice $letterChoice new value being set
     *
     * @return LetterSectionVariant
     */
    public function setLetterChoice($letterChoice)
    {
        $this->letterChoice = $letterChoice;

        return $this;
    }

    /**
     * Get the letter choice
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterChoice
     */
    public function getLetterChoice()
    {
        return $this->letterChoice;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterSectionVariant
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy new value being set
     *
     * @return LetterSectionVariant
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
     * Set the is variation
     *
     * @param bool $isVariation new value being set
     *
     * @return LetterSectionVariant
     */
    public function setIsVariation($isVariation)
    {
        $this->isVariation = $isVariation;

        return $this;
    }

    /**
     * Get the is variation
     *
     * @return bool
     */
    public function getIsVariation()
    {
        return $this->isVariation;
    }

    /**
     * Set the is ni
     *
     * @param bool $isNi new value being set
     *
     * @return LetterSectionVariant
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
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return LetterSectionVariant
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * Get the display order
     *
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LetterSectionVariant
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
     * Get bundle data
     */
    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
