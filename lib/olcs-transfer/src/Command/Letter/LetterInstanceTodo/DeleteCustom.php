<?php

namespace Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceTodo;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Delete a caseworker-written to-do from a letter (VOL-7735)
 */
#[Transfer\RouteName('backend/letter/letter-instance-todo/single')]
#[Transfer\Method('DELETE')]
final class DeleteCustom extends AbstractDeleteCommand
{
}
