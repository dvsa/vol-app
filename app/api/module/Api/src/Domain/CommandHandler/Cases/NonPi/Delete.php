<?php

/**
 * Delete NonPi
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\NonPi;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;

/**
 * Delete NonPi
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'NonPi';
}
