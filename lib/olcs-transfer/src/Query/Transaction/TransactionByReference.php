<?php

/**
 * Transaction by reference
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Query\Transaction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/transaction/by-reference")
 */
class TransactionByReference extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $reference;

    public function getReference()
    {
        return $this->reference;
    }
}
