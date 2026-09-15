<?php

namespace Dvsa\Olcs\Api\Entity\Task;

use Doctrine\ORM\Mapping as ORM;

/**
 * TaskAlphaSplit Entity
 */
#[ORM\Table(name: 'task_alpha_split')]
#[ORM\Index(name: 'ix_task_alpha_split_task_allocation_rule_id', columns: ['task_allocation_rule_id'])]
#[ORM\Index(name: 'ix_task_alpha_split_user_id', columns: ['user_id'])]
#[ORM\Entity]
class TaskAlphaSplit extends AbstractTaskAlphaSplit
{
}
