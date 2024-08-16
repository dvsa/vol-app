<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\User;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can read user record
 */
class CanReadUser extends AbstractHandler
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
        return $this->canReadUser($dto->getId());
    }
}
