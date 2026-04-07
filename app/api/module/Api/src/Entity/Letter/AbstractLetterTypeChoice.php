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
 * AbstractLetterTypeChoice Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_type_choice",
 *    indexes={
 *        @ORM\Index(name="ix_letter_type_choice_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_type_choice_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_type_choice_letter_choice_id", columns={"letter_choice_id"}),
 *        @ORM\Index(name="uk_letter_type_choice", columns={"letter_type_id", "letter_choice_id"}),
 *        @ORM\Index(name="IDX_89D7E87430450394", columns={"letter_type_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_letter_type_choice", columns={"letter_type_id", "letter_choice_id"})
 *    }
 * )
 */
abstract class AbstractLetterTypeChoice implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * LetterType
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterType
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterType", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_type_id", referencedColumnName="id")
     */
    protected $letterType;

    /**
     * LetterChoice
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterChoice
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterChoice", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_choice_id", referencedColumnName="id")
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
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false, options={"default": 0})
     */
    protected $displayOrder = 0;

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
     * @return LetterTypeChoice
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
     * Set the letter type
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterType $letterType new value being set
     *
     * @return LetterTypeChoice
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
     * Set the letter choice
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterChoice $letterChoice new value being set
     *
     * @return LetterTypeChoice
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
     * @return LetterTypeChoice
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
     * @return LetterTypeChoice
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
     * @return LetterTypeChoice
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
     * Get bundle data
     */
    #[\Override]
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
