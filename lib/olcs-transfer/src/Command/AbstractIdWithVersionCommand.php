<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;

abstract class AbstractIdWithVersionCommand extends AbstractIdOnlyCommand
{
    use Version;
}
