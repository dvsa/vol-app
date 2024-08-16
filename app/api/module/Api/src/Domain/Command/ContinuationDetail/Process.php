<?php

/**
 * Process Continuation Detail
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Command\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Process Continuation Detail
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Process extends AbstractIdOnlyCommand
{
    protected $user;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
