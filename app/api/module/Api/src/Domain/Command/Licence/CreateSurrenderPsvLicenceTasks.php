<?php

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * Create surrender PSV licence tasks
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateSurrenderPsvLicenceTasks extends AbstractCommand
{
    use Ids;
}
