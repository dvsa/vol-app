<?php

namespace Dvsa\Olcs\Transfer\Command\Bus;

use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/bus/single/print/reg-letter")
 * @Transfer\Method("POST")
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class PrintLetter extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\PrintOptional;
}
