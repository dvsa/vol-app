<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Conversations;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Domain\Repository\Conversation as ConversationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Message as MessageRepo;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByOrganisation as GetConversationsByOrganisationQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class ByOrganisation extends AbstractConversationQueryHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [ConversationRepo::class, MessageRepo::class];

    /** @param GetConversationsByOrganisationQuery|QueryInterface $query */
    public function handleQuery(QueryInterface $query): array
    {
        $conversationRepo = $this->getRepo(ConversationRepo::class);
        $messageRepo = $this->getRepo(MessageRepo::class);

        $conversationsQuery = $conversationRepo->getByOrganisationId($query, (int)$query->getOrganisation());
        $conversationsQuery = $conversationRepo->applyOrderForListing($conversationsQuery, $this->getFilteringRoles());
        $conversationsQuery = $conversationRepo->filterByStatuses($conversationsQuery, $query->getStatuses());
        $conversations = $conversationRepo->fetchPaginatedList($conversationsQuery, AbstractQuery::HYDRATE_ARRAY, $query);

        foreach ($conversations as &$conversation) {
            $hasUnread = (int)$conversation['has_unread'];
            $conversation = $conversation[0];
            $conversation['userContextUnreadCount'] = $hasUnread;
            $conversation['userContextStatus'] = $this->stringifyMessageStatus($conversation, $hasUnread > 0);
            $conversation['latestMessage'] = $messageRepo->getLastMessageForConversation((int)$conversation['id']);
            unset($conversation);
        }

        return [
            'result' => $conversations,
            'count'  => $conversationRepo->fetchPaginatedCount($conversationsQuery),
        ];
    }
}
