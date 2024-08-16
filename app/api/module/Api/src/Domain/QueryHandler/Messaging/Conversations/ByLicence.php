<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Conversations;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByLicence as ByLicenceQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class ByLicence extends AbstractConversationQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [Repository\Conversation::class, Repository\Message::class];

    /** @param ByLicenceQuery|QueryInterface $query */
    public function handleQuery(QueryInterface $query)
    {
        $conversationRepository = $this->getRepo(Repository\Conversation::class);

        $conversationsQuery = $conversationRepository->getBaseConversationListQuery($query);
        $conversationsQuery = $conversationRepository->filterByLicenceId($conversationsQuery, $query->getLicence());
        $conversationsQuery = $conversationRepository->applyOrderForListing($conversationsQuery, $this->getFilteringRoles());
        $conversationsQuery = $conversationRepository->filterByStatuses($conversationsQuery, $query->getStatuses());

        $conversations = $conversationRepository->fetchPaginatedList($conversationsQuery, AbstractQuery::HYDRATE_ARRAY, $query);

        foreach ($conversations as &$conversation) {
            $hasUnread = (int)$conversation['has_unread'];
            $conversation = $conversation[0];
            $conversation['userContextUnreadCount'] = $hasUnread;
            $conversation['userContextStatus'] = $this->stringifyMessageStatus($conversation, $hasUnread > 0);
            $conversation['latestMessage'] = $this->getLatestMessageMetadata((int)$conversation['id']);
            unset($conversation);
        }

        return [
            'result' => $conversations,
            'count'  => $conversationRepository->fetchPaginatedCount($conversationsQuery),
        ];
    }

    private function getLatestMessageMetadata(int $conversationId): array
    {
        $messageRepository = $this->getRepo(Repository\Message::class);

        return $messageRepository->getLastMessageForConversation($conversationId);
    }
}
