<?php

namespace Dvsa\Olcs\Api\Entity\Licence;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * LicenceReadAudit Entity
 */
#[ORM\Table(name: 'licence_read_audit')]
#[ORM\UniqueConstraint(name: 'uk_audit_read_licence_licence_id_user_id_created_on', columns: ['licence_id', 'user_id', 'created_on'])]
#[ORM\Entity]
class LicenceReadAudit extends AbstractLicenceReadAudit
{
    public function __construct(User $user, Licence $licence)
    {
        $this->setUser($user);
        $this->setLicence($licence);
    }
}
