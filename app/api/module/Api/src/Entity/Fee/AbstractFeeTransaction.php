<?php

declare(strict_types=1);

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
 * AbstractFeeTransaction Abstract Entity
 *
 * Auto-Generated
 * @source OLCS-Entity-Generator-v2
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
abstract class AbstractFeeTransaction implements BundleSerializableInterface, JsonSerializable, \Stringable
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
     * Foreign Key to fee
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\Fee
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Fee\Fee", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="fee_id", referencedColumnName="id")
     */
    protected $fee;

    /**
     * Foreign Key to payment
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\Transaction
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Fee\Transaction", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="txn_id", referencedColumnName="id")
     */
    protected $transaction;

    /**
     * ReversedFeeTxn
     *
     * @var \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction
     *
     * @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\Fee\FeeTransaction", fetch="LAZY")
     * @ORM\JoinColumn(name="reversed_fee_txn_id", referencedColumnName="id", nullable=true)
     */
    protected $reversedFeeTransaction;

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
     * Amount
     *
     * @var string
     *
     * @ORM\Column(type="decimal", name="amount", nullable=true)
     */
    protected $amount;

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
     * ReversingFeeTransactions
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Dvsa\Olcs\Api\Entity\Fee\FeeTransaction", mappedBy="reversedFeeTransaction")
     */
    protected $reversingFeeTransactions;

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
        $this->reversingFeeTransactions = new ArrayCollection();
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
     * @return int     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the fee
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\Fee $fee new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Fee\Fee     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set the transaction
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\Transaction $transaction new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Fee\Transaction     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set the reversed fee transaction
     *
     * @param \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction $reversedFeeTransaction new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\Fee\FeeTransaction     */
    public function getReversedFeeTransaction()
    {
        return $this->reversedFeeTransaction;
    }

    /**
     * Set the created by
     *
     * @param \Dvsa\Olcs\Api\Entity\User\User $createdBy new value being set
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
     * @return \Dvsa\Olcs\Api\Entity\User\User     */
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    /**
     * Set the amount
     *
     * @param string $amount new value being set
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
     * @return string     */
    public function getAmount()
    {
        return $this->amount;
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
     * @return int     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the reversing fee transactions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $reversingFeeTransactions collection being set as the value
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getReversingFeeTransactions()
    {
        return $this->reversingFeeTransactions;
    }

    /**
     * Add a reversing fee transactions
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|mixed $reversingFeeTransactions collection being added
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

    /**
     * Get bundle data
     */
    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
