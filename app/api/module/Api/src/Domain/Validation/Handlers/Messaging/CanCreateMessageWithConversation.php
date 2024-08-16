<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Messaging;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Domain\Repository\MessagingConversation as ConversationRepo;

/**
 * Can create Message for a Licence or Application
 */
class CanCreateMessageWithConversation extends AbstractHandler implements AuthAwareInterface, RepositoryManagerAwareInterface
{
    use AuthAwareTrait;
    use RepositoryManagerAwareTrait;

    /**
     * Validate DTO
     *
     * @param \Dvsa\Olcs\Transfer\Query\QueryInterface $dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        // Exit early if no permission
        if (!$this->isGranted(Permission::CAN_REPLY_TO_CONVERSATIONS)) {
            return false;
        }

        $conversationId = $dto->getConversation();
        $conversationEntity = $this->getRepo(ConversationRepo::class)->fetchById($conversationId);
        /**
         * @var $conversationEntity
         */
        if (empty($conversationEntity)) {
            throw new NotFoundException('Could not check status of Messaging Conversation with id ' . $conversationId . ' as it was not found');
        }

        return !$conversationEntity->getIsClosed()
            &&
            $this->canAccessConversation($conversationId);
    }
}
