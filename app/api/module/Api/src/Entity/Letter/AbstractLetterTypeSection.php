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
 * AbstractLetterTypeSection Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_type_section",
 *    indexes={
 *        @ORM\Index(name="ix_letter_type_section_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_type_section_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_type_section_letter_section_version_id", columns={"letter_section_version_id"}),
 *        @ORM\Index(name="IDX_6452411030450394", columns={"letter_type_id"})
 *    }
 * )
 */
abstract class AbstractLetterTypeSection implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * LetterType
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterType
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterType", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_type_id", referencedColumnName="id")
     */
    protected $letterType;

    /**
     * LetterSectionVersion
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_section_version_id", referencedColumnName="id")
     */
    protected $letterSectionVersion;

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
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false)
     */
    protected $displayOrder = 0;

    /**
     * Override default section content
     *
     * @var array
     *
     * @ORM\Column(type="json", name="override_content", nullable=true)
     */
    protected $overrideContent;

    /**
     * JSON filter config for issues meta-section
     *
     * @var array
     *
     * @ORM\Column(type="json", name="issue_filter", nullable=true)
     */
    protected $issueFilter;

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
     * Set the letter type
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType new value being set
     *
     * @return LetterTypeSection
     */
    public function setLetterType($letterType)
    {
        $this->letterType = $letterType;

        return $this;
    }

    /**
     * Get the letter type
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterType
     */
    public function getLetterType()
    {
        return $this->letterType;
    }

    /**
     * Set the letter section version
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion $letterSectionVersion new value being set
     *
     * @return LetterTypeSection
     */
    public function setLetterSectionVersion($letterSectionVersion)
    {
        $this->letterSectionVersion = $letterSectionVersion;

        return $this;
    }

    /**
     * Get the letter section version
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion
     */
    public function getLetterSectionVersion()
    {
        return $this->letterSectionVersion;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterTypeSection
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
     * @return LetterTypeSection
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
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return LetterTypeSection
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
     * Set the override content
     *
     * @param array $overrideContent new value being set
     *
     * @return LetterTypeSection
     */
    public function setOverrideContent($overrideContent)
    {
        $this->overrideContent = $overrideContent;

        return $this;
    }

    /**
     * Get the override content
     *
     * @return array
     */
    public function getOverrideContent()
    {
        return $this->overrideContent;
    }

    /**
     * Set the issue filter
     *
     * @param array $issueFilter new value being set
     *
     * @return LetterTypeSection
     */
    public function setIssueFilter($issueFilter)
    {
        $this->issueFilter = $issueFilter;

        return $this;
    }

    /**
     * Get the issue filter
     *
     * @return array
     */
    public function getIssueFilter()
    {
        return $this->issueFilter;
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
