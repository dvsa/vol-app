<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Message;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Email\SendNewMessageNotificationToOperators;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingContent;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingMessage;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use RuntimeException;

final class Create extends AbstractCommandHandler implements ToggleRequiredInterface, AuthAwareInterface, CacheAwareInterface
{
    use CacheAwareTrait;
    use ToggleAwareTrait;
    use AuthAwareTrait;

    public const string TASK_DESCRIPTION_ON_EXTERNAL_REPLY = 'New message';
    public const string TASK_DESCRIPTION_ON_INTERNAL_REPLY = 'Awaiting external response';

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [
        Repository\Conversation::class,
        Repository\Message::class,
        Repository\MessageContent::class,
        Repository\Task::class,
        Repository\Document::class,
    ];

    /**
     * @param CreateMessageCommand $command
     * @throws BadRequestException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        $message = $this->generateAndSaveMessage($command);
        $updatedTask = $this->updateTaskDescriptionAndActionDate($command);
        $sendEmailResult = $this->sendEmail($command);

        if ($command->getCorrelationId() !== null) {
            $this->assignUploadsToMessage($message, $command->getCorrelationId());
        }

        $result = new Result();

        $result->addId('message', $message->getId())
               ->addMessage('Message created')
               ->addId('messageContent', $message->getMessagingContent()->getId())
               ->addMessage('MessageContent created')
               ->addId('messageConversation', $message->getMessagingConversation()->getId())
               ->addMessage('Message added to conversation')
               ->addId('task', $updatedTask->getId())
               ->addMessage(
                   sprintf(
                       'Updated task action date: %s',
                       $updatedTask->getActionDate()->format(DateTimeInterface::ATOM),
                   ),
               )
               ->addMessage(sprintf('Updated task description: %s', $updatedTask->getDescription()));

        if ($sendEmailResult !== null) {
            $result->merge($sendEmailResult);
        }

        return $result;
    }

    private function generateAndSaveMessage(CreateMessageCommand $command): MessagingMessage
    {
        $conversation = $this->getConversationFromCommand($command);

        if ($conversation->getIsClosed() || $conversation->getIsArchived()) {
            throw new BadRequestException('Unable to create message on conversations that are closed or archived');
        }

        $messageContent = $this->createMessageContentEntity($command->getMessageContent());
        $message = $this->createMessageEntity($conversation, $messageContent);

        $this->getMessageContentRepository()->save($messageContent);
        $this->getMessageRepository()->save($message);

        return $message;
    }

    private function getConversationFromCommand(CreateMessageCommand $command): MessagingConversation
    {
        return $this->getConversationRepository()->fetchById($command->getConversation());
    }

    private function getConversationRepository(): Repository\Conversation
    {
        return $this->getRepo(Repository\Conversation::class);
    }

    private function createMessageContentEntity(string $text): MessagingContent
    {
        $entity = new MessagingContent();
        $entity->setText($text);
        return $entity;
    }

    private function createMessageEntity(
        MessagingConversation $messagingConversation,
        MessagingContent $messagingContent
    ): MessagingMessage {
        $entity = new MessagingMessage();
        $entity->setMessagingConversation($messagingConversation)->setMessagingContent($messagingContent);
        return $entity;
    }

    private function getMessageContentRepository(): Repository\MessageContent
    {
        return $this->getRepo(Repository\MessageContent::class);
    }

    private function getMessageRepository(): Repository\Message
    {
        return $this->getRepo(Repository\Message::class);
    }

    private function updateTaskDescriptionAndActionDate(CreateMessageCommand $command): Task
    {
        $task = $this->getConversationFromCommand($command)->getTask();
        $task->setDescription($this->getTaskDescription());
        $task->setActionDate($this->determineActionDate());
        $this->getTaskRepository()->save($task);
        return $task;
    }

    private function getTaskDescription(): string
    {
        if ($this->isInternalUser()) {
            return self::TASK_DESCRIPTION_ON_INTERNAL_REPLY;
        } elseif ($this->isExternalUser()) {
            return self::TASK_DESCRIPTION_ON_EXTERNAL_REPLY;
        }

        throw new RuntimeException("Unable to generate task description; not internal or external user.");
    }

    private function determineActionDate(): DateTime
    {
        if ($this->isInternalUser()) {
            return (new DateTime())->add(new DateInterval('P14D'));
        } elseif ($this->isExternalUser()) {
            return (new DateTime());
        }

        throw new RuntimeException("Unable to determine task action date; not internal or external user.");
    }

    private function getTaskRepository(): Repository\Task
    {
        return $this->getRepo(Repository\Task::class);
    }

    public function sendEmail(CreateMessageCommand $command): ?Result
    {
        if ($this->isExternalUser()) {
            return null;
        }

        $conversation = $this->getConversationFromCommand($command);

        return $this->handleSideEffect(
            SendNewMessageNotificationToOperators::create(
                [
                    'id' => $conversation->getTask()->getLicence()->getId(),
                ],
            ),
        );
    }

    private function assignUploadsToMessage(MessagingMessage $message, string $correlationId): void
    {
        $documentIds = $this->getCache()->getCustomItem(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, $correlationId) ?: [];
        $docRepo = $this->getRepo(Repository\Document::class);

        foreach ($documentIds as $id) {
            try {
                $doc = $docRepo->fetchById($id);
            } catch (NotFoundException) {
                // Document deleted.
                continue;
            }

            // Ensure only documents the user definitely uploaded can be modified
            if ($doc->getCreatedBy()->getId() !== $this->getUser()->getId()) {
                continue;
            }

            $doc->setMessagingConversation($message->getMessagingConversation());
            $doc->setMessagingMessage($message);
            $doc->setLicence($message->getMessagingConversation()->getRelatedLicence());
            $doc->setApplication($message->getMessagingConversation()->getTask()->getApplication());
            $docRepo->saveOnFlush($doc);
        }

        $docRepo->flushAll();
    }
}
