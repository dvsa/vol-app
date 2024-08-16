<?php

/**
 * Process Continuation Detail Checklist Reminder Letter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Command\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Process Continuation Detail Checklist Reminder Letter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class ProcessReminder extends AbstractIdOnlyCommand
{
    public $user;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
