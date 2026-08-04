<?php

/**
 * Delete Operating Centres
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/named-single/operating-centre")
 * @Transfer\Method("DELETE")
 */
final class DeleteOperatingCentres extends AbstractCommand
{
    use Ids;
    use Application;
}
