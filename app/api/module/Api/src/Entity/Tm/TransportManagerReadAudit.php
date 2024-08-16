<?php

namespace Dvsa\Olcs\Api\Entity\Tm;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * TransportManagerReadAudit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="transport_manager_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_audit_read_transport_manager_transport_manager_id",
 *        columns={"transport_manager_id"}),
 *        @ORM\Index(name="ix_audit_read_transport_manager_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_audit_read_transport_manager_created_on", columns={"created_on"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_audit_read_tm_tm_id_user_id_created_on",
 *        columns={"transport_manager_id","user_id","created_on"})
 *    }
 * )
 */
class TransportManagerReadAudit extends AbstractTransportManagerReadAudit
{
    public function __construct(User $user, TransportManager $tm)
    {
        $this->setUser($user);
        $this->setTransportManager($tm);
    }
}
