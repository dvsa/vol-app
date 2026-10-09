<?php

/**
 * Publish Bus
 */

namespace Dvsa\Olcs\Transfer\Command\Publication;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

#[Transfer\RouteName('backend/publication/bus')]
#[Transfer\Method('POST')]
final class Bus extends AbstractCommand
{
    use FieldType\Identity;
}
