<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\Letter;

use Doctrine\ORM\Mapping as ORM;

/**
 * LetterIssueTodo Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="letter_issue_todo")
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
