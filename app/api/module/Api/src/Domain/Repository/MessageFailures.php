<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\LicenceStatusAwareTrait;
use Dvsa\Olcs\Api\Entity\MessageFailures as Entity;

/**
 * MessageFailures
 *
 * @author Hijas Veerasan <hijas.veerasan@bjss.com>
 */
class MessageFailures extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'mf';
}
