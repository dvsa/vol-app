<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\CpmsHelperInterface;

/**
 * Cpms Aware Interface
 */
interface CpmsAwareInterface
{
    public function setCpmsService(CpmsHelperInterface $service);

    /**
     * @return CpmsHelperInterface
     */
    public function getCpmsService();
}
