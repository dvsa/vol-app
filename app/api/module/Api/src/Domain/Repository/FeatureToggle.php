<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\System\FeatureToggle as Entity;

/**
 * Feature toggle
 */
class FeatureToggle extends AbstractRepository
{
    protected $entity = Entity::class;
}
