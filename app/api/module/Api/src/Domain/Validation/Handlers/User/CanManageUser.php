<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\User;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can manage user
 */
class CanManageUser extends AbstractHandler
{
    /**
     * Is valid
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto Dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canManageUser($this->getId($dto));
    }

    /**
     * Get the user id
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto Dto
     *
     * @return int|null
     */
    protected function getId($dto)
    {
        if (method_exists($dto, 'getId')) {
            return $dto->getId();
        }

        return null;
    }
}
