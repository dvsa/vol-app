<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Disqualification;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Class Delete
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Disqualification
 */
class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Disqualification';
}
