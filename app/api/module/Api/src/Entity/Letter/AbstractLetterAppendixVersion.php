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
 * AbstractLetterAppendixVersion Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="letter_appendix_version",
 *    indexes={
 *        @ORM\Index(name="ix_letter_appendix_version_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_appendix_version_document_id", columns={"document_id"}),
 *        @ORM\Index(name="ix_letter_appendix_version_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_letter_appendix_version_letter_appendix_id", columns={"letter_appendix_id"}),
 *        @ORM\Index(name="ix_letter_appendix_version_publish_from", columns={"publish_from"})
 *    }
 * )
 */
abstract class AbstractLetterAppendixVersion implements BundleSerializableInterface, JsonSerializable
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
     * LetterAppendix
     *
     * @var \Dvsa\Olcs\Api\Entity\Letter\LetterAppendix
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Letter\LetterAppendix", fetch="LAZY")
     * @ORM\JoinColumn(name="letter_appendix_id", referencedColumnName="id")
     */
    protected $letterAppendix;

    /**
     * FK to document table for PDF
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=true)
     */
    protected $document;

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
     * Display name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="name", length=255, nullable=false)
     */
    protected $name = '';

    /**
     * What this appendix contains
     *
     * @var string
     *
     * @ORM\Column(type="text", name="description", nullable=true)
     */
    protected $description;

    /**
     * Prevent selection
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_locked", nullable=false, options={"default": 0})
     */
    protected $isLocked = 0;

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
     * @return LetterAppendixVersion
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
     * Set the letter appendix
     *
     * @param \Dvsa\Olcs\Api\Entity\Letter\LetterAppendix $letterAppendix new value being set
     *
     * @return LetterAppendixVersion
     */
    public function setLetterAppendix($letterAppendix)
    {
        $this->letterAppendix = $letterAppendix;

        return $this;
    }

    /**
     * Get the letter appendix
     *
     * @return \Dvsa\Olcs\Api\Entity\Letter\LetterAppendix     */
    public function getLetterAppendix()
    {
        return $this->letterAppendix;
    }

    /**
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document new value being set
     *
     * @return LetterAppendixVersion
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get the document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return LetterAppendixVersion
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
     * @return LetterAppendixVersion
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
     * Set the name
     *
     * @param string $name new value being set
     *
     * @return LetterAppendixVersion
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name
     *
     * @return string     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the description
     *
     * @param string $description new value being set
     *
     * @return LetterAppendixVersion
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description
     *
     * @return string     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the is locked
     *
     * @param bool $isLocked new value being set
     *
     * @return LetterAppendixVersion
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
     * Set the publish from
     *
     * @param \DateTime $publishFrom new value being set
     *
     * @return LetterAppendixVersion
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
     * @return LetterAppendixVersion
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
     * @return LetterAppendixVersion
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