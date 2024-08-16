<?php

/**
 * RegenerateIssueFee
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\IrhpApplication;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

final class RegenerateIssueFee extends AbstractCommand
{
    use Identity;
}
