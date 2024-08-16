<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Can update TxcInbox record with an ID
 */
class CanUpdateTxcInboxRecords extends AbstractHandler
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
        return $this->canUpdateTxcInbox($dto->getIds());
    }
}
