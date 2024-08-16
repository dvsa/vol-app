<?php

namespace Dvsa\Olcs\Api\Entity\Cases;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * CasesReadAudit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="cases_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_audit_read_cases_cases_id", columns={"case_id"}),
 *        @ORM\Index(name="ix_audit_read_cases_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_audit_read_cases_created_on", columns={"created_on"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_cases_read_audit_case_id_user_id_created_on",
 *        columns={"case_id","user_id","created_on"})
 *    }
 * )
 */
class CasesReadAudit extends AbstractCasesReadAudit
{
    public function __construct(User $user, Cases $case)
    {
        $this->setUser($user);
        $this->setCase($case);
    }
}
