<?php

/**
 * Tm Nominated Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * Tm Nominated Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class TmNominatedTask extends AbstractCommand
{
    use Ids;
}
