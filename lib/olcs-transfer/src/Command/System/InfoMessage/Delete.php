<?php

namespace Dvsa\Olcs\Transfer\Command\System\InfoMessage;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/system-info-message/single')]
#[Transfer\Method('DELETE')]
class Delete extends AbstractCommand
{
    use Identity;
}
