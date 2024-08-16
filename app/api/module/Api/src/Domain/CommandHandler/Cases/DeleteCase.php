<?php

/**
 * Delete a case
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;

/**
 * Delete a case
 */
final class DeleteCase extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'Cases';
}
