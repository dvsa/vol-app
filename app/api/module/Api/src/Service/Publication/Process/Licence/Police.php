<?php

namespace Dvsa\Olcs\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData as PoliceDataEntity;

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
    }

    /**
     * Add all People from context into the police data
     */
    private function addPeople(PublicationLink $publicationLink, ImmutableArrayObject $context)
    {
        if ($context->offsetExists('licencePeople')) {
            // add each person populated from context
            foreach ($context->offsetGet('licencePeople') as $person) {
                /* @var $person PersonEntity */
                $publicationLink->getPoliceDatas()->add(
                    new PoliceDataEntity($publicationLink, $person)
                );
            }
        }
    }
}
