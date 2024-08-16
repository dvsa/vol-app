<?php

/**
 * Snapshot
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Snapshot
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class Snapshot extends AbstractIdOnlyCommand
{
    protected $user;

    public function getUser()
    {
        return $this->user;
    }
}
