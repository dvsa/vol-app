<?php

namespace Dvsa\Olcs\Api\Entity\Task;

use Doctrine\ORM\Mapping as ORM;

/**
 * TaskAllocationRule Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="task_allocation_rule",
 *    indexes={
 *        @ORM\Index(name="ix_task_allocation_rule_category_id", columns={"category_id"}),
 *        @ORM\Index(name="ix_task_allocation_rule_team_id", columns={"team_id"}),
 *        @ORM\Index(name="ix_task_allocation_rule_user_id", columns={"user_id"}),
 *        @ORM\Index(name="ix_task_allocation_rule_goods_or_psv", columns={"goods_or_psv"}),
 *        @ORM\Index(name="ix_task_allocation_rule_traffic_area_id", columns={"traffic_area_id"})
 *    }
 * )
 */
class TaskAllocationRule extends AbstractTaskAllocationRule
{
}
