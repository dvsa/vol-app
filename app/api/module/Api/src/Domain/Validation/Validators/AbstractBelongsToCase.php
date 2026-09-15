<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Cases\Cases;

/**
 * Abstract Belongs To Case
 *
 * Confirms a child entity is attached to a given case. Concrete validators just
 * declare the child $repo (used when an id rather than an entity is passed).
 */
abstract class AbstractBelongsToCase extends AbstractBelongsToValidator
{
    #[\Override]
    protected function getRelatedEntity(object $entity): ?Cases
    {
        return $entity->getCase();
    }
}
