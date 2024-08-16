<?php

namespace Dvsa\Olcs\Api\Domain\Command\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;

/**
 * Class GenerateCheckListReminder
 */
final class GenerateCheckListReminder extends AbstractIdOnlyCommand
{
    protected $user;

    /**
     * Get user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}
