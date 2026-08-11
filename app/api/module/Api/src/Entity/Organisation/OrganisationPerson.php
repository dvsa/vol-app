<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * OrganisationPerson Entity
 */
#[ORM\Table(name: 'organisation_person')]
#[ORM\Index(name: 'ix_organisation_person_person_id', columns: ['person_id'])]
#[ORM\Index(name: 'ix_organisation_person_organisation_id', columns: ['organisation_id'])]
#[ORM\Index(name: 'ix_organisation_person_created_by', columns: ['created_by'])]
#[ORM\Index(name: 'ix_organisation_person_last_modified_by', columns: ['last_modified_by'])]
#[ORM\UniqueConstraint(name: 'uk_organisation_person_olbs_key', columns: ['olbs_key'])]
#[ORM\Entity]
class OrganisationPerson extends AbstractOrganisationPerson implements OrganisationProviderInterface
{
    /**
     * @inheritdoc
     */
    #[\Override]
    public function getRelatedOrganisation()
    {
        return $this->getOrganisation();
    }
}
