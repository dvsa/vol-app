<?php

/**
 * Tm Hearing text 1
 */

namespace Dvsa\Olcs\Api\Service\Publication\Process\PiHearing;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Text1 as AbstractText1;

/**
 * Class TmHearingText1
 * @package Dvsa\Olcs\Api\Service\Publication\Process\PiHearing
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class TmHearingText1 extends AbstractText1
{
    protected $pi = 'TM Public Inquiry (Case ID: %s, Public Inquiry ID: %s) for %s to be held at %s,
    on %s commencing at %s';

    /**
     * @param PublicationLink $publication
     * @param ImmutableArrayObject $context
     * @return PublicationLink
     */
    #[\Override]
    public function process(PublicationLink $publication, ImmutableArrayObject $context)
    {
        $hearingText = [];
        $hearingText[] = $this->getOpeningText($publication, $context);

        //previous publication
        if ($context->offsetExists('previousPublication')) {
            $hearingText[] = $this->getPreviousPublication($context->offsetGet('previousPublication'));
        }

        //previous hearing, only present on hearing publication, not on decision
        if ($context->offsetExists('previousHearing')) {
            $hearingText[] = $this->getPreviousHearing($context->offsetGet('previousHearing'));
        }

        $publication->setText1(implode(' ', $hearingText));

        return $publication;
    }

    /**
     * @param PublicationLink $publication
     * @param ImmutableArrayObject $context
     * @return String
     */
    private function getOpeningText(PublicationLink $publication, ImmutableArrayObject $context)
    {
        return sprintf(
            $this->pi,
            $publication->getPi()->getCase()->getId(),
            $context->offsetGet('id'),
            $context->offsetGet('transportManagerName'),
            $context->offsetGet('venueOther'),
            $context->offsetGet('formattedHearingDate'),
            $context->offsetGet('formattedHearingTime')
        );
    }
}
