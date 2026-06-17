<?php

/**
 * Read Organisation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Audit;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/audit/read/organisation")
 * @Transfer\Method("POST")
 */
final class ReadOrganisation extends AbstractCommand
{
    use Identity;
}
