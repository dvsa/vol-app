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
 * AbstractLetterChoice Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_choice",
 *    indexes={
 *        @ORM\Index(name="ix_letter_choice_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_choice_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_letter_choice_key", columns={"choice_key"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_letter_choice_key", columns={"choice_key"})
 *    }
 * )
 */
abstract class AbstractLetterChoice implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Business identifier e.g. time_limited_interim
     *
     * @var string
     *
     * @ORM\Column(type="string", name="choice_key", length=100, nullable=false)
     */
    protected $choiceKey = '';

    /**
     * Display label for the modal
     *
     * @var string
     *
     * @ORM\Column(type="string", name="label", length=255, nullable=false)
     */
    protected $label = '';

    /**
     * UI grouping label
     *
     * @var string
     *
     * @ORM\Column(type="string", name="group_label", length=255, nullable=false, options={"default": "Other letter choices"})
     */
    protected $groupLabel = 'Other letter choices';

    /**
     * checkbox or radio
     *
     * @var string
     *
     * @ORM\Column(type="string", name="input_type", length=20, nullable=false, options={"default": "checkbox"})
     */
    protected $inputType = 'checkbox';

    /**
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false, options={"default": 0})
     */
    protected $displayOrder = 0;

    /**
     * Is active
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_active", nullable=false, options={"default": 1})
     */
    protected $isActive = 1;

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
     * @return LetterChoice
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterChoice
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
     * @return LetterChoice
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
     * Set the choice key
     *
     * @param string $choiceKey new value being set
     *
     * @return LetterChoice
     */
    public function setChoiceKey($choiceKey)
    {
        $this->choiceKey = $choiceKey;

        return $this;
    }

    /**
     * Get the choice key
     *
     * @return string
     */
    public function getChoiceKey()
    {
        return $this->choiceKey;
    }

    /**
     * Set the label
     *
     * @param string $label new value being set
     *
     * @return LetterChoice
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the group label
     *
     * @param string $groupLabel new value being set
     *
     * @return LetterChoice
     */
    public function setGroupLabel($groupLabel)
    {
        $this->groupLabel = $groupLabel;

        return $this;
    }

    /**
     * Get the group label
     *
     * @return string
     */
    public function getGroupLabel()
    {
        return $this->groupLabel;
    }

    /**
     * Set the input type
     *
     * @param string $inputType new value being set
     *
     * @return LetterChoice
     */
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;

        return $this;
    }

    /**
     * Get the input type
     *
     * @return string
     */
    public function getInputType()
    {
        return $this->inputType;
    }

    /**
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return LetterChoice
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
     * Set the is active
     *
     * @param bool $isActive new value being set
     *
     * @return LetterChoice
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get the is active
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LetterChoice
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
