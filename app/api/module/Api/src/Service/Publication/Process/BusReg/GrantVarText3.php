<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\BusReg;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;

/**
 * Class GrantVarText3
 * @package Dvsa\Olcs\Api\Service\Publication\Process\BusReg
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class GrantVarText3 implements ProcessInterface
{
    /**
     * @param PublicationLink $publication
     * @param ImmutableArrayObject $context
     * @return PublicationLink
     */
    public function process(PublicationLink $publication, ImmutableArrayObject $context)
    {
        $busReg = $publication->getBusReg();

        $text = 'Operating between %s and %s given service number %s effective from %s.';

        $variationText =  ' To amend %s.';
        $effectiveDate = new \DateTime($busReg->getEffectiveDate());
        $variationReasons = $context->offsetGet('variationReasons');

        $result = sprintf(
            $text,
            $busReg->getStartPoint(),
            $busReg->getFinishPoint(),
            $context->offsetGet('busServices'),
            $effectiveDate->format('d F Y')
        );

        if ($variationReasons) {
            $result .= sprintf($variationText, $variationReasons);
        }

        $publication->setText3($result);

        return $publication;
    }
}
