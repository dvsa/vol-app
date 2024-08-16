<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrganisationType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="organisation_type",
 *    indexes={
 *        @ORM\Index(name="ix_organisation_type_org_type_id", columns={"org_type_id"}),
 *        @ORM\Index(name="ix_organisation_type_org_person_type_id", columns={"org_person_type_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_org_person", columns={"org_type_id","org_person_type_id"})
 *    }
 * )
 */
class OrganisationType extends AbstractOrganisationType
{
}
