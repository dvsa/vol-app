<?php

namespace Dvsa\Olcs\Transfer\Command\Disqualification;

use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/disqualification/single')]
#[Transfer\Method('DELETE')]
final class Delete extends AbstractDeleteCommand
{
}
