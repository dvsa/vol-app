<?php

/**
 * Delete ConditionUndertaking
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;

/**
 * Delete ConditionUndertaking
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'ConditionUndertaking';
}
