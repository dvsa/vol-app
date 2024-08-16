<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * @author Alex Peshkov <alex.peshkov@valtech.com>
 */
class CanEditBusRegWithId extends AbstractHandler
{
    /**
     * Check is can edit Bus Registration
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto Dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canEditBusReg($dto->getId());
    }
}
