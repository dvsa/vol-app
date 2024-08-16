<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Can edit a permit app with id
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CanEditIrhpApplicationWithId extends AbstractHandler
{
    /**
     * Whether the user can edit the IRHP application
     *
     * @param CommandInterface|QueryInterface $dto transfer object
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canEditIrhpApplicationWithId($dto->getId());
    }
}
