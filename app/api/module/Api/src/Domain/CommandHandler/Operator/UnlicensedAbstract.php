<?php

/**
* Abstract Unlicensed Operator Command Handler
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Operator;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;

/**
 * Abstract Unlicensed Operator Command Handler
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class UnlicensedAbstract extends AbstractCommandHandler
{
    public const LICENCE_NUMBER_PREFIX = 'U';

    public const EXEMPT_LICENCE_NUMBER_PREFIX = 'E';

    /**
     * Get a licence number for an unlicensed operator
     *
     * @param string $categoryPrefix e.g. 'O'
     * @param string $trafficAreaId e.g. 'B'
     * @param string $number e.g. '1234567'
     * @param bool $isExempt
     * @return string e.g. 'UOB1234567'
     */
    protected function buildLicenceNumber($categoryPrefix, $trafficAreaId, $number, $isExempt = false)
    {
        $prefix = $isExempt ? self::EXEMPT_LICENCE_NUMBER_PREFIX : self::LICENCE_NUMBER_PREFIX;

        return sprintf(
            '%s%s%s%s',
            $prefix,
            $categoryPrefix,
            $trafficAreaId,
            $number
        );
    }
}
