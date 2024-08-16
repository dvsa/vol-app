<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Can access an IRHP application with id
 */
class CanAccessIrhpApplicationWithId extends AbstractHandler
{
    /**
     * Whether the user can access the IRHP application
     *
     * @param CommandInterface|QueryInterface $dto transfer object
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canAccessIrhpApplicationWithId($this->getId($dto));
    }

    /**
     * Get id
     *
     * @param CommandInterface|QueryInterface $dto transfer object
     *
     * @return int
     */
    protected function getId($dto)
    {
        return $dto->getId();
    }
}
