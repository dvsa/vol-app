<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterType
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterType';
}
