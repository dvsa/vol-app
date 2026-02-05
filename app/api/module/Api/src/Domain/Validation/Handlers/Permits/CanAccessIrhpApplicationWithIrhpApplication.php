<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Permits;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Can access an IRHP application with IrhpApplication
 */
class CanAccessIrhpApplicationWithIrhpApplication extends CanAccessIrhpApplicationWithId
{
    /**
     * Get id
     *
     * @param CommandInterface|QueryInterface $dto transfer object
     *
     * @return int
     */
    #[\Override]
    protected function getId($dto)
    {
        return $dto->getIrhpApplication();
    }
}
