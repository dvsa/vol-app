<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\TransportManager;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData as PoliceDataEntity;
use Dvsa\Olcs\Api\Service\Publication\Process\ProcessInterface;

/**
 * Class Police
 *
 * @author Ian Lindsay <mat.evans@valtech.co.uk>
 */
class Police implements ProcessInterface
{
    /**
     * @param PublicationLink $publicationLink
     * @param ImmutableArrayObject $context
     */
    public function process(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        // remove any existing
        $publicationLink->getPoliceDatas()->clear();

        if ($context->offsetExists('tmPeople')) {
            // add each person populated from context
            foreach ($context->offsetGet('tmPeople') as $person) {
                /* @var $person PersonEntity */
                $publicationLink->getPoliceDatas()->add(
                    new PoliceDataEntity($publicationLink, $person)
                );
            }
        }
    }
}
