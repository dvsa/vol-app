<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\LetterTodo;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

// phpcs:disable Generic.Commenting.Todo.TaskFound
// phpcs:enable Generic.Commenting.Todo.TaskFound
#[Transfer\RouteName('backend/letter/letter-todo')]
final class GetList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
}
