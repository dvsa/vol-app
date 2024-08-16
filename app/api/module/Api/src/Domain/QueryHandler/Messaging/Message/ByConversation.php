<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Message;

use ArrayIterator;
use DateTime;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingUserMessageRead;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Query\Messaging\Messages\ByConversation as ByConversationQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class ByConversation extends AbstractQueryHandler implements ToggleRequiredInterface, AuthAwareInterface
{
    use ToggleAwareTrait;
    use AuthAwareTrait;

    protected array $toggleConfig = [FeatureToggle::MESSAGING];

    protected $extraRepos = [
        Repository\Message::class,
        Repository\Conversation::class,
        Repository\MessagingUserMessageRead::class,
    ];

    /** @param QueryInterface|ByConversationQuery $query */
    public function handleQuery(QueryInterface $query)
    {
        $messageRepository = $this->getRepo(Repository\Message::class);

        $messageQueryBuilder = $messageRepository->getBaseMessageListWithContentQuery($query);
        $messagesQuery =
            $messageRepository->filterByConversationId($messageQueryBuilder, (int)$query->getConversation());

        if ($query->getIncludeReadRoles()) {
            $messagesQuery = $messageRepository->addReadersToMessages($messagesQuery);
        }

        $messages = $messageRepository->fetchPaginatedList($messagesQuery, Query::HYDRATE_ARRAY, $query);
        $messages = $messages->getArrayCopy();

        if ($query->getIncludeReadRoles() && count($query->getReadRoles()) > 0) {
            $messages = $this->filterReadHistory($messages, $query->getReadRoles());
        }

        /** @var MessagingConversation $conversation */
        $conversation = $this->getRepo(Repository\Conversation::class)->fetchById($query->getConversation());
        $application = $conversation->getTask()->getApplication();

        $this->markMessagesAsReadByCurrentUser($messages);

        return [
            'result'       => $messages,
            'count'        => $messageRepository->fetchPaginatedCount($messagesQuery),
            'licence'      => $conversation->getRelatedLicence()->serialize(),
            'application'  => $application ? $application->serialize() : null,
            'conversation' => $conversation->serialize(),
        ];
    }

    private function markMessagesAsReadByCurrentUser(array $messages): void
    {
        $currentDatetime = new DateTime();

        $messageRepo = $this->getRepo(Repository\Message::class);
        $userMessageReadRepo = $this->getRepo(Repository\MessagingUserMessageRead::class);

        foreach ($messages as $message) {
            $messageId = $message['id'];
            $message = $messageRepo->fetchById($messageId);
            try {
                $messageUserRead = $userMessageReadRepo->fetchByMessageIdAndUserId($messageId, $this->getUser()->getId());
            } catch (NoResultException) {
                $messageUserRead = new MessagingUserMessageRead();
                $messageUserRead->setMessagingMessage($message);
                $messageUserRead->setUser($this->getUser());
            } finally {
                $messageUserRead->setLastReadOn($currentDatetime);
                $userMessageReadRepo->save($messageUserRead);
            }
        }
    }

    private function filterReadHistory(array $messages, array $readRoles): array
    {
        array_walk(
            $messages,
            function (&$message) use ($readRoles) {
                $message['userMessageReads'] = array_filter(
                    $message['userMessageReads'],
                    fn($umr) => count(array_filter($umr['user']['roles'], fn($r) => in_array($r['role'], $readRoles))) > 0,
                );

                usort($message['userMessageReads'], fn($a, $b) => $a['createdOn'] <=> $b['createdOn']);
            },
        );

        return $messages;
    }
}
