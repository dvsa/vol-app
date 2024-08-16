<?php

namespace Dvsa\Olcs\Api\Entity\Fee;

use Doctrine\ORM\Mapping as ORM;

/**
 * FeeTransaction Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="fee_txn",
 *    indexes={
 *        @ORM\Index(name="ix_fee_txn_txn_id", columns={"txn_id"}),
 *        @ORM\Index(name="ix_fee_txn_fee_id", columns={"fee_id"}),
 *        @ORM\Index(name="ix_fee_txn_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_fee_txn_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_fee_txn_fee_txn1_idx", columns={"reversed_fee_txn_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_fee_txn_fee_id_txn_id", columns={"fee_id","txn_id"})
 *    }
 * )
 */
class FeeTransaction extends AbstractFeeTransaction
{
    /**
     * @return boolean
     */
    public function isRefundedOrReversed()
    {
        return count($this->getReversingFeeTransactions()) > 0;
    }
}
