<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterSection;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete LetterSection
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'LetterSection';
}
