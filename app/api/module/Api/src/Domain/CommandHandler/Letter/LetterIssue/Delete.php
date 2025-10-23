<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterIssue
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterIssue';
}
