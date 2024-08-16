<?php

namespace Dvsa\Olcs\Api\Entity\Fee;

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
 * Transaction Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="txn",
 *    indexes={
 *        @ORM\Index(name="ix_txn_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_txn_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_txn_olbs_key", columns={"olbs_key"}),
 *        @ORM\Index(name="ix_txn_payment_method", columns={"payment_method"}),
 *        @ORM\Index(name="ix_txn_processed_by_user_id", columns={"processed_by_user_id"}),
 *        @ORM\Index(name="ix_txn_reference", columns={"reference"}),
 *        @ORM\Index(name="ix_txn_status", columns={"status"}),
 *        @ORM\Index(name="ix_txn_type", columns={"type"}),
 *        @ORM\Index(name="ix_txn_waive_recommender_user_id", columns={"waive_recommender_user_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_txn_receipt_document_id", columns={"receipt_document_id"})
 *    }
 * )
 */
abstract class AbstractTransaction implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Cheque po date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date", name="cheque_po_date", nullable=true)
     */
    protected $chequePoDate;

    /**
     * Cheque po number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="cheque_po_number", length=100, nullable=true)
     */
    protected $chequePoNumber;

    /**
     * Comment
     *
     * @var string
     *
     * @ORM\Column(type="string", name="comment", length=1000, nullable=true)
     */
    protected $comment;

    /**
     * Completed date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="completed_date", nullable=true)
     */
    protected $completedDate;

    /**
     * Cpms schema
     *
     * @var string
     *
     * @ORM\Column(type="string", name="cpms_schema", length=10, nullable=true)
     */
    protected $cpmsSchema;

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
     * Gateway url
     *
     * @var string
     *
     * @ORM\Column(type="string", name="gateway_url", length=1000, nullable=true)
     */
    protected $gatewayUrl;

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
     * Legacy choice
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="legacy_choice", nullable=true)
     */
    protected $legacyChoice;

    /**
     * Legacy guid
     *
     * @var string
     *
     * @ORM\Column(type="string", name="legacy_guid", length=255, nullable=true)
     */
    protected $legacyGuid;

    /**
     * Legacy method
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="legacy_method", nullable=true)
     */
    protected $legacyMethod;

    /**
     * Legacy status
     *
     * @var int
     *
     * @ORM\Column(type="smallint", name="legacy_status", nullable=true)
     */
    protected $legacyStatus;

    /**
     * Olbs key
     *
     * @var int
     *
     * @ORM\Column(type="integer", name="olbs_key", nullable=true)
     */
    protected $olbsKey;

    /**
     * Olbs type
     *
     * @var string
     *
     * @ORM\Column(type="string", name="olbs_type", length=45, nullable=true)
     */
    protected $olbsType;

    /**
     * Payer name
     *
     * @var string
     *
     * @ORM\Column(type="string", name="payer_name", length=100, nullable=true)
     */
    protected $payerName;

    /**
     * Paying in slip number
     *
     * @var string
     *
     * @ORM\Column(type="string", name="paying_in_slip_number", length=100, nullable=true)
     */
    protected $payingInSlipNumber;

    /**
     * Payment method
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="payment_method", referencedColumnName="id", nullable=true)
     */
    protected $paymentMethod;

    /**
     * Processed by user
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="processed_by_user_id", referencedColumnName="id", nullable=true)
     */
    protected $processedByUser;

    /**
     * Receipt document
     *
     * @var \Dvsa\Olcs\Api\Entity\Doc\Document
     *
     * @ORM\OneToOne(targetEntity="Dvsa\Olcs\Api\Entity\Doc\Document", fetch="LAZY")
     * @ORM\JoinColumn(name="receipt_document_id", referencedColumnName="id", nullable=true)
     */
    protected $receiptDocument;

    /**
     * Reference
     *
     * @var string
     *
     * @ORM\Column(type="string", name="reference", length=255, nullable=true)
     */
    protected $reference;

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
     * Type
     *
     * @var \Dvsa\Olcs\Api\Entity\System\RefData
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\System\RefData", fetch="LAZY")
     * @ORM\JoinColumn(name="type", referencedColumnName="id", nullable=false)
     */
    protected $type;

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
     * Waive recommendation date
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="waive_recommendation_date", nullable=true)
     */
    protected $waiveRecommendationDate;

    /**
     * Waive recommender user
     *
     * @var \Dvsa\Olcs\Api\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="waive_recommender_user_id", referencedColumnName="id", nullable=true)
     */
    protected $waiveRecommenderUser;

    /**
     * Fee transaction
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Fee\FeeTransaction",
     *     mappedBy="transaction",
     *     cascade={"persist"}
     * )
     */
    protected $feeTransactions;

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function __construct()
    {
        $this->initCollections();
    }

    /**
     * Initialise the collections
     *
     * @return void
     */
    public function initCollections()
    {
        $this->feeTransactions = new ArrayCollection();
    }

    /**
     * Set the cheque po date
     *
     * @param \DateTime $chequePoDate new value being set
     *
     * @return Transaction
     */
    public function setChequePoDate($chequePoDate)
    {
        $this->chequePoDate = $chequePoDate;

        return $this;
    }

    /**
     * Get the cheque po date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getChequePoDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->chequePoDate);
        }

        return $this->chequePoDate;
    }

    /**
     * Set the cheque po number
     *
     * @param string $chequePoNumber new value being set
     *
     * @return Transaction
     */
    public function setChequePoNumber($chequePoNumber)
    {
        $this->chequePoNumber = $chequePoNumber;

        return $this;
    }

    /**
     * Get the cheque po number
     *
     * @return string
     */
    public function getChequePoNumber()
    {
        return $this->chequePoNumber;
    }

    /**
     * Set the comment
     *
     * @param string $comment new value being set
     *
     * @return Transaction
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the completed date
     *
     * @param \DateTime $completedDate new value being set
     *
     * @return Transaction
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;

        return $this;
    }

    /**
     * Get the completed date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getCompletedDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->completedDate);
        }

        return $this->completedDate;
    }

    /**
     * Set the cpms schema
     *
     * @param string $cpmsSchema new value being set
     *
     * @return Transaction
     */
    public function setCpmsSchema($cpmsSchema)
    {
        $this->cpmsSchema = $cpmsSchema;

        return $this;
    }

    /**
     * Get the cpms schema
     *
     * @return string
     */
    public function getCpmsSchema()
    {
        return $this->cpmsSchema;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return Transaction
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
     * Set the gateway url
     *
     * @param string $gatewayUrl new value being set
     *
     * @return Transaction
     */
    public function setGatewayUrl($gatewayUrl)
    {
        $this->gatewayUrl = $gatewayUrl;

        return $this;
    }

    /**
     * Get the gateway url
     *
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->gatewayUrl;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return Transaction
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
     * @return Transaction
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
     * Set the legacy choice
     *
     * @param int $legacyChoice new value being set
     *
     * @return Transaction
     */
    public function setLegacyChoice($legacyChoice)
    {
        $this->legacyChoice = $legacyChoice;

        return $this;
    }

    /**
     * Get the legacy choice
     *
     * @return int
     */
    public function getLegacyChoice()
    {
        return $this->legacyChoice;
    }

    /**
     * Set the legacy guid
     *
     * @param string $legacyGuid new value being set
     *
     * @return Transaction
     */
    public function setLegacyGuid($legacyGuid)
    {
        $this->legacyGuid = $legacyGuid;

        return $this;
    }

    /**
     * Get the legacy guid
     *
     * @return string
     */
    public function getLegacyGuid()
    {
        return $this->legacyGuid;
    }

    /**
     * Set the legacy method
     *
     * @param int $legacyMethod new value being set
     *
     * @return Transaction
     */
    public function setLegacyMethod($legacyMethod)
    {
        $this->legacyMethod = $legacyMethod;

        return $this;
    }

    /**
     * Get the legacy method
     *
     * @return int
     */
    public function getLegacyMethod()
    {
        return $this->legacyMethod;
    }

    /**
     * Set the legacy status
     *
     * @param int $legacyStatus new value being set
     *
     * @return Transaction
     */
    public function setLegacyStatus($legacyStatus)
    {
        $this->legacyStatus = $legacyStatus;

        return $this;
    }

    /**
     * Get the legacy status
     *
     * @return int
     */
    public function getLegacyStatus()
    {
        return $this->legacyStatus;
    }

    /**
     * Set the olbs key
     *
     * @param int $olbsKey new value being set
     *
     * @return Transaction
     */
    public function setOlbsKey($olbsKey)
    {
        $this->olbsKey = $olbsKey;

        return $this;
    }

    /**
     * Get the olbs key
     *
     * @return int
     */
    public function getOlbsKey()
    {
        return $this->olbsKey;
    }

    /**
     * Set the olbs type
     *
     * @param string $olbsType new value being set
     *
     * @return Transaction
     */
    public function setOlbsType($olbsType)
    {
        $this->olbsType = $olbsType;

        return $this;
    }

    /**
     * Get the olbs type
     *
     * @return string
     */
    public function getOlbsType()
    {
        return $this->olbsType;
    }

    /**
     * Set the payer name
     *
     * @param string $payerName new value being set
     *
     * @return Transaction
     */
    public function setPayerName($payerName)
    {
        $this->payerName = $payerName;

        return $this;
    }

    /**
     * Get the payer name
     *
     * @return string
     */
    public function getPayerName()
    {
        return $this->payerName;
    }

    /**
     * Set the paying in slip number
     *
     * @param string $payingInSlipNumber new value being set
     *
     * @return Transaction
     */
    public function setPayingInSlipNumber($payingInSlipNumber)
    {
        $this->payingInSlipNumber = $payingInSlipNumber;

        return $this;
    }

    /**
     * Get the paying in slip number
     *
     * @return string
     */
    public function getPayingInSlipNumber()
    {
        return $this->payingInSlipNumber;
    }

    /**
     * Set the payment method
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $paymentMethod entity being set as the value
     *
     * @return Transaction
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get the payment method
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set the processed by user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $processedByUser entity being set as the value
     *
     * @return Transaction
     */
    public function setProcessedByUser($processedByUser)
    {
        $this->processedByUser = $processedByUser;

        return $this;
    }

    /**
     * Get the processed by user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getProcessedByUser()
    {
        return $this->processedByUser;
    }

    /**
     * Set the receipt document
     *
     * @param \Dvsa\Olcs\Api\Entity\Doc\Document $receiptDocument entity being set as the value
     *
     * @return Transaction
     */
    public function setReceiptDocument($receiptDocument)
    {
        $this->receiptDocument = $receiptDocument;

        return $this;
    }

    /**
     * Get the receipt document
     *
     * @return \Dvsa\Olcs\Api\Entity\Doc\Document
     */
    public function getReceiptDocument()
    {
        return $this->receiptDocument;
    }

    /**
     * Set the reference
     *
     * @param string $reference new value being set
     *
     * @return Transaction
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get the reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the status
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $status entity being set as the value
     *
     * @return Transaction
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
     * Set the type
     *
     * @param \Dvsa\Olcs\Api\Entity\System\RefData $type entity being set as the value
     *
     * @return Transaction
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the type
     *
     * @return \Dvsa\Olcs\Api\Entity\System\RefData
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return Transaction
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
     * Set the waive recommendation date
     *
     * @param \DateTime $waiveRecommendationDate new value being set
     *
     * @return Transaction
     */
    public function setWaiveRecommendationDate($waiveRecommendationDate)
    {
        $this->waiveRecommendationDate = $waiveRecommendationDate;

        return $this;
    }

    /**
     * Get the waive recommendation date
     *
     * @param bool $asDateTime If true will always return a \DateTime (or null) never a string datetime
     *
     * @return \DateTime|string

     */
    public function getWaiveRecommendationDate($asDateTime = false)
    {
        if ($asDateTime === true) {
            return $this->asDateTime($this->waiveRecommendationDate);
        }

        return $this->waiveRecommendationDate;
    }

    /**
     * Set the waive recommender user
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $waiveRecommenderUser entity being set as the value
     *
     * @return Transaction
     */
    public function setWaiveRecommenderUser($waiveRecommenderUser)
    {
        $this->waiveRecommenderUser = $waiveRecommenderUser;

        return $this;
    }

    /**
     * Get the waive recommender user
     *
     * @return \Dvsa\Olcs\Api\Entity\User\User
     */
    public function getWaiveRecommenderUser()
    {
        return $this->waiveRecommenderUser;
    }

    /**
     * Set the fee transaction
     *
     * @param ArrayCollection $feeTransactions collection being set as the value
     *
     * @return Transaction
     */
    public function setFeeTransactions($feeTransactions)
    {
        $this->feeTransactions = $feeTransactions;

        return $this;
    }

    /**
     * Get the fee transactions
     *
     * @return ArrayCollection
     */
    public function getFeeTransactions()
    {
        return $this->feeTransactions;
    }

    /**
     * Add a fee transactions
     *
     * @param ArrayCollection|mixed $feeTransactions collection being added
     *
     * @return Transaction
     */
    public function addFeeTransactions($feeTransactions)
    {
        if ($feeTransactions instanceof ArrayCollection) {
            $this->feeTransactions = new ArrayCollection(
                array_merge(
                    $this->feeTransactions->toArray(),
                    $feeTransactions->toArray()
                )
            );
        } elseif (!$this->feeTransactions->contains($feeTransactions)) {
            $this->feeTransactions->add($feeTransactions);
        }

        return $this;
    }

    /**
     * Remove a fee transactions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $feeTransactions collection being removed
     *
     * @return Transaction
     */
    public function removeFeeTransactions($feeTransactions)
    {
        if ($this->feeTransactions->contains($feeTransactions)) {
            $this->feeTransactions->removeElement($feeTransactions);
        }

        return $this;
    }
}
