<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\LetterTodo;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

// phpcs:disable Generic.Commenting.Todo.TaskFound
// phpcs:enable Generic.Commenting.Todo.TaskFound
#[Transfer\RouteName('backend/letter/letter-todo/single')]
final class Get extends AbstractQuery
{
    use Identity;
}
