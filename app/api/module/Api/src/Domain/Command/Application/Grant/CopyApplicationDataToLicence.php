<?php

/**
 * Copy Application Data To Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Copy Application Data To Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CopyApplicationDataToLicence extends AbstractCommand
{
    use Identity;
}
