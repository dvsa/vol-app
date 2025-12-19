<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete MasterTemplate
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'MasterTemplate';
}
