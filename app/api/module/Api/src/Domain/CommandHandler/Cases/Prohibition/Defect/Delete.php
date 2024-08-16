<?php

/**
 * Delete Prohibition
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Prohibition\Defect;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractDeleteCommandHandler;
use Dvsa\Olcs\Api\Entity as Entities;
use Doctrine\ORM\Query;

/**
 * Delete Prohibition
 */
final class Delete extends AbstractDeleteCommandHandler
{
    protected $repoServiceName = 'ProhibitionDefect';
}
