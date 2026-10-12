<?php

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
#[Transfer\RouteName('backend/bus/single/print/reg-letter')]
#[Transfer\Method('POST')]
class PrintLetter extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\PrintOptional;
}
