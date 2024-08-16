<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Application;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TransportManagerApplicationEntity;

/**
 * Class TransportManagers
 * @package Dvsa\Olcs\Api\Service\Publication\Context\Application
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class TransportManagers extends AbstractContext
{
    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject $context
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $tmData = $publicationLink->getApplication()->getTransportManagers();

        $newTmData = [];
        $applicationTransportManagers = [];

        /**
         * @var TransportManagerApplicationEntity $tm
         */
        foreach ($tmData as $tma) {
            $newTmData[] = $tma->getTransportManager()->getHomeCd()->getPerson()->getFullName();

            if ($tma->getAction() === 'A' || $tma->getAction() === 'U') {
                $applicationTransportManagers[] = $tma->getTransportManager();
            }
        }

        // contains a string of comma seperated TM's that are attached to the application
        if (!empty($newTmData)) {
            $context->offsetSet('transportManagers', implode(', ', $newTmData));
        }
        // contains an array of TM entities that have been added/updated on the application
        if (!empty($applicationTransportManagers)) {
            $context->offsetSet('applicationTransportManagers', $applicationTransportManagers);
        }

        return $context;
    }
}
