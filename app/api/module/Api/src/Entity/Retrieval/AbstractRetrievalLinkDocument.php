<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Retrieval;

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
 * AbstractRetrievalLinkDocument Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="retrieval_link_document",
 *    indexes={
 *        @ORM\Index(name="ix_retrieval_link_document_retrieval_link_id", columns={"retrieval_link_id"}),
 *        @ORM\Index(name="ix_retrieval_link_document_document_id", columns={"document_id"}),
 *        @ORM\Index(name="ix_retrieval_link_document_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_retrieval_link_document_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="uk_retrieval_link_document_member_ref", columns={"member_ref"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_retrieval_link_document_member_ref", columns={"member_ref"})
 *    }
 * )
 */
abstract class AbstractRetrievalLinkDocument implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to retrieval_link
     *
     * @var \Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink", fetch="LAZY")
     * @ORM\JoinColumn(name="retrieval_link_id", referencedColumnName="id")
     */
    protected $retrievalLink;

    /**
     * Foreign Key to document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id")
     */
    protected $document;

    /**
     * Member ref
     *
     * @var string
     *
     * @ORM\Column(type="string", name="member_ref", length=64, nullable=false)
     */
    protected $memberRef = '';

    /**
     * Display filename
     *
     * @var string
     *
     * @ORM\Column(type="string", name="display_filename", length=255, nullable=false)
     */
    protected $displayFilename = '';

    /**
     * Display order
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="display_order", nullable=false, options={"default": 0})
     */
    protected $displayOrder = 0;

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
     * @return RetrievalLinkDocument
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
     * Set the retrieval link
     *
     * @param \Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink $retrievalLink new value being set
     *
     * @return RetrievalLinkDocument
     */
    public function setRetrievalLink($retrievalLink)
    {
        $this->retrievalLink = $retrievalLink;

        return $this;
    }

    /**
     * Get the retrieval link
     *
     * @return \Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink
     */
    public function getRetrievalLink()
    {
        return $this->retrievalLink;
    }

    /**
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document new value being set
     *
     * @return RetrievalLinkDocument
     */
    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get the document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set the member ref
     *
     * @param string $memberRef new value being set
     *
     * @return RetrievalLinkDocument
     */
    public function setMemberRef($memberRef)
    {
        $this->memberRef = $memberRef;

        return $this;
    }

    /**
     * Get the member ref
     *
     * @return string
     */
    public function getMemberRef()
    {
        return $this->memberRef;
    }

    /**
     * Set the display filename
     *
     * @param string $displayFilename new value being set
     *
     * @return RetrievalLinkDocument
     */
    public function setDisplayFilename($displayFilename)
    {
        $this->displayFilename = $displayFilename;

        return $this;
    }

    /**
     * Get the display filename
     *
     * @return string
     */
    public function getDisplayFilename()
    {
        return $this->displayFilename;
    }

    /**
     * Set the display order
     *
     * @param int $displayOrder new value being set
     *
     * @return RetrievalLinkDocument
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
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return RetrievalLinkDocument
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
     * @return RetrievalLinkDocument
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
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return RetrievalLinkDocument
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
