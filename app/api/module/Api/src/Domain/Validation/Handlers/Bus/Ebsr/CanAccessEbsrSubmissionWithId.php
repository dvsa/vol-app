<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Can access EBSR submission with an ID
 */
class CanAccessEbsrSubmissionWithId extends AbstractHandler
{
    /**
     * Validate DTO
     *
     * @param CommandInterface|QueryInterface $dto dto being validated
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canAccessEbsrSubmission($dto->getId());
    }
}
