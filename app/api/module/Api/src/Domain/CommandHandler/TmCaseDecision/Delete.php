<?php

/**
 * Delete TmCaseDecision
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete TmCaseDecision
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'TmCaseDecision';
}
