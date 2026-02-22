<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Can List Conversation messages by Licence
 */
class CanListConversationsByLicence extends CanListConversations implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Validate DTO
     *
     * @param \Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return bool
     */
    #[\Override]
    public function isValid($dto)
    {
        if (!$this->canAccessLicence($dto->getLicence())) {
            return false;
        };

        return parent::isValid($dto);
    }
}
