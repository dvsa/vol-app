<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Application;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData as PoliceDataEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;

/**
 * Class Police
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Police implements \Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface
{
    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        // remove any existing
        $publicationLink->getPoliceDatas()->clear();
        $this->addPeople($publicationLink, $context);
        $this->addTransportManagers($publicationLink, $context);
    }

    /**
     * Add all People from context into the police data
     */
    private function addPeople(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        if ($context->offsetExists('applicationPeople')) {
            // add each person populated from context
            foreach ($context->offsetGet('applicationPeople') as $person) {
                /* @var $person PersonEntity */
                $publicationLink->getPoliceDatas()->add(
                    new PoliceDataEntity($publicationLink, $person)
                );
            }
        }
    }

    /**
     * Add all Transport Manager from context into the police data
     */
    private function addTransportManagers(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        if (
            $publicationLink->getApplication()->isNew() &&
            $publicationLink->getPublicationSection()->getId() !== PublicationSection::APP_NEW_SECTION &&
            $publicationLink->getPublicationSection()->getId() !== PublicationSection::APP_GRANTED_SECTION
        ) {
            // if new application and NOT received or granted then don't add the TM's
            return;
        }

        if ($context->offsetExists('applicationTransportManagers')) {
            // add each person populated from context
            foreach ($context->offsetGet('applicationTransportManagers') as $transportManager) {
                /* @var $transportManager \Dvsa\Olcs\Api\Entity\Tm\TransportManager */
                $publicationLink->getPoliceDatas()->add(
                    new PoliceDataEntity($publicationLink, $transportManager->getHomeCd()->getPerson())
                );
            }
        }
    }
}
