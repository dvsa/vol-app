<?php

namespace Dvsa\Olcs\Api\Entity\Task;

use Doctrine\ORM\Mapping as ORM;

/**
 * TaskAlphaSplit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="task_alpha_split",
 *    indexes={
 *        @ORM\Index(name="ix_task_alpha_split_task_allocation_rule_id",
     *     columns={"task_allocation_rule_id"}),
 *        @ORM\Index(name="ix_task_alpha_split_user_id", columns={"user_id"})
 *    }
 * )
 */
class TaskAlphaSplit extends AbstractTaskAlphaSplit
{
}
