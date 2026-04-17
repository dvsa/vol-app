<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterChoice;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterChoice
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterChoice';
}
