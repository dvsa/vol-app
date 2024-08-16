<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * ApplicationReadAudit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="application_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_audit_read_application_application_id", columns={"application_id"}),
 *        @ORM\Index(name="ix_audit_read_application_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_audit_read_application_created_on", columns={"created_on"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_audit_read_application_application_id_user_id_created_on",
 *        columns={"application_id","user_id","created_on"})
 *    }
 * )
 */
class ApplicationReadAudit extends AbstractApplicationReadAudit
{
    public function __construct(User $user, Application $application)
    {
        $this->setUser($user);
        $this->setApplication($application);
    }
}
