<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterInstanceTodo Entity
 */
#[ORM\Table(name: 'letter_instance_todo')]
#[ORM\Entity]
class LetterInstanceTodo extends AbstractLetterInstanceTodo
{
}
