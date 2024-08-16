<?php

/**
 * Delete Opposition
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Opposition;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;

/**
 * Delete Opposition
 */
final class DeleteOpposition extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Opposition';
}
