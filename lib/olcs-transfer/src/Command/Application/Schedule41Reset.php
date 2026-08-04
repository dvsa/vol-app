<?php

/**
 * Schedule41Reset.php
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/reset-schedule-41")
 * @Transfer\Method("PUT")
 */
final class Schedule41Reset extends AbstractCommand
{
    use Identity;
}
