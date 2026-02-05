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
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * AbstractPublicationLink Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="publication_link",
 *    indexes={
 *        @ORM\Index(name="ix_publication_link_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_publication_link_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_publication_link_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_publication_link_impounding_id", columns={"impounding_id"}),
 *        @ORM\Index(name="ix_publication_link_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_publication_link_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_publication_link_pi_id", columns={"pi_id"}),
 *        @ORM\Index(name="ix_publication_link_publication_id", columns={"publication_id"}),
 *        @ORM\Index(name="ix_publication_link_publication_section_id", columns={"publication_section_id"}),
 *        @ORM\Index(name="ix_publication_link_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_publication_link_transport_manager_id", columns={"transport_manager_id"}),
 *        @ORM\Index(name="uk_publication_link_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_publication_link_olbs_key_olbs_type", columns={"olbs_key", "olbs_type"})
 *    }
 * )
 */
abstract class AbstractPublicationLink implements BundleSerializableInterface, JsonSerializable, \Stringable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

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
     * Foreign Key to publication
     *
     * @var \Dvsa\Olcs\Api\Entity\Publication\Publication
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Publication\Publication", fetch="LAZY")
     * @ORM\JoinColumn(name="publication_id", referencedColumnName="id")
     */
    protected $publication;

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
     * Foreign Key to transport_manager
     *
     * @var \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Tm\TransportManager", fetch="LAZY")
     * @ORM\JoinColumn(name="transport_manager_id", referencedColumnName="id", nullable=true)
     */
    protected $transportManager;

    /**
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=true)
     */
    protected $licence;

    /**
     * Foreign Key to application
     *
     * @var \Dvsa\Olcs\Api\Entity\Application\Application
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Application\Application", fetch="LAZY")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=true)
     */
    protected $application;

    /**
     * Foreign Key to pi
     *
     * @var \Dvsa\Olcs\Api\Entity\Pi\Pi
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Pi\Pi", fetch="LAZY")
     * @ORM\JoinColumn(name="pi_id", referencedColumnName="id", nullable=true)
     */
    protected $pi;

    /**
     * Foreign Key to bus_reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg", fetch="LAZY")
     * @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id", nullable=true)
     */
    protected $busReg;

    /**
     * Foreign key to impounding
     *
     * @var \Dvsa\Olcs\Api\Entity\Cases\Impounding
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Cases\Impounding", fetch="LAZY")
     * @ORM\JoinColumn(name="impounding_id", referencedColumnName="id", nullable=true)
     */
    protected $impounding;

    /**
     * Foreign Key to publication_section
     *
     * @var \Dvsa\Olcs\Api\Entity\Publication\PublicationSection
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Publication\PublicationSection", fetch="LAZY")
     * @ORM\JoinColumn(name="publication_section_id", referencedColumnName="id")
     */
    protected $publicationSection;

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
     * Publish after date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="publish_after_date", nullable=true)
     */
    protected $publishAfterDate;

    /**
     * Text1
     *
     * @var string
     *
     * @ORM\Column(type="text", name="text1", nullable=true)
     */
    protected $text1;

    /**
     * Text2
     *
     * @var string
     *
     * @ORM\Column(type="text", name="text2", nullable=true)
     */
    protected $text2;

    /**
     * Text3
     *
     * @var string
     *
     * @ORM\Column(type="text", name="text3", nullable=true)
     */
    protected $text3;

    /**
     * Orig pub date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="orig_pub_date", nullable=true)
     */
    protected $origPubDate;

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
     * Used to map FKs during ETL. Can be dropped safely when OLBS decommissioned
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * used to differntiate source of data during ETL when one OLCS table relates to many OLBS. Can be dropped when fully live
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=32, nullable=true)
     */
    protected $olbsType;

    /**
     * PoliceDatas
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData", mappedBy="publicationLink", cascade={"persist"}, orphanRemoval=true)
     */
    protected $policeDatas;

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
        $this->policeDatas = new ArrayCollection();
    }


    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return PublicationLink
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
     * Set the publication
     *
     * @param \Dvsa\Olcs\Api\Entity\Publication\Publication $publication new value being set
     *
     * @return PublicationLink
     */
    public function setPublication($publication)
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * Get the publication
     *
     * @return \Dvsa\Olcs\Api\Entity\Publication\Publication     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Set the traffic area
     *
     * @param \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea $trafficArea new value being set
     *
     * @return PublicationLink
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
     * Set the transport manager
     *
     * @param \Dvsa\Olcs\Api\Entity\Tm\TransportManager $transportManager new value being set
     *
     * @return PublicationLink
     */
    public function setTransportManager($transportManager)
    {
        $this->transportManager = $transportManager;

        return $this;
    }

    /**
     * Get the transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager     */
    public function getTransportManager()
    {
        return $this->transportManager;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return PublicationLink
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the application
     *
     * @param \Dvsa\Olcs\Api\Entity\Application\Application $application new value being set
     *
     * @return PublicationLink
     */
    public function setApplication($application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the application
     *
     * @return \Dvsa\Olcs\Api\Entity\Application\Application     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the pi
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi $pi new value being set
     *
     * @return PublicationLink
     */
    public function setPi($pi)
    {
        $this->pi = $pi;

        return $this;
    }

    /**
     * Get the pi
     *
     * @return \Dvsa\Olcs\Api\Entity\Pi\Pi     */
    public function getPi()
    {
        return $this->pi;
    }

    /**
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg new value being set
     *
     * @return PublicationLink
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;

        return $this;
    }

    /**
     * Get the bus reg
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set the impounding
     *
     * @param \Dvsa\Olcs\Api\Entity\Cases\Impounding $impounding new value being set
     *
     * @return PublicationLink
     */
    public function setImpounding($impounding)
    {
        $this->impounding = $impounding;

        return $this;
    }

    /**
     * Get the impounding
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Impounding     */
    public function getImpounding()
    {
        return $this->impounding;
    }

    /**
     * Set the publication section
     *
     * @param \Dvsa\Olcs\Api\Entity\Publication\PublicationSection $publicationSection new value being set
     *
     * @return PublicationLink
     */
    public function setPublicationSection($publicationSection)
    {
        $this->publicationSection = $publicationSection;

        return $this;
    }

    /**
     * Get the publication section
     *
     * @return \Dvsa\Olcs\Api\Entity\Publication\PublicationSection     */
    public function getPublicationSection()
    {
        return $this->publicationSection;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return PublicationLink
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
     * @return PublicationLink
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
     * Set the publish after date
     *
     * @param \DateTime $publishAfterDate new value being set
     *
     * @return PublicationLink
     */
    public function setPublishAfterDate($publishAfterDate)
    {
        $this->publishAfterDate = $publishAfterDate;

        return $this;
    }

    /**
     * Get the publish after date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getPublishAfterDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->publishAfterDate);
        }

        return $this->publishAfterDate;
    }

    /**
     * Set the text1
     *
     * @param string $text1 new value being set
     *
     * @return PublicationLink
     */
    public function setText1($text1)
    {
        $this->text1 = $text1;

        return $this;
    }

    /**
     * Get the text1
     *
     * @return string     */
    public function getText1()
    {
        return $this->text1;
    }

    /**
     * Set the text2
     *
     * @param string $text2 new value being set
     *
     * @return PublicationLink
     */
    public function setText2($text2)
    {
        $this->text2 = $text2;

        return $this;
    }

    /**
     * Get the text2
     *
     * @return string     */
    public function getText2()
    {
        return $this->text2;
    }

    /**
     * Set the text3
     *
     * @param string $text3 new value being set
     *
     * @return PublicationLink
     */
    public function setText3($text3)
    {
        $this->text3 = $text3;

        return $this;
    }

    /**
     * Get the text3
     *
     * @return string     */
    public function getText3()
    {
        return $this->text3;
    }

    /**
     * Set the orig pub date
     *
     * @param \DateTime $origPubDate new value being set
     *
     * @return PublicationLink
     */
    public function setOrigPubDate($origPubDate)
    {
        $this->origPubDate = $origPubDate;

        return $this;
    }

    /**
     * Get the orig pub date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime     */
    public function getOrigPubDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->origPubDate);
        }

        return $this->origPubDate;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return PublicationLink
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
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return PublicationLink
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return PublicationLink
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the police datas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $policeDatas collection being set as the value
     *
     * @return PublicationLink
     */
    public function setPoliceDatas($policeDatas)
    {
        $this->policeDatas = $policeDatas;

        return $this;
    }

    /**
     * Get the police datas
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPoliceDatas()
    {
        return $this->policeDatas;
    }

    /**
     * Add a police datas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $policeDatas collection being added
     *
     * @return PublicationLink
     */
    public function addPoliceDatas($policeDatas)
    {
        if ($policeDatas instanceof ArrayCollection) {
            $this->policeDatas = new ArrayCollection(
                array_merge(
                    $this->policeDatas->toArray(),
                    $policeDatas->toArray()
                )
            );
        } elseif (!$this->policeDatas->contains($policeDatas)) {
            $this->policeDatas->add($policeDatas);
        }

        return $this;
    }

    /**
     * Remove a police datas
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $policeDatas collection being removed
     *
     * @return PublicationLink
     */
    public function removePoliceDatas($policeDatas)
    {
        if ($this->policeDatas->contains($policeDatas)) {
            $this->policeDatas->removeElement($policeDatas);
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
