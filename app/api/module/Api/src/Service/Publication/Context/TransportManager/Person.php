<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\TransportManager;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TmEntity;

/**
 * Gets the Transport manager person entity for this publication
 *
 * Saves to TmPeople key as it's possible in the future there will be filters with multiple transport managers
 * e.g. all from an application
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class Person extends AbstractContext
{
    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject $context
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        /**
         * @var PersonEntity $person
         * @var TmEntity $transportManager
         */
        $transportManager = $publicationLink->getTransportManager();

        if ($transportManager instanceof TmEntity) {
            $person = $transportManager->getHomeCd()->getPerson();
            $context->offsetSet('tmPeople', [$person->getId() => $person]);
        }

        return $context;
    }
}
