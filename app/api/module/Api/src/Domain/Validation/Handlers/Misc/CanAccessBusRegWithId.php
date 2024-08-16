<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class CanAccessBusRegWithId extends AbstractHandler
{
    /**
     * Check is can access Bus Registration
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto Dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canAccessBusReg($dto->getId());
    }
}
