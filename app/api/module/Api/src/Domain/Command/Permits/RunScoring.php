<?php

/**
 * Run scoring
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\DeviationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class RunScoring extends AbstractCommand
{
    use Identity;

    use DeviationOptional;
}
