<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Licence;

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
 * AbstractContinuationDetail Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="continuation_detail",
 *    indexes={
 *        @ORM\Index(name="fk_continuation_detail_digital_signature_id_digital_signature_id", columns={"digital_signature_id"}),
 *        @ORM\Index(name="fk_continuation_detail_signature_type_ref_data_id", columns={"signature_type"}),
 *        @ORM\Index(name="ix_continuation_detail_checklist_document_id", columns={"checklist_document_id"}),
 *        @ORM\Index(name="ix_continuation_detail_continuation_id", columns={"continuation_id"}),
 *        @ORM\Index(name="ix_continuation_detail_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_continuation_detail_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_continuation_detail_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_continuation_detail_status", columns={"status"}),
 *        @ORM\Index(name="uk_continuation_detail_licence_id_continuation_id", columns={"licence_id", "continuation_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_continuation_detail_licence_id_continuation_id", columns={"licence_id", "continuation_id"})
 *    }
 * )
 */
abstract class AbstractContinuationDetail implements BundleSerializableInterface, JsonSerializable
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
     * Foreign Key to continuation
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Continuation
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Continuation", fetch="LAZY")
     * @ORM\JoinColumn(name="continuation_id", referencedColumnName="id")
     */
    protected $continuation;

    /**
     * Foreign Key to licence
     *
     * @var \Dvsa\Olcs\Api\Entity\Licence\Licence
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Licence\Licence", fetch="LAZY")
     * @ORM\JoinColumn(name="licence_id", referencedColumnName="id")
     */
    protected $licence;

    /**
     * ChecklistDocument
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="checklist_document_id", referencedColumnName="id", nullable=true)
     */
    protected $checklistDocument;

    /**
     * Status
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="status", referencedColumnName="id", nullable=true)
     */
    protected $status;

    /**
     * SignatureType
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="signature_type", referencedColumnName="id", nullable=true)
     */
    protected $signatureType;

    /**
     * DigitalSignature
     *
     * @var \Dvsa\Olcs\Api\Entity\DigitalSignature
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\DigitalSignature", fetch="LAZY")
     * @ORM\JoinColumn(name="digital_signature_id", referencedColumnName="id", nullable=true)
     */
    protected $digitalSignature;

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
     * received
     *
     * @var string
     *
     * @ORM\Column(type="yesno", name="received", nullable=false, options={"default": 0})
     */
    protected $received = 0;

    /**
     * Tot auth vehicles
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="tot_auth_vehicles", nullable=true)
     */
    protected $totAuthVehicles;

    /**
     * Tot psv discs
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="tot_psv_discs", nullable=true)
     */
    protected $totPsvDiscs;

    /**
     * Tot community licences
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="tot_community_licences", nullable=true)
     */
    protected $totCommunityLicences;

    /**
     * Average balance amount
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="average_balance_amount", nullable=true)
     */
    protected $averageBalanceAmount;

    /**
     * hasOverdraft
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="has_overdraft", nullable=true)
     */
    protected $hasOverdraft;

    /**
     * Overdraft amount
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="overdraft_amount", nullable=true)
     */
    protected $overdraftAmount;

    /**
     * hasFactoring
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="has_factoring", nullable=true)
     */
    protected $hasFactoring;

    /**
     * Factoring amount
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="factoring_amount", nullable=true)
     */
    protected $factoringAmount;

    /**
     * hasOtherFinances
     *
     * @var string
     *
     * @ORM\Column(type="yesnonull", name="has_other_finances", nullable=true)
     */
    protected $hasOtherFinances;

    /**
     * Other finances amount
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="other_finances_amount", nullable=true)
     */
    protected $otherFinancesAmount;

    /**
     * Other finances details
     *
     * @var string
     *
     * @ORM\Column(type="string", name="other_finances_details", length=200, nullable=true)
     */
    protected $otherFinancesDetails;

    /**
     * Financial evidence uploaded
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="financial_evidence_uploaded", nullable=true)
     */
    protected $financialEvidenceUploaded;

    /**
     * Is digital
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="is_digital", nullable=false, options={"default": 0})
     */
    protected $isDigital = 0;

    /**
     * Digital notification sent
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="digital_notification_sent", nullable=true)
     */
    protected $digitalNotificationSent;

    /**
     * Digital reminder sent
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", name="digital_reminder_sent", nullable=false, options={"default": 0})
     */
    protected $digitalReminderSent = 0;

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
     * @return ContinuationDetail
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
     * Set the continuation
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Continuation $continuation new value being set
     *
     * @return ContinuationDetail
     */
    public function setContinuation($continuation)
    {
        $this->continuation = $continuation;

        return $this;
    }

    /**
     * Get the continuation
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Continuation     */
    public function getContinuation()
    {
        return $this->continuation;
    }

    /**
     * Set the licence
     *
     * @param \Dvsa\Olcs\Api\Entity\Licence\Licence $licence new value being set
     *
     * @return ContinuationDetail
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
     * Set the checklist document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $checklistDocument new value being set
     *
     * @return ContinuationDetail
     */
    public function setChecklistDocument($checklistDocument)
    {
        $this->checklistDocument = $checklistDocument;

        return $this;
    }

    /**
     * Get the checklist document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document     */
    public function getChecklistDocument()
    {
        return $this->checklistDocument;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status new value being set
     *
     * @return ContinuationDetail
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the status
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the signature type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $signatureType new value being set
     *
     * @return ContinuationDetail
     */
    public function setSignatureType($signatureType)
    {
        $this->signatureType = $signatureType;

        return $this;
    }

    /**
     * Get the signature type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData     */
    public function getSignatureType()
    {
        return $this->signatureType;
    }

    /**
     * Set the digital signature
     *
     * @param \Dvsa\Olcs\Api\Entity\DigitalSignature $digitalSignature new value being set
     *
     * @return ContinuationDetail
     */
    public function setDigitalSignature($digitalSignature)
    {
        $this->digitalSignature = $digitalSignature;

        return $this;
    }

    /**
     * Get the digital signature
     *
     * @return \Dvsa\Olcs\Api\Entity\DigitalSignature     */
    public function getDigitalSignature()
    {
        return $this->digitalSignature;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
     *
     * @return ContinuationDetail
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
     * @return ContinuationDetail
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
     * Set the received
     *
     * @param string $received new value being set
     *
     * @return ContinuationDetail
     */
    public function setReceived($received)
    {
        $this->received = $received;

        return $this;
    }

    /**
     * Get the received
     *
     * @return string     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * Set the tot auth vehicles
     *
     * @param int $totAuthVehicles new value being set
     *
     * @return ContinuationDetail
     */
    public function setTotAuthVehicles($totAuthVehicles)
    {
        $this->totAuthVehicles = $totAuthVehicles;

        return $this;
    }

    /**
     * Get the tot auth vehicles
     *
     * @return int     */
    public function getTotAuthVehicles()
    {
        return $this->totAuthVehicles;
    }

    /**
     * Set the tot psv discs
     *
     * @param int $totPsvDiscs new value being set
     *
     * @return ContinuationDetail
     */
    public function setTotPsvDiscs($totPsvDiscs)
    {
        $this->totPsvDiscs = $totPsvDiscs;

        return $this;
    }

    /**
     * Get the tot psv discs
     *
     * @return int     */
    public function getTotPsvDiscs()
    {
        return $this->totPsvDiscs;
    }

    /**
     * Set the tot community licences
     *
     * @param int $totCommunityLicences new value being set
     *
     * @return ContinuationDetail
     */
    public function setTotCommunityLicences($totCommunityLicences)
    {
        $this->totCommunityLicences = $totCommunityLicences;

        return $this;
    }

    /**
     * Get the tot community licences
     *
     * @return int     */
    public function getTotCommunityLicences()
    {
        return $this->totCommunityLicences;
    }

    /**
     * Set the average balance amount
     *
     * @param string $averageBalanceAmount new value being set
     *
     * @return ContinuationDetail
     */
    public function setAverageBalanceAmount($averageBalanceAmount)
    {
        $this->averageBalanceAmount = $averageBalanceAmount;

        return $this;
    }

    /**
     * Get the average balance amount
     *
     * @return string     */
    public function getAverageBalanceAmount()
    {
        return $this->averageBalanceAmount;
    }

    /**
     * Set the has overdraft
     *
     * @param string $hasOverdraft new value being set
     *
     * @return ContinuationDetail
     */
    public function setHasOverdraft($hasOverdraft)
    {
        $this->hasOverdraft = $hasOverdraft;

        return $this;
    }

    /**
     * Get the has overdraft
     *
     * @return string     */
    public function getHasOverdraft()
    {
        return $this->hasOverdraft;
    }

    /**
     * Set the overdraft amount
     *
     * @param string $overdraftAmount new value being set
     *
     * @return ContinuationDetail
     */
    public function setOverdraftAmount($overdraftAmount)
    {
        $this->overdraftAmount = $overdraftAmount;

        return $this;
    }

    /**
     * Get the overdraft amount
     *
     * @return string     */
    public function getOverdraftAmount()
    {
        return $this->overdraftAmount;
    }

    /**
     * Set the has factoring
     *
     * @param string $hasFactoring new value being set
     *
     * @return ContinuationDetail
     */
    public function setHasFactoring($hasFactoring)
    {
        $this->hasFactoring = $hasFactoring;

        return $this;
    }

    /**
     * Get the has factoring
     *
     * @return string     */
    public function getHasFactoring()
    {
        return $this->hasFactoring;
    }

    /**
     * Set the factoring amount
     *
     * @param string $factoringAmount new value being set
     *
     * @return ContinuationDetail
     */
    public function setFactoringAmount($factoringAmount)
    {
        $this->factoringAmount = $factoringAmount;

        return $this;
    }

    /**
     * Get the factoring amount
     *
     * @return string     */
    public function getFactoringAmount()
    {
        return $this->factoringAmount;
    }

    /**
     * Set the has other finances
     *
     * @param string $hasOtherFinances new value being set
     *
     * @return ContinuationDetail
     */
    public function setHasOtherFinances($hasOtherFinances)
    {
        $this->hasOtherFinances = $hasOtherFinances;

        return $this;
    }

    /**
     * Get the has other finances
     *
     * @return string     */
    public function getHasOtherFinances()
    {
        return $this->hasOtherFinances;
    }

    /**
     * Set the other finances amount
     *
     * @param string $otherFinancesAmount new value being set
     *
     * @return ContinuationDetail
     */
    public function setOtherFinancesAmount($otherFinancesAmount)
    {
        $this->otherFinancesAmount = $otherFinancesAmount;

        return $this;
    }

    /**
     * Get the other finances amount
     *
     * @return string     */
    public function getOtherFinancesAmount()
    {
        return $this->otherFinancesAmount;
    }

    /**
     * Set the other finances details
     *
     * @param string $otherFinancesDetails new value being set
     *
     * @return ContinuationDetail
     */
    public function setOtherFinancesDetails($otherFinancesDetails)
    {
        $this->otherFinancesDetails = $otherFinancesDetails;

        return $this;
    }

    /**
     * Get the other finances details
     *
     * @return string     */
    public function getOtherFinancesDetails()
    {
        return $this->otherFinancesDetails;
    }

    /**
     * Set the financial evidence uploaded
     *
     * @param bool $financialEvidenceUploaded new value being set
     *
     * @return ContinuationDetail
     */
    public function setFinancialEvidenceUploaded($financialEvidenceUploaded)
    {
        $this->financialEvidenceUploaded = $financialEvidenceUploaded;

        return $this;
    }

    /**
     * Get the financial evidence uploaded
     *
     * @return bool     */
    public function getFinancialEvidenceUploaded()
    {
        return $this->financialEvidenceUploaded;
    }

    /**
     * Set the is digital
     *
     * @param bool $isDigital new value being set
     *
     * @return ContinuationDetail
     */
    public function setIsDigital($isDigital)
    {
        $this->isDigital = $isDigital;

        return $this;
    }

    /**
     * Get the is digital
     *
     * @return bool     */
    public function getIsDigital()
    {
        return $this->isDigital;
    }

    /**
     * Set the digital notification sent
     *
     * @param bool $digitalNotificationSent new value being set
     *
     * @return ContinuationDetail
     */
    public function setDigitalNotificationSent($digitalNotificationSent)
    {
        $this->digitalNotificationSent = $digitalNotificationSent;

        return $this;
    }

    /**
     * Get the digital notification sent
     *
     * @return bool     */
    public function getDigitalNotificationSent()
    {
        return $this->digitalNotificationSent;
    }

    /**
     * Set the digital reminder sent
     *
     * @param bool $digitalReminderSent new value being set
     *
     * @return ContinuationDetail
     */
    public function setDigitalReminderSent($digitalReminderSent)
    {
        $this->digitalReminderSent = $digitalReminderSent;

        return $this;
    }

    /**
     * Get the digital reminder sent
     *
     * @return bool     */
    public function getDigitalReminderSent()
    {
        return $this->digitalReminderSent;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return ContinuationDetail
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