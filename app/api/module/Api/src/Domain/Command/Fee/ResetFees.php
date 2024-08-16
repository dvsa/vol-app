<?php

/**
 * Reset Fees
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Reset Fees
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ResetFees extends AbstractCommand
{
    /** @var array Fee */
    protected $fees;

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
