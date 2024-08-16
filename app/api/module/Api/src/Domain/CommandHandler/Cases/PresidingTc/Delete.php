<?php

/**
 * Delete Presiding TC
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete PresidingTc
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'PresidingTc';
}
