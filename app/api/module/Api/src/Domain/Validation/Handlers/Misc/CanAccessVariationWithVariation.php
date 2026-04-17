<?php

/**
 * Can Access Variation With Variation
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

/**
 * Can Access Variation With Variation
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessVariationWithVariation extends CanAccessApplicationWithId
{
    #[\Override]
    protected function getId($dto)
    {
        return $dto->getVariation();
    }
}
