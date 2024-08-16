<?php

/**
 * Create Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Fee;

/**
 * Create Fee
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateFee extends \Dvsa\Olcs\Transfer\Command\Fee\CreateFee
{
    /**
     * @var string
     */
    protected $irfoFeeExempt;

    /**
     * @var string
     */
    protected $waiveReason;

    /**
     * @return string
     */
    public function getIrfoFeeExempt()
    {
        return $this->irfoFeeExempt;
    }

    /**
     * @return string
     */
    public function getWaiveReason()
    {
        return $this->waiveReason;
    }
}
