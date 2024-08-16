<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * BusRegReadAudit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="bus_reg_read_audit",
 *    indexes={
 *        @ORM\Index(name="ix_audit_read_bus_reg_bus_reg_id", columns={"bus_reg_id"}),
 *        @ORM\Index(name="ix_audit_read_bus_reg_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_audit_read_bus_reg_created_on", columns={"created_on"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_audit_read_bus_reg_bus_reg_id_user_id_created_on",
 *        columns={"bus_reg_id","user_id","created_on"})
 *    }
 * )
 */
class BusRegReadAudit extends AbstractBusRegReadAudit
{
    public function __construct(User $user, BusReg $busReg)
    {
        $this->setUser($user);
        $this->setBusReg($busReg);
    }
}
