<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\Statement;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete Statement class.
 */
#[Transfer\RouteName('backend/statement/single')]
#[Transfer\Method('DELETE')]
class DeleteStatement extends AbstractDeleteCommand
{
    //
}
