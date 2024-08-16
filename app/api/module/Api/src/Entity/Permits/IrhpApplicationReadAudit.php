<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * IrhpApplicationReadAudit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_application_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_irhp_application_read_audit_irhp_application_id",
     *     columns={"irhp_application_id"}),
 *        @ORM\Index(name="ix_irhp_application_read_audit_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_irhp_application_read_audit_created_on", columns={"created_on"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_irhp_app_read_audit_irhp_app_id_user_id_created_on",
     *     columns={"irhp_application_id","user_id","created_on"})
 *    }
 * )
 */
class IrhpApplicationReadAudit extends AbstractIrhpApplicationReadAudit
{
    /**
     * Constructor
     *
     *
     * @return $this
     */
    public function __construct(User $user, IrhpApplication $irhpApplication)
    {
        $this->setUser($user);
        $this->setIrhpApplication($irhpApplication);
    }
}
