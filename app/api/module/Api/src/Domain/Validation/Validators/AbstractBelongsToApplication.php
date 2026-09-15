<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Abstract Belongs To Application
 *
 * Confirms a child entity is attached to a given application. Concrete validators
 * just declare the child $repo (used when an id rather than an entity is passed).
 */
abstract class AbstractBelongsToApplication extends AbstractBelongsToValidator
{
    #[\Override]
    protected function getRelatedEntity(object $entity): ?Application
    {
        return $entity->getApplication();
    }
}
