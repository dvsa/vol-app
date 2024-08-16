<?php

/**
 * Process Continuation Not Sought
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Licence;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;

/**
 * Process Continuation Not Sought
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class ProcessContinuationNotSought extends AbstractIdOnlyCommand
{
    use Version;
}
