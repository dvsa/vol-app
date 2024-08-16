<?php

/**
 * Resolve Payment
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Transaction;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Resolve Payment
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ResolvePayment extends AbstractCommand
{
    protected $id;

    protected $paymentMethod;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }
}
