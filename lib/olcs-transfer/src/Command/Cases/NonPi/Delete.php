<?php

namespace Dvsa\Olcs\Transfer\Command\Cases\NonPi;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * Concrete delete class.
 */
#[Transfer\RouteName('backend/non-pi/single')]
#[Transfer\Method('DELETE')]
class Delete extends AbstractDeleteCommand
{
    //
}
