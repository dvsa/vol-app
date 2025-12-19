<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterAppendix;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterAppendix
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterAppendix';
}
