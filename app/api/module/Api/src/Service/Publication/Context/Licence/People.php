<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;

/**
 * People
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class People extends AbstractContext
{
    /**
     * @param PublicationLink $publicationLink
     * @param \ArrayObject $context
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $people = [];
        // populate with all people from licence
        foreach ($publicationLink->getLicence()->getOrganisation()->getOrganisationPersons() as $op) {
            /* @var $op \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson */
            $people[$op->getPerson()->getId()] = $op->getPerson();
        }

        $context->offsetSet('licencePeople', $people);
    }
}
