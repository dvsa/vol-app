<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * LicenceReadAudit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="licence_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_audit_read_licence_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_audit_read_licence_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_audit_read_licence_created_on", columns={"created_on"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_audit_read_licence_licence_id_user_id_created_on",
 *        columns={"licence_id","user_id","created_on"})
 *    }
 * )
 */
class LicenceReadAudit extends AbstractLicenceReadAudit
{
    public function __construct(User $user, Licence $licence)
    {
        $this->setUser($user);
        $this->setLicence($licence);
    }
}
