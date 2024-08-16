<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Application;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;
use Dvsa\Olcs\Api\Service\Publication\Formatter;

/**
 * Class Text2
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Text2 implements ProcessInterface
{
    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        $organisation = $publicationLink->getApplication()->getLicence()->getOrganisation();

        $text = [];
        $text[] = Formatter\OrganisationName::format($organisation);
        $text[] = Formatter\People::format($organisation, $context->offsetGet('applicationPeople'));

        $publicationLink->setText2(implode("\n", $text));
    }
}
