<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Messaging\Conversation;

use Dvsa\Olcs\Api\Domain\Command\Email\CreateCorrespondenceRecord;
use Dvsa\Olcs\Api\Domain\Command\Messaging\Conversation\StoreSnapshot;
use Dvsa\Olcs\Api\Domain\Command\Messaging\Conversation\StoreEnhancedSnapshot;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractUserCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Messaging\MessagingConversation;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks;

final class Close extends AbstractUserCommandHandler implements ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [
        FeatureToggle::MESSAGING,
    ];
    protected $extraRepos = [
        Repository\Conversation::class,
    ];

    /**
     * Close Command Handler Abstract
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var MessagingConversation $conversation */
        $conversation = $this->getRepo(Repository\Conversation::class)->fetchUsingId($command);
        $conversation->setIsClosed(true);
        $this->getRepo(Repository\Conversation::class)->save($conversation);

        $result = new Result();
        $result->addId('conversation', $conversation->getId());
        $result->addMessage('Conversation closed');

        $documentResult = $this->handleSideEffect(StoreSnapshot::create(['id' => $conversation->getId()]));
        $result->merge($documentResult);
        $enhancedDocumentResult = $this->handleSideEffect(StoreEnhancedSnapshot::create(['id' => $conversation->getId()]));
        $result->merge($enhancedDocumentResult);

        $taskResult = $this->handleSideEffect(CloseTasks::create(['ids' => [$conversation->getTask()->getId()]]));
        $result->merge($taskResult);

        $result->merge(
            $this->handleSideEffect(
                CreateCorrespondenceRecord::create(
                    [
                        'licence'  => $conversation->getRelatedLicence(),
                        'document' => $documentResult->getId('document'),
                        'type'     => CreateCorrespondenceRecord::TYPE_CLOSED_CONVERSATION,
                    ],
                ),
            ),
        );

        return $result;
    }
}
