<?php

namespace Dvsa\Olcs\Api\Domain\Command\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\User;

/**
 * Create continuation detail snapshot
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateSnapshot extends AbstractIdOnlyCommand
{
    use User;
}
