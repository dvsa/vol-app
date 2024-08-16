<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApplicationOrganisationPerson Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="application_organisation_person",
 *    indexes={
 *        @ORM\Index(name="ix_application_organisation_person_person_id", columns={"person_id"}),
 *        @ORM\Index(name="ix_application_organisation_person_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_application_organisation_person_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_application_organisation_person_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_application_organisation_person_created_by", columns={"created_by"}),
 *        @ORM\Index(name="fk_application_organisation_person_person1_idx", columns={"original_person_id"})
 *    }
 * )
 */
class ApplicationOrganisationPerson extends AbstractApplicationOrganisationPerson
{
    public const ACTION_ADD = 'A';
    public const ACTION_DELETE = 'D';
    public const ACTION_UPDATE = 'U';

    public function __construct(
        Application $application,
        \Dvsa\Olcs\Api\Entity\Organisation\Organisation $organisation,
        \Dvsa\Olcs\Api\Entity\Person\Person $person
    ) {
        $this->setApplication($application);
        $this->setOrganisation($organisation);
        $this->setPerson($person);
    }
}
