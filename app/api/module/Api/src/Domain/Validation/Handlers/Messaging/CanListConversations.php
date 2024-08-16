<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Can List Conversation messages
 */
class CanListConversations extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Validate DTO
     *
     * @param \Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        return $this->isGranted(Permission::CAN_LIST_CONVERSATIONS);
    }
}
