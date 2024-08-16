<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Variation;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;
use Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Variation Text2
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class Text2 implements ProcessInterface
{
    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $text[] = Formatter\OrganisationName::format($publicationLink->getLicence()->getOrganisation());

        if ($context->offsetExists('applicationPeople') && is_array($context->offsetGet('applicationPeople'))) {
            $text[] = Formatter\People::format(
                $publicationLink->getLicence()->getOrganisation(),
                $context->offsetGet('applicationPeople')
            );
        }

        $publicationLink->setText2(implode("\n", $text));
    }
}
