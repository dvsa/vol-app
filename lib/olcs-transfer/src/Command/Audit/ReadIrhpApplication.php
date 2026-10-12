<?php

namespace Dvsa\Olcs\Transfer\Command\Audit;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/audit/read/irhp-application')]
#[Transfer\Method('POST')]
final class ReadIrhpApplication extends AbstractCommand
{
    use Identity;
}
