<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Can Create new Conversation
 */
class CanCreateConversationForOrganisation extends AbstractHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Validate DTO
     *
     * @param \Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create $dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        if (!$this->isGranted(Permission::CAN_CREATE_CONVERSATION)) {
            return false;
        }

        $canAccessObject = false;
        if (!empty($dto->getLicence())) {
            $canAccessObject = $this->canAccessLicence($dto->getLicence());
        } elseif (!empty($dto->getApplication())) {
            $canAccessObject = $this->canAccessApplication($dto->getApplication());
        }

        return $canAccessObject;
    }
}
