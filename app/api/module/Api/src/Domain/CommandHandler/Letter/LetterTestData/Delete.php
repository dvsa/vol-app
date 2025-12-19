<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTestData;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterTestData
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterTestData';
}
