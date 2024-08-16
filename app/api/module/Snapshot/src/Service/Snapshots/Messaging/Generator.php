<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Messaging;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\Repository\Message as MessageRepo;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGenerator;
use Dvsa\Olcs\Snapshot\Service\Snapshots\AbstractGeneratorServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\SnapshotGeneratorInterface;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation;

class Generator extends AbstractGenerator implements SnapshotGeneratorInterface
{
    protected MessagingConversation $conversation;

    public function __construct(
        AbstractGeneratorServices $abstractGeneratorServices,
        protected MessageRepo $messageRepository
    ) {
        parent::__construct($abstractGeneratorServices);
    }

    public function setData($data): void
    {
        $this->conversation = $data['entity'];
    }

    public function generate(): string
    {
        $query = $this->messageRepository->getBaseMessageListWithContentQuery(
            ByConversation::create(
                [
                    'page'  => 1,
                    'limit' => 1000,
                ],
            ),
        );
        $query = $this->messageRepository->filterByConversationId($query, $this->conversation->getId());
        /** @var MessagingMessage[] $messages */
        $messages = $this->messageRepository->fetchPaginatedList($query, AbstractQuery::HYDRATE_OBJECT);

        return $this->generateReadonly(
            [
                'conversation' => $this->conversation,
                'messages'     => $messages,
            ],
            'conversation',
        );
    }
}
