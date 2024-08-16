<?php

/**
 * Create Disc Records
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\Application\Grant;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * Create Disc Records
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateDiscRecords extends AbstractCommand
{
    use Identity;

    protected $currentTotAuth;

    /**
     * @return mixed
     */
    public function getCurrentTotAuth()
    {
        return $this->currentTotAuth;
    }
}
