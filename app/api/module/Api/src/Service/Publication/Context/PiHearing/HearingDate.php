<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\PiHearing;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;

/**
 * Class HearingDate
 * @package Dvsa\Olcs\Api\Service\Publication\Context\PiHearing
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class HearingDate extends AbstractContext
{
    /**
     * Provide data
     *
     * @param PublicationLink $publication Publication link
     * @param \ArrayObject    $context     Context data
     *
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publication, \ArrayObject $context)
    {
        // use DateTimeFrontEnd as the time needs to be rendered in users local time
        $hearingDateTime = new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTimeFrontEnd($context->offsetGet('hearingDate'));
        $context->offsetSet('formattedHearingDate', $hearingDateTime->format('j F Y'));
        $context->offsetSet('formattedHearingTime', $hearingDateTime->format('H:i'));

        return $context;
    }
}
