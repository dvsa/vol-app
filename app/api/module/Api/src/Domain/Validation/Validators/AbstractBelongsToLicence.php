<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Licence\Licence;

/**
 * Abstract Belongs To Licence
 *
 * Confirms a child entity is attached to a given licence. Concrete validators
 * just declare the child $repo (used when an id rather than an entity is passed).
 */
abstract class AbstractBelongsToLicence extends AbstractBelongsToValidator
{
    #[\Override]
    protected function getRelatedEntity(object $entity): ?Licence
    {
        return $entity->getLicence();
    }
}
