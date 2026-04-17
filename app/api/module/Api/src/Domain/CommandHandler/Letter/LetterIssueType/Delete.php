<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterIssueType
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterIssueType';
}
