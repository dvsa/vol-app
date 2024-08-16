<?php

namespace Dvsa\Olcs\Api\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * RolePermission Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="role_permission",
 *    indexes={
 *        @ORM\Index(name="ix_role_permission_permission_id", columns={"permission_id"}),
 *        @ORM\Index(name="ix_role_permission_role_id", columns={"role_id"}),
 *        @ORM\Index(name="ix_role_permission_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_role_permission_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class RolePermission extends AbstractRolePermission
{
}
