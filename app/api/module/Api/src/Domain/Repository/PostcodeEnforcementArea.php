<?php

/**
 * Postcode Enforcement Area
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\EnforcementArea\PostcodeEnforcementArea as Entity;

/**
 * Postcode Enforcement Area
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PostcodeEnforcementArea extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchByPostcodeId($postcodeId): ?Entity
    {
        return $this->getRepository()->findOneBy(['postcodeId' => $postcodeId]);
    }
}
