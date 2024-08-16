<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Application;

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
        $application = $publicationLink->getApplication();

        if (!is_null($application->getTotAuthLgvVehicles())) {
            $text = sprintf(
                'Authorisation: %s Light goods vehicle(s).',
                $application->getTotAuthLgvVehicles()
            );

            $context->offsetSet('authorisation', $text);
        }

        return $context;
    }
}
