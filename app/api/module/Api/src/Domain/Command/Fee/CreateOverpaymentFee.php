<?php

/**
 * Create Overpayment Fee
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create Overpayment Fee
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class CreateOverpaymentFee extends AbstractCommand
{
    /** @var string */
    protected $receivedAmount;

    /** @var array Fee */
    protected $fees;

    /**
     * Gets the value of receivedAmount.
     *
     * @return string
     */
    public function getReceivedAmount()
    {
        return $this->receivedAmount;
    }

    /**
     * Gets the array of fees.
     *
     * @return array
     */
    public function getFees()
    {
        return $this->fees;
    }
}
