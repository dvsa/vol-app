<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\User;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can access user list
 */
class CanAccessUserList extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Is valid
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto Dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        if ($this->isInternalUser()) {
            return true;
        }

        if (method_exists($dto, 'getOrganisation') && $dto->getOrganisation()) {
            return $this->canAccessOrganisation($dto->getOrganisation());
        }

        return false;
    }
}
