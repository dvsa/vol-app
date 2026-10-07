<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterTodo;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

// phpcs:disable Generic.Commenting.Todo.TaskFound
// phpcs:enable Generic.Commenting.Todo.TaskFound
#[Transfer\RouteName('backend/letter/letter-todo/single')]
#[Transfer\Method('DELETE')]
final class Delete extends AbstractDeleteCommand
{
}
