<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterInstanceTodo Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_instance_todo",
 *    indexes={
 *        @ORM\Index(name="ix_letter_instance_todo_letter_todo_version_id", columns={"letter_todo_version_id"}),
 *        @ORM\Index(name="ix_letter_instance_todo_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_instance_todo_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterInstanceTodo extends AbstractLetterInstanceTodo
{
}