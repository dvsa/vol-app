<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTodo;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterTodo
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterTodo';
}
