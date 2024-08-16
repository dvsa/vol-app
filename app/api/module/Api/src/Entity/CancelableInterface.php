<?php

namespace Dvsa\Olcs\Api\Entity;

use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Cancelable interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface CancelableInterface
{
    public function getId();
    public function cancel(RefData $cancelStatus);
    public function canBeCancelled();
    public function getOutstandingFees();
    public function getCancellationDate();
}
