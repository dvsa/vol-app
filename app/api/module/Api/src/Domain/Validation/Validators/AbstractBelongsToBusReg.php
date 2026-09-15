<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Bus\BusReg;

/**
 * Abstract Belongs To Bus Reg
 *
 * Confirms a child entity is attached to a given bus registration. Concrete
 * validators just declare the child $repo (used when an id rather than an entity
 * is passed).
 */
abstract class AbstractBelongsToBusReg extends AbstractBelongsToValidator
{
    #[\Override]
    protected function getRelatedEntity(object $entity): ?BusReg
    {
        return $entity->getBusReg();
    }
}
