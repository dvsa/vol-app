<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

abstract class AbstractIdOnlyCommand extends AbstractCommand
{
    use Identity;
}
