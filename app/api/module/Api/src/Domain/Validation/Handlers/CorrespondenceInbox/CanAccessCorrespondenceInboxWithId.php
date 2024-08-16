<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\CorrespondenceInbox;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access a CorrespondenceInbox entity with an ID
 */
class CanAccessCorrespondenceInboxWithId extends AbstractHandler
{
    /**
     * Validate DTO
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->canAccessCorrespondenceInbox($this->getId($dto));
    }

    /**
     * Get the correspondence ID
     *
     * @param \Dvsa\Olcs\Transfer\Command\CommandInterface|\Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return int
     */
    protected function getId($dto)
    {
        return $dto->getId();
    }
}
