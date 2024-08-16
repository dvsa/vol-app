<?php

/**
 * Trading Name
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Organisation\TradingName as Entity;

/**
 * Trading Name
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TradingName extends AbstractRepository
{
    protected $entity = Entity::class;
}
