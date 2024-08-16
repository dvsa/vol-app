<?php

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

/**
 * Interface DateProviderInterface
 * @package Dvsa\Olcs\Api\Util\DateTime
 */
interface DateProviderInterface
{
    /**
     * Returns an array of dates between $startDate and $endDate implementation determines which dates apply
     *
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return array
     */
    public function between(\DateTime $startDate, \DateTime $endDate);
}
