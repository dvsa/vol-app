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
 * AbstractLetterInstanceAppendix Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_instance_appendix",
 *    indexes={
 *        @ORM\Index(name="ix_letter_instance_appendix_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_instance_appendix_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_instance_appendix_letter_appendix_version_id", columns={"letter_appendix_version_id"}),
 *        @ORM\Index(name="ix_letter_instance_appendix_letter_instance_id", columns={"letter_instance_id"})
 *    }
 * )
 */
abstract class AbstractLetterInstanceAppendix implements BundleSerializableInterface, JsonSerializable
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
     * LetterInstance
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterInstance
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterInstance", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_instance_id", referencedColumnName="id")
     */
    protected $letterInstance;

    /**
     * LetterAppendixVersion
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_appendix_version_id", referencedColumnName="id")
     */
    protected $letterAppendixVersion;

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
     * @return LetterInstanceAppendix
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
     * Set the letter instance
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterInstance $letterInstance new value being set
     *
     * @return LetterInstanceAppendix
     */
    public function setLetterInstance($letterInstance)
    {
        $this->letterInstance = $letterInstance;

        return $this;
    }

    /**
     * Get the letter instance
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterInstance     */
    public function getLetterInstance()
    {
        return $this->letterInstance;
    }

    /**
     * Set the letter appendix version
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion $letterAppendixVersion new value being set
     *
     * @return LetterInstanceAppendix
     */
    public function setLetterAppendixVersion($letterAppendixVersion)
    {
        $this->letterAppendixVersion = $letterAppendixVersion;

        return $this;
    }

    /**
     * Get the letter appendix version
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion     */
    public function getLetterAppendixVersion()
    {
        return $this->letterAppendixVersion;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterInstanceAppendix
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
     * @return LetterInstanceAppendix
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
     * @return LetterInstanceAppendix
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return LetterInstanceAppendix
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