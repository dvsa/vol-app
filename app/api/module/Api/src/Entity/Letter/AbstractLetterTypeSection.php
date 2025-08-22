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
 * LetterTypeSection Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_type_section",
 *    indexes={
 *        @ORM\Index(name="ix_letter_type_section_letter_section_version_id", columns={"letter_section_version_id"}),
 *        @ORM\Index(name="ix_letter_type_section_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_type_section_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
abstract class AbstractLetterTypeSection implements BundleSerializableInterface, JsonSerializable
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
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false)
     */
    protected $displayOrder;

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
     * Letter section version
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_section_version_id", referencedColumnName="id", nullable=false)
     */
    protected $letterSectionVersion;

    /**
     * Letter type
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterType
     *
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterType",
     *     fetch="LAZY",
     *     inversedBy="letterTypeSections"
     * )
     * @ORM\JoinColumn(name="letter_type_id", referencedColumnName="id", nullable=false)
     */
    protected $letterType;

    /**
     * Override content
     *
     * @var array
     *
     * @ORM\Column(type="json", name="override_content", nullable=true)
     */
    protected $overrideContent;

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
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return self
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
     * Set the letter section version
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion $letterSectionVersion entity being set as the value
     *
     * @return self
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
     * Set the letter type
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType entity being set as the value
     *
     * @return self
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
     * Set the override content
     *
     * @param array $overrideContent new value being set
     *
     * @return self
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
}