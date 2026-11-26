<?php

namespace Dvsa\Olcs\Transfer\Command\Messaging\Subject;

use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/messaging/subjects/single')]
#[Transfer\Method('DELETE')]
final class Delete extends AbstractDeleteCommand
{
}
