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
 * AbstractLetterTypeAppendix Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_type_appendix",
 *    indexes={
 *        @ORM\Index(name="ix_letter_type_appendix_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_type_appendix_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_type_appendix_letter_appendix_version_id", columns={"letter_appendix_version_id"}),
 *        @ORM\Index(name="IDX_9C765E0130450394", columns={"letter_type_id"})
 *    }
 * )
 */
abstract class AbstractLetterTypeAppendix implements BundleSerializableInterface, JsonSerializable
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
     * @ORM\Column(type="integer", name="letter_appendix_version_id", nullable=false)
     */
    protected $letter_appendix_version_id = 0;

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
     * Order for this letter type
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false)
     */
    protected $displayOrder = 0;

    /**
     * Always include vs selectable
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_mandatory", nullable=false, options={"default": 0})
     */
    protected $isMandatory = 0;

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
     * Set the letter_type_id
     *
     * @param int $letter_type_id new value being set
     *
     * @return LetterTypeAppendix
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
     * Set the letter_appendix_version_id
     *
     * @param int $letter_appendix_version_id new value being set
     *
     * @return LetterTypeAppendix
     */
    public function setLetter_appendix_version_id($letter_appendix_version_id)
    {
        $this->letter_appendix_version_id = $letter_appendix_version_id;

        return $this;
    }

    /**
     * Get the letter_appendix_version_id
     *
     * @return int     */
    public function getLetter_appendix_version_id()
    {
        return $this->letter_appendix_version_id;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterTypeAppendix
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
     * @return LetterTypeAppendix
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
     * @return LetterTypeAppendix
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
     * Set the is mandatory
     *
     * @param bool $isMandatory new value being set
     *
     * @return LetterTypeAppendix
     */
    public function setIsMandatory($isMandatory)
    {
        $this->isMandatory = $isMandatory;

        return $this;
    }

    /**
     * Get the is mandatory
     *
     * @return bool     */
    public function getIsMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LetterTypeAppendix
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