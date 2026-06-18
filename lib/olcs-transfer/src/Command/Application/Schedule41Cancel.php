<?php

/**
 * Schedule41Cancel.php
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/cancel-schedule-41")
 * @Transfer\Method("PUT")
 */
final class Schedule41Cancel extends AbstractCommand
{
    use Identity;
}
