<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\BusReg;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Class VariationReasons
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Bus
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class VariationReasons extends AbstractContext
{
    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        $busReg = $publication->getBusReg();
        $variationReasons = $busReg->getVariationReasons();

        $reasons = [];

        if (!$variationReasons->isEmpty()) {
            /** @var RefData $reason */
            foreach ($variationReasons as $reason) {
                $reasons[] = $reason->getDescription();
            }
        }

        $numReasons = count($reasons);

        switch ($numReasons) {
            case 0:
                $variationReasons = null;
                break;
            case 1:
                $variationReasons = $reasons[0];
                break;
            default:
                $variationReasons = $reasons[0];

                for ($i = 1; $i < $numReasons; $i++) {
                    if ($i == ($numReasons - 1)) {
                        //array counts from zero, so this is last record
                        $variationReasons .= ' and ' . $reasons[$i];
                    } else {
                        $variationReasons .= ', ' . $reasons[$i];
                    }
                }
        }

        $context->offsetSet('variationReasons', $variationReasons);

        return $context;
    }
}
