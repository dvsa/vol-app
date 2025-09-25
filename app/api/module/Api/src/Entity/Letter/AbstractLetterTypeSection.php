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
abstract class AbstractLetterTypeSection implements BundleSerializableInterface, JsonSerializable
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
     * @ORM\Column(type="integer", name="letter_type_id", nullable=false)
     */
    protected $letter_type_id = 0;

    /**
     * Primary key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="letter_section_version_id", nullable=false)
     */
    protected $letter_section_version_id = 0;

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
     * Set the letter_type_id
     *
     * @param int $letter_type_id new value being set
     *
     * @return LetterTypeSection
     */
    public function setLetter_type_id($letter_type_id)
    {
        $this->letter_type_id = $letter_type_id;

        return $this;
    }

    /**
     * Get the letter_type_id
     *
     * @return int     */
    public function getLetter_type_id()
    {
        return $this->letter_type_id;
    }

    /**
     * Set the letter_section_version_id
     *
     * @param int $letter_section_version_id new value being set
     *
     * @return LetterTypeSection
     */
    public function setLetter_section_version_id($letter_section_version_id)
    {
        $this->letter_section_version_id = $letter_section_version_id;

        return $this;
    }

    /**
     * Get the letter_section_version_id
     *
     * @return int     */
    public function getLetter_section_version_id()
    {
        return $this->letter_section_version_id;
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
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
     * @return int     */
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
     * @return array     */
    public function getOverrideContent()
    {
        return $this->overrideContent;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}