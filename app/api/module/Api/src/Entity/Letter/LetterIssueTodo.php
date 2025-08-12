<?php

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterIssueTodo Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_issue_todo",
 *    indexes={
 *        @ORM\Index(name="ix_letter_issue_todo_letter_todo_version_id", columns={"letter_todo_version_id"}),
 *        @ORM\Index(name="ix_letter_issue_todo_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_letter_issue_todo_last_modified_by", columns={"last_modified_by"})
 *    }
 * )
 */
class LetterIssueTodo extends AbstractLetterIssueTodo
{
    /**
     * Get todo description
     *
     * @return string
     */
    public function getTodoDescription()
    {
        return $this->letterTodoVersion->getDescription();
    }

    /**
     * Get todo help text
     *
     * @return string|null
     */
    public function getTodoHelpText()
    {
        return $this->letterTodoVersion->getHelpText();
    }
}