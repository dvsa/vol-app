<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Variation;

use ArrayObject;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;

/**
 * Authorisations
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class Authorisations extends AbstractContext
{
    /**
     * @param PublicationLink $publicationLink
     * @param ArrayObject $context
     *
     * @return ArrayObject
     */
    public function provide(PublicationLink $publicationLink, ArrayObject $context)
    {
        $variation = $publicationLink->getApplication();
        $text = [];

        if ($variation->hasHgvAuthorisationIncreased()) {
            $vehicleCaption = 'vehicle(s)';
            if ($variation->isVehicleTypeMixedWithLgv()) {
                $vehicleCaption = 'Heavy goods vehicle(s)';
            }

            $text[] = sprintf(
                'New licence authorisation will be %d %s',
                $variation->getTotAuthHgvVehicles(),
                $vehicleCaption
            );
        }

        if ($variation->hasLgvAuthorisationIncreased() || $variation->hasLgvAuthorisationChangedFromNullToNumeric()) {
            $text[] = sprintf(
                'New licence authorisation will be %d Light goods vehicle(s)',
                $variation->getTotAuthLgvVehicles()
            );
        }

        if ($variation->hasAuthTrailersIncrease()) {
            $text[] = sprintf(
                'New licence authorisation will be %d trailer(s)',
                $variation->getTotAuthTrailers()
            );
        }

        if (count($text)) {
            $context->offsetSet('authorisation', $text);
        }

        return $context;
    }
}
