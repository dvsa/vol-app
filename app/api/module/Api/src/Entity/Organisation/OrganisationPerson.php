<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * OrganisationPerson Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="organisation_person",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_person_person_id", columns={"person_id"}),
 *        @ORM\Index(name="ix_organisation_person_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_organisation_person_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_organisation_person_last_modified_by", columns={"last_modified_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_organisation_person_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class OrganisationPerson extends AbstractOrganisationPerson implements OrganisationProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getRelatedOrganisation()
    {
        return $this->getOrganisation();
    }
}
