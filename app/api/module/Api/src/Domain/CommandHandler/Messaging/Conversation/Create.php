<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Operator\UnlicensedAbstract as AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\ToggleAwareInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingSubject;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Messaging\Conversation\Create as CreateConversationCommand;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;

final class Create extends AbstractCommandHandler implements ToggleAwareInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [FeatureToggle::MESSAGING];
    protected $extraRepos = [
        Repository\Conversation::class,
        Repository\Task::class,
        Repository\MessagingSubject::class,
        Repository\Application::class,
    ];

    /**
     * @param $command CreateConversationCommand
     * @throws NotFoundException|Exception
     */
    public function handleCommand(CommandInterface $command): Result
    {
        if (empty($command->getApplication()) && empty($command->getLicence())) {
            throw new Exception('Command expects either a application or licence defined');
        }

        $licenceId = $command->getLicence();
        if (empty($licenceId)) {
            $licenceId = $this->getLicenceByApplication((int)$command->getApplication())->getId();
        }

        $messageSubject = $this->getMessageSubject($command);

        $createTaskCommandParameters = [
            'category' => $messageSubject->getCategory()->getId(),
            'licence' => $licenceId,
            'messaging' => true,
        ];
        if (!empty($command->getApplication())) {
            $createTaskCommandParameters['application'] = $command->getApplication();
        }
        if (!empty($messageSubject->getSubCategory())) {
            $createTaskCommandParameters['subCategory'] = $messageSubject->getSubCategory()->getId();
        }
        $createTaskResult = $this->handleSideEffect(CreateTask::create($createTaskCommandParameters));

        $conversation = $this->generateAndSaveConversation($createTaskResult, $messageSubject);

        $createMessageResult = $this->handleSideEffect(CreateMessageCommand::create([
            'conversation' => $conversation->getId(),
            'messageContent' => $command->getMessageContent(),
            'correlationId' => $command->getCorrelationId(),
        ]));

        $result = new Result();

        $result->addId('conversation', $conversation->getId())->addMessage('Conversation added');
        $result->addId('licence', $licenceId);

        $result->merge($createTaskResult);
        $result->merge($createMessageResult);

        return $result;
    }

    private function getLicenceByApplication(int $application): Licence
    {
        return $this->getApplicationRepo()->fetchById($application)->getLicence();
    }

    private function getApplicationRepo(): Repository\Application
    {
        return $this->getRepo(Repository\Application::class);
    }

    /**
     * @throws NotFoundException
     */
    private function getMessageSubject(CreateConversationCommand $command): MessagingSubject
    {
        return $this->getMessagingSubjectRepo()->fetchById($command->getMessageSubject());
    }

    private function getMessagingSubjectRepo(): Repository\MessagingSubject
    {
        return $this->getRepo(Repository\MessagingSubject::class);
    }

    private function generateAndSaveConversation(Result $createTaskResult, MessagingSubject $messageSubject): MessagingConversation
    {
        $task = $this->getTask($createTaskResult->getId('task'));
        $subject = $this->generateConversationSubjectFromMessageSubject($messageSubject);
        $conversation = $this->createConversationEntity($task, $subject);

        $this->getConversationRepo()->save($conversation);

        return $conversation;
    }

    private function getTask(int $taskId): Task
    {
        $taskRepo = $this->getTaskRepo();
        return $taskRepo->fetchById($taskId);
    }

    private function getTaskRepo(): Repository\Task
    {
        return $this->getRepo(Repository\Task::class);
    }

    private function generateConversationSubjectFromMessageSubject(MessagingSubject $messageSubject): string
    {
        return sprintf('%s query', $messageSubject->getDescription());
    }

    private function createConversationEntity(Task $task, string $subject): MessagingConversation
    {
        $entity = new MessagingConversation();
        $entity->setTask($task)->setSubject($subject);
        return $entity;
    }

    private function getConversationRepo(): Repository\Conversation
    {
        return $this->getRepo(Repository\Conversation::class);
    }
}
