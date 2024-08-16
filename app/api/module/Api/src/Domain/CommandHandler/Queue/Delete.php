<?php

/**
 * Delete a queue item
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;

/**
 * Delete a queue item
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Queue';
}
