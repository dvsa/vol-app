<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * OrganisationReadAudit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="organisation_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_audit_read_organisation_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_audit_read_organisation_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_audit_read_organisation_created_on", columns={"created_on"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_audit_read_organisation_organisation_id_user_id_created_on",
 *        columns={"organisation_id","user_id","created_on"})
 *    }
 * )
 */
class OrganisationReadAudit extends AbstractOrganisationReadAudit
{
    public function __construct(User $user, Organisation $organisation)
    {
        $this->setUser($user);
        $this->setOrganisation($organisation);
    }
}
