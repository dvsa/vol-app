<?php

/**
 * Can Access Organisation With Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Can Access Organisation
 */
class CanAccessOrganisationAuthAware extends CanAccessOrganisationWithId implements AuthAwareInterface
{
    use AuthAwareTrait;

    #[\Override]
    protected function getId($dto)
    {
        return (int)($dto->getOrganisation() ?: $this->getCurrentOrganisation()->getId());
    }
}
