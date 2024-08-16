<?php

/**
 * CreateDefaultIrhpPermitApplications
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\YearOptional;

final class CreateDefaultIrhpPermitApplications extends AbstractCommand
{
    use Identity;
    use IrhpPermitStockOptional;
}
