<?php

/**
 * Address
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\ContactDetails\Address as Entity;

/**
 * Address
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Address extends AbstractRepository
{
    protected $entity = Entity::class;
}
