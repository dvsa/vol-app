<?php

namespace Dvsa\Olcs\Api\Entity;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use JsonSerializable;
use Dvsa\Olcs\Api\Entity\Traits\BundleSerializableTrait;
use Dvsa\Olcs\Api\Entity\Traits\ProcessDateTrait;
use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;
use Dvsa\Olcs\Api\Entity\Traits\CreatedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\ModifiedOnTrait;
use Dvsa\Olcs\Api\Entity\Traits\SoftDeletableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Surrender Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedDate", timeAware=true)
 * @ORM\Table(name="surrender",
 *    indexes={
 *        @ORM\Index(name="fk_signature_type_ref_data_id", columns={"signature_type"}),
 *        @ORM\Index(name="surrender__index_licence", columns={"licence_id"}),
 *        @ORM\Index(name="surrender_created_by_index", columns={"created_by"}),
 *        @ORM\Index(name="surrender_fk_community_licence_document_status_ref_data_id",
     *     columns={"community_licence_document_status"}),
 *        @ORM\Index(name="surrender_fk_digital_signature_id_ref_data_id",
     *     columns={"digital_signature_id"}),
 *        @ORM\Index(name="surrender_fk_last_modified", columns={"last_modified_by"}),
 *        @ORM\Index(name="surrender_licence_document_ref_data_id_fk",
     *     columns={"licence_document_status"}),
 *        @ORM\Index(name="surrender_status_index", columns={"status"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="surrender_id_uindex", columns={"id"})
 *    }
 * )
 */
abstract class AbstractSurrender implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;
    use SoftDeletableTrait;

    /**
     * Community licence document info
     *
     * @var string
     *
     * @ORM\Column(type="text", name="community_licence_document_info", length=65535, nullable=true)
     */
    protected $communityLicenceDocumentInfo;

    /**
     * Community licence document status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="community_licence_document_status",
     *     referencedColumnName="id",
     *     nullable=true)
     */
    protected $communityLicenceDocumentStatus;

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
     * Digital signature
     *
     * @var \Dvsa\Olcs\Api\Entity\DigitalSignature
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DigitalSignature", fetch="LAZY")
     * @ORM\JoinColumn(name="digital_signature_id", referencedColumnName="id", nullable=true)
     */
    protected $digitalSignature;

    /**
     * Disc destroyed
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="disc_destroyed", nullable=true)
     */
    protected $discDestroyed;

    /**
     * Disc lost
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="disc_lost", nullable=true)
     */
    protected $discLost;

    /**
     * Disc lost info
     *
     * @var string
     *
     * @ORM\Column(type="text", name="disc_lost_info", length=65535, nullable=true)
     */
    protected $discLostInfo;

    /**
     * Disc stolen
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="disc_stolen", nullable=true)
     */
    protected $discStolen;

    /**
     * Disc stolen info
     *
     * @var string
     *
     * @ORM\Column(type="text", name="disc_stolen_info", length=65535, nullable=true)
     */
    protected $discStolenInfo;

    /**
     * Ecms checked
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="ecms_checked", nullable=true)
     */
    protected $ecmsChecked;

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
     * Licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id", nullable=false)
     */
    protected $licence;

    /**
     * Licence document info
     *
     * @var string
     *
     * @ORM\Column(type="text", name="licence_document_info", length=65535, nullable=true)
     */
    protected $licenceDocumentInfo;

    /**
     * Licence document status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_document_status", referencedColumnName="id", nullable=true)
     */
    protected $licenceDocumentStatus;

    /**
     * Signature checked
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="signature_checked", nullable=true)
     */
    protected $signatureChecked;

    /**
     * Signature type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="signature_type", referencedColumnName="id", nullable=true)
     */
    protected $signatureType;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * Version
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="version", nullable=true, options={"default": 1})
     * @ORM\Version
     */
    protected $version = 1;

    /**
     * Set the community licence document info
     *
     * @param string $communityLicenceDocumentInfo new value being set
     *
     * @return Surrender
     */
    public function setCommunityLicenceDocumentInfo($communityLicenceDocumentInfo)
    {
        $this->communityLicenceDocumentInfo = $communityLicenceDocumentInfo;

        return $this;
    }

    /**
     * Get the community licence document info
     *
     * @return string
     */
    public function getCommunityLicenceDocumentInfo()
    {
        return $this->communityLicenceDocumentInfo;
    }

    /**
     * Set the community licence document status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $communityLicenceDocumentStatus entity being set as the value
     *
     * @return Surrender
     */
    public function setCommunityLicenceDocumentStatus($communityLicenceDocumentStatus)
    {
        $this->communityLicenceDocumentStatus = $communityLicenceDocumentStatus;

        return $this;
    }

    /**
     * Get the community licence document status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getCommunityLicenceDocumentStatus()
    {
        return $this->communityLicenceDocumentStatus;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Surrender
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
     * Set the digital signature
     *
     * @param \Dvsa\Olcs\Api\Entity\DigitalSignature $digitalSignature entity being set as the value
     *
     * @return Surrender
     */
    public function setDigitalSignature($digitalSignature)
    {
        $this->digitalSignature = $digitalSignature;

        return $this;
    }

    /**
     * Get the digital signature
     *
     * @return \Dvsa\Olcs\Api\Entity\DigitalSignature
     */
    public function getDigitalSignature()
    {
        return $this->digitalSignature;
    }

    /**
     * Set the disc destroyed
     *
     * @param int $discDestroyed new value being set
     *
     * @return Surrender
     */
    public function setDiscDestroyed($discDestroyed)
    {
        $this->discDestroyed = $discDestroyed;

        return $this;
    }

    /**
     * Get the disc destroyed
     *
     * @return int
     */
    public function getDiscDestroyed()
    {
        return $this->discDestroyed;
    }

    /**
     * Set the disc lost
     *
     * @param int $discLost new value being set
     *
     * @return Surrender
     */
    public function setDiscLost($discLost)
    {
        $this->discLost = $discLost;

        return $this;
    }

    /**
     * Get the disc lost
     *
     * @return int
     */
    public function getDiscLost()
    {
        return $this->discLost;
    }

    /**
     * Set the disc lost info
     *
     * @param string $discLostInfo new value being set
     *
     * @return Surrender
     */
    public function setDiscLostInfo($discLostInfo)
    {
        $this->discLostInfo = $discLostInfo;

        return $this;
    }

    /**
     * Get the disc lost info
     *
     * @return string
     */
    public function getDiscLostInfo()
    {
        return $this->discLostInfo;
    }

    /**
     * Set the disc stolen
     *
     * @param int $discStolen new value being set
     *
     * @return Surrender
     */
    public function setDiscStolen($discStolen)
    {
        $this->discStolen = $discStolen;

        return $this;
    }

    /**
     * Get the disc stolen
     *
     * @return int
     */
    public function getDiscStolen()
    {
        return $this->discStolen;
    }

    /**
     * Set the disc stolen info
     *
     * @param string $discStolenInfo new value being set
     *
     * @return Surrender
     */
    public function setDiscStolenInfo($discStolenInfo)
    {
        $this->discStolenInfo = $discStolenInfo;

        return $this;
    }

    /**
     * Get the disc stolen info
     *
     * @return string
     */
    public function getDiscStolenInfo()
    {
        return $this->discStolenInfo;
    }

    /**
     * Set the ecms checked
     *
     * @param boolean $ecmsChecked new value being set
     *
     * @return Surrender
     */
    public function setEcmsChecked($ecmsChecked)
    {
        $this->ecmsChecked = $ecmsChecked;

        return $this;
    }

    /**
     * Get the ecms checked
     *
     * @return boolean
     */
    public function getEcmsChecked()
    {
        return $this->ecmsChecked;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Surrender
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
     * @return Surrender
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
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence entity being set as the value
     *
     * @return Surrender
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;

        return $this;
    }

    /**
     * Get the licence
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * Set the licence document info
     *
     * @param string $licenceDocumentInfo new value being set
     *
     * @return Surrender
     */
    public function setLicenceDocumentInfo($licenceDocumentInfo)
    {
        $this->licenceDocumentInfo = $licenceDocumentInfo;

        return $this;
    }

    /**
     * Get the licence document info
     *
     * @return string
     */
    public function getLicenceDocumentInfo()
    {
        return $this->licenceDocumentInfo;
    }

    /**
     * Set the licence document status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $licenceDocumentStatus entity being set as the value
     *
     * @return Surrender
     */
    public function setLicenceDocumentStatus($licenceDocumentStatus)
    {
        $this->licenceDocumentStatus = $licenceDocumentStatus;

        return $this;
    }

    /**
     * Get the licence document status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getLicenceDocumentStatus()
    {
        return $this->licenceDocumentStatus;
    }

    /**
     * Set the signature checked
     *
     * @param boolean $signatureChecked new value being set
     *
     * @return Surrender
     */
    public function setSignatureChecked($signatureChecked)
    {
        $this->signatureChecked = $signatureChecked;

        return $this;
    }

    /**
     * Get the signature checked
     *
     * @return boolean
     */
    public function getSignatureChecked()
    {
        return $this->signatureChecked;
    }

    /**
     * Set the signature type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $signatureType entity being set as the value
     *
     * @return Surrender
     */
    public function setSignatureType($signatureType)
    {
        $this->signatureType = $signatureType;

        return $this;
    }

    /**
     * Get the signature type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getSignatureType()
    {
        return $this->signatureType;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status entity being set as the value
     *
     * @return Surrender
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Surrender
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
}
