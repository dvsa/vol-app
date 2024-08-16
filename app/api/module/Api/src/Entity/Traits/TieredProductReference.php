<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use DateTime;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;

/**
 * Tiered Product Reference Trait
 *
 * author Andy Newton <andy@vitri.ltd>
 *
 */
trait TieredProductReference
{
    /**
     * @param array $tieredProductReferenceArray Associative array mappping Jan-Dec with Product References
     * @return string
     * @throws BadRequestException
     */
    public function genericGetProdRefForTier(DateTime $validityStart, DateTime $validityEnd, DateTime $now, array $tieredProductReferenceArray)
    {
        // If the stock validity period is in the future, return 'Jan', If inside, return from array.
        if ($validityStart > $now) {
            return $tieredProductReferenceArray['Jan'];
        } elseif ($now <= $validityEnd) {
            return $tieredProductReferenceArray[$now->format('M')];
        }

        // Validity period must be in the past, no automatic issue fees should be created for useless permits so throw.
        throw new BadRequestException('Cannot get issue fee type for permit with window validity period in the past.');
    }
}
