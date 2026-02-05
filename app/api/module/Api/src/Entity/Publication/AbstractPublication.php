<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Publication;

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
 * AbstractPublication Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="publication",
 *    indexes={
 *        @ORM\Index(name="ix_publication_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_publication_doc_template_id", columns={"doc_template_id"}),
 *        @ORM\Index(name="ix_publication_document_id", columns={"document_id"}),
 *        @ORM\Index(name="ix_publication_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_publication_police_document_id", columns={"police_document_id"}),
 *        @ORM\Index(name="ix_publication_pub_status", columns={"pub_status"}),
 *        @ORM\Index(name="ix_publication_traffic_area_id", columns={"traffic_area_id"})
 *    }
 * )
 */
abstract class AbstractPublication implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to traffic_area
     *
     * @var \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea", fetch="LAZY")
     * @ORM\JoinColumn(name="traffic_area_id", referencedColumnName="id")
     */
    protected $trafficArea;

    /**
     * Foreign Key to document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="document_id", referencedColumnName="id", nullable=true)
     */
    protected $document;

    /**
     * Foreign Key to police version of the document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="police_document_id", referencedColumnName="id", nullable=true)
     */
    protected $policeDocument;

    /**
     * Foreign Key to doc_template
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\DocTemplate
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\DocTemplate", fetch="LAZY")
     * @ORM\JoinColumn(name="doc_template_id", referencedColumnName="id", nullable=true)
     */
    protected $docTemplate;

    /**
     * PubStatus
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="pub_status", referencedColumnName="id")
     */
    protected $pubStatus;

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
     * Publication no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="publication_no", nullable=false)
     */
    protected $publicationNo = 0;

    /**
     * Either A&D or N&P
     *
     * @var string
     *
     * @ORM\Column(type="string", name="pub_type", length=3, nullable=false)
     */
    protected $pubType = '';

    /**
     * Pub date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="pub_date", nullable=true)
     */
    protected $pubDate;

    /**
     * Doc name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="doc_name", length=255, nullable=true)
     */
    protected $docName;

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
     * PublicationLinks
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Publication\PublicationLink", mappedBy="publication")
     */
    protected $publicationLinks;

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
        $this->publicationLinks = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Publication
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
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea new value being set
     *
     * @return Publication
     */
    public function setTrafficArea($trafficArea)
    {
        $this->trafficArea = $trafficArea;

        return $this;
    }

    /**
     * Get the traffic area
     *
     * @return \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }

    /**
     * Set the document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $document new value being set
     *
     * @return Publication
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
     * Set the police document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $policeDocument new value being set
     *
     * @return Publication
     */
    public function setPoliceDocument($policeDocument)
    {
        $this->policeDocument = $policeDocument;

        return $this;
    }

    /**
     * Get the police document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document     */
    public function getPoliceDocument()
    {
        return $this->policeDocument;
    }

    /**
     * Set the doc template
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\DocTemplate $docTemplate new value being set
     *
     * @return Publication
     */
    public function setDocTemplate($docTemplate)
    {
        $this->docTemplate = $docTemplate;

        return $this;
    }

    /**
     * Get the doc template
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\DocTemplate     */
    public function getDocTemplate()
    {
        return $this->docTemplate;
    }

    /**
     * Set the pub status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $pubStatus new value being set
     *
     * @return Publication
     */
    public function setPubStatus($pubStatus)
    {
        $this->pubStatus = $pubStatus;

        return $this;
    }

    /**
     * Get the pub status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getPubStatus()
    {
        return $this->pubStatus;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return Publication
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
     * @return Publication
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
     * Set the publication no
     *
     * @param int $publicationNo new value being set
     *
     * @return Publication
     */
    public function setPublicationNo($publicationNo)
    {
        $this->publicationNo = $publicationNo;

        return $this;
    }

    /**
     * Get the publication no
     *
     * @return int     */
    public function getPublicationNo()
    {
        return $this->publicationNo;
    }

    /**
     * Set the pub type
     *
     * @param string $pubType new value being set
     *
     * @return Publication
     */
    public function setPubType($pubType)
    {
        $this->pubType = $pubType;

        return $this;
    }

    /**
     * Get the pub type
     *
     * @return string     */
    public function getPubType()
    {
        return $this->pubType;
    }

    /**
     * Set the pub date
     *
     * @param \DateTime $pubDate new value being set
     *
     * @return Publication
     */
    public function setPubDate($pubDate)
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    /**
     * Get the pub date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPubDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->pubDate);
        }

        return $this->pubDate;
    }

    /**
     * Set the doc name
     *
     * @param string $docName new value being set
     *
     * @return Publication
     */
    public function setDocName($docName)
    {
        $this->docName = $docName;

        return $this;
    }

    /**
     * Get the doc name
     *
     * @return string     */
    public function getDocName()
    {
        return $this->docName;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Publication
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
     * Set the publication links
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publicationLinks collection being set as the value
     *
     * @return Publication
     */
    public function setPublicationLinks($publicationLinks)
    {
        $this->publicationLinks = $publicationLinks;

        return $this;
    }

    /**
     * Get the publication links
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPublicationLinks()
    {
        return $this->publicationLinks;
    }

    /**
     * Add a publication links
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $publicationLinks collection being added
     *
     * @return Publication
     */
    public function addPublicationLinks($publicationLinks)
    {
        if ($publicationLinks instanceof ArrayCollection) {
            $this->publicationLinks = new ArrayCollection(
                array_merge(
                    $this->publicationLinks->toArray(),
                    $publicationLinks->toArray()
                )
            );
        } elseif (!$this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->add($publicationLinks);
        }

        return $this;
    }

    /**
     * Remove a publication links
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $publicationLinks collection being removed
     *
     * @return Publication
     */
    public function removePublicationLinks($publicationLinks)
    {
        if ($this->publicationLinks->contains($publicationLinks)) {
            $this->publicationLinks->removeElement($publicationLinks);
        }

        return $this;
    }

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
