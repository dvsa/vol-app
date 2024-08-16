<?php

namespace Dvsa\Olcs\Api\Entity\Ebsr;

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
 * TxcInbox Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="txc_inbox",
 *    indexes={
 *        @ORM\Index(name="ix_txc_inbox_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_txc_inbox_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_txc_inbox_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_txc_inbox_local_authority_id", columns={"local_authority_id"}),
 *        @ORM\Index(name="ix_txc_inbox_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_txc_inbox_pdf_document_id", columns={"pdf_document_id"}),
 *        @ORM\Index(name="ix_txc_inbox_route_document_id", columns={"route_document_id"}),
 *        @ORM\Index(name="ix_txc_inbox_zip_document_id", columns={"zip_document_id"})
 *    }
 * )
 */
abstract class AbstractTxcInbox implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Bus reg
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\BusReg
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Bus\BusReg",
     *     fetch="LAZY",
     *     inversedBy="txcInboxs"
     * )
     * @ORM\JoinColumn(name="bus_reg_id", referencedColumnName="id", nullable=false)
     */
    protected $busReg;

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
     * File read
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="file_read", nullable=false, options={"default": 0})
     */
    protected $fileRead = 0;

    /**
     * Identifier - Id
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

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
     * Local authority
     *
     * @var \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Bus\LocalAuthority", fetch="LAZY")
     * @ORM\JoinColumn(name="local_authority_id", referencedColumnName="id", nullable=true)
     */
    protected $localAuthority;

    /**
     * Organisation
     *
     * @var \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Organisation\Organisation", fetch="LAZY")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $organisation;

    /**
     * Pdf document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="pdf_document_id", referencedColumnName="id", nullable=true)
     */
    protected $pdfDocument;

    /**
     * Route document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="route_document_id", referencedColumnName="id", nullable=true)
     */
    protected $routeDocument;

    /**
     * Variation no
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="variation_no", nullable=false)
     */
    protected $variationNo;

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
     * Zip document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="zip_document_id", referencedColumnName="id", nullable=true)
     */
    protected $zipDocument;

    /**
     * Set the bus reg
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\BusReg $busReg entity being set as the value
     *
     * @return TxcInbox
     */
    public function setBusReg($busReg)
    {
        $this->busReg = $busReg;

        return $this;
    }

    /**
     * Get the bus reg
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\BusReg
     */
    public function getBusReg()
    {
        return $this->busReg;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return TxcInbox
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
     * Set the file read
     *
     * @param string $fileRead new value being set
     *
     * @return TxcInbox
     */
    public function setFileRead($fileRead)
    {
        $this->fileRead = $fileRead;

        return $this;
    }

    /**
     * Get the file read
     *
     * @return string
     */
    public function getFileRead()
    {
        return $this->fileRead;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return TxcInbox
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
     * Set the last modified by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $lastModifiedBy entity being set as the value
     *
     * @return TxcInbox
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
     * Set the local authority
     *
     * @param \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority $localAuthority entity being set as the value
     *
     * @return TxcInbox
     */
    public function setLocalAuthority($localAuthority)
    {
        $this->localAuthority = $localAuthority;

        return $this;
    }

    /**
     * Get the local authority
     *
     * @return \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority
     */
    public function getLocalAuthority()
    {
        return $this->localAuthority;
    }

    /**
     * Set the organisation
     *
     * @param \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation entity being set as the value
     *
     * @return TxcInbox
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get the organisation
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set the pdf document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $pdfDocument entity being set as the value
     *
     * @return TxcInbox
     */
    public function setPdfDocument($pdfDocument)
    {
        $this->pdfDocument = $pdfDocument;

        return $this;
    }

    /**
     * Get the pdf document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getPdfDocument()
    {
        return $this->pdfDocument;
    }

    /**
     * Set the route document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $routeDocument entity being set as the value
     *
     * @return TxcInbox
     */
    public function setRouteDocument($routeDocument)
    {
        $this->routeDocument = $routeDocument;

        return $this;
    }

    /**
     * Get the route document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getRouteDocument()
    {
        return $this->routeDocument;
    }

    /**
     * Set the variation no
     *
     * @param int $variationNo new value being set
     *
     * @return TxcInbox
     */
    public function setVariationNo($variationNo)
    {
        $this->variationNo = $variationNo;

        return $this;
    }

    /**
     * Get the variation no
     *
     * @return int
     */
    public function getVariationNo()
    {
        return $this->variationNo;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return TxcInbox
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
     * Set the zip document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $zipDocument entity being set as the value
     *
     * @return TxcInbox
     */
    public function setZipDocument($zipDocument)
    {
        $this->zipDocument = $zipDocument;

        return $this;
    }

    /**
     * Get the zip document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getZipDocument()
    {
        return $this->zipDocument;
    }
}
