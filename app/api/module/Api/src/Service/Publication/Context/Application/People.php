<?php

namespace Dvsa\Olcs\Api\Service\Publication\Context\Application;

use Dvsa\Olcs\Api\Service\Publication\Context\AbstractContext;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;

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
     *
     * @return \ArrayObject
     */
    public function provide(PublicationLink $publicationLink, \ArrayObject $context)
    {
        $people = [];

        // if we have a licence, populate with all people from that licence
        $licence = $publicationLink->getLicence();

        if ($licence instanceof LicenceEntity) {
            foreach ($licence->getOrganisation()->getOrganisationPersons() as $op) {
                /* @var $op \Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson */
                $people[$op->getPerson()->getId()] = $op->getPerson();
            }
        }

        // iterate application people
        foreach ($publicationLink->getApplication()->getApplicationOrganisationPersons() as $aop) {
            /* @var $aop \Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson */
            switch ($aop->getAction()) {
                case 'D':
                    if (isset($people[$aop->getPerson()->getId()])) {
                        unset($people[$aop->getPerson()->getId()]);
                    }
                    break;
                case 'U':
                    $people[$aop->getOriginalPerson()->getId()] = $aop->getPerson();
                    break;
                case 'A':
                    $people[$aop->getPerson()->getId()] = $aop->getPerson();
                    break;
            }
        }

        $context->offsetSet('applicationPeople', $people);

        return $context;
    }
}
