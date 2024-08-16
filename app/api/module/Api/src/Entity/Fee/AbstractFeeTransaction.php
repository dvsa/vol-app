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
 * FeeTransaction Abstract Entity
 *
 * Auto-Generated
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="fee_txn",
 *    indexes={
 *        @ORM\Index(name="ix_fee_txn_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_fee_txn_fee_id", columns={"fee_id"}),
 *        @ORM\Index(name="ix_fee_txn_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_fee_txn_reversed_fee_txn_id", columns={"reversed_fee_txn_id"}),
 *        @ORM\Index(name="ix_fee_txn_txn_id", columns={"txn_id"})
 *    }
 * )
 */
abstract class AbstractFeeTransaction implements BundleSerializableInterface, JsonSerializable
{
    use BundleSerializableTrait;
    use ProcessDateTrait;
    use ClearPropertiesWithCollectionsTrait;
    use CreatedOnTrait;
    use ModifiedOnTrait;

    /**
     * Amount
     *
     * @var float
     *
     * @ORM\Column(type="decimal", name="amount", precision=10, scale=2, nullable=true)
     */
    protected $amount;

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
     * Fee
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\Fee
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Fee\Fee",
     *     fetch="LAZY",
     *     cascade={"persist"},
     *     inversedBy="feeTransactions"
     * )
     * @ORM\JoinColumn(name="fee_id", referencedColumnName="id", nullable=false)
     */
    protected $fee;

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
     * Reversed fee transaction
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Fee\FeeTransaction",
     *     fetch="LAZY",
     *     inversedBy="reversingFeeTransactions"
     * )
     * @ORM\JoinColumn(name="reversed_fee_txn_id", referencedColumnName="id", nullable=true)
     */
    protected $reversedFeeTransaction;

    /**
     * Transaction
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\Transaction
     *
     * @ORM\ManyToOne(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Fee\Transaction",
     *     fetch="LAZY",
     *     cascade={"persist"},
     *     inversedBy="feeTransactions"
     * )
     * @ORM\JoinColumn(name="txn_id", referencedColumnName="id", nullable=false)
     */
    protected $transaction;

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
     * Reversing fee transaction
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="Dvsa\Olcs\Api\Entity\Fee\FeeTransaction",
     *     mappedBy="reversedFeeTransaction"
     * )
     */
    protected $reversingFeeTransactions;

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
        $this->reversingFeeTransactions = new ArrayCollection();
    }

    /**
     * Set the amount
     *
     * @param float $amount new value being set
     *
     * @return FeeTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy entity being set as the value
     *
     * @return FeeTransaction
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
     * Set the fee
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\Fee $fee entity being set as the value
     *
     * @return FeeTransaction
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get the fee
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\Fee
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set the id
     *
     * @param int $id new value being set
     *
     * @return FeeTransaction
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
     * @return FeeTransaction
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
     * Set the reversed fee transaction
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction $reversedFeeTransaction entity being set as the value
     *
     * @return FeeTransaction
     */
    public function setReversedFeeTransaction($reversedFeeTransaction)
    {
        $this->reversedFeeTransaction = $reversedFeeTransaction;

        return $this;
    }

    /**
     * Get the reversed fee transaction
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction
     */
    public function getReversedFeeTransaction()
    {
        return $this->reversedFeeTransaction;
    }

    /**
     * Set the transaction
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\Transaction $transaction entity being set as the value
     *
     * @return FeeTransaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get the transaction
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set the version
     *
     * @param int $version new value being set
     *
     * @return FeeTransaction
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
     * Set the reversing fee transaction
     *
     * @param ArrayCollection $reversingFeeTransactions collection being set as the value
     *
     * @return FeeTransaction
     */
    public function setReversingFeeTransactions($reversingFeeTransactions)
    {
        $this->reversingFeeTransactions = $reversingFeeTransactions;

        return $this;
    }

    /**
     * Get the reversing fee transactions
     *
     * @return ArrayCollection
     */
    public function getReversingFeeTransactions()
    {
        return $this->reversingFeeTransactions;
    }

    /**
     * Add a reversing fee transactions
     *
     * @param ArrayCollection|mixed $reversingFeeTransactions collection being added
     *
     * @return FeeTransaction
     */
    public function addReversingFeeTransactions($reversingFeeTransactions)
    {
        if ($reversingFeeTransactions instanceof ArrayCollection) {
            $this->reversingFeeTransactions = new ArrayCollection(
                array_merge(
                    $this->reversingFeeTransactions->toArray(),
                    $reversingFeeTransactions->toArray()
                )
            );
        } elseif (!$this->reversingFeeTransactions->contains($reversingFeeTransactions)) {
            $this->reversingFeeTransactions->add($reversingFeeTransactions);
        }

        return $this;
    }

    /**
     * Remove a reversing fee transactions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $reversingFeeTransactions collection being removed
     *
     * @return FeeTransaction
     */
    public function removeReversingFeeTransactions($reversingFeeTransactions)
    {
        if ($this->reversingFeeTransactions->contains($reversingFeeTransactions)) {
            $this->reversingFeeTransactions->removeElement($reversingFeeTransactions);
        }

        return $this;
    }
}
