<?php

namespace Dvsa\Olcs\Api\Domain\Command\Permits;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;

/**
 * Proceed to status
 */
final class ProceedToStatus extends AbstractCommand
{
    use Ids;

    protected $status;

    public function getStatus()
    {
        return $this->status;
    }
}
