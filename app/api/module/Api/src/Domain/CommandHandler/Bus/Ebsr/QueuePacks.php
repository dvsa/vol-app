<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\QueuePacks as QueuePacksCmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Queue EBSR packs for processing
 */
final class QueuePacks extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;
    use QueueAwareTrait;

    protected $repoServiceName = 'EbsrSubmission';

    /**
     * Queues EBSR packs for processing
     *
     * @param CommandInterface $command
     * @return Result
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();
        $queuedPacks = 0;
        $queueItems = [];

        /**
         * @var EbsrSubmissionRepo $repo
         * @var QueuePacksCmd $command
         */
        $repo = $this->getRepo();

        $organisation = $this->getCurrentOrganisation();

        if (!$organisation instanceof Organisation) {
            throw new ValidationException(['No organisation was found']);
        }

        $ebsrSubmissions = $repo->fetchForOrganisationByStatus(
            $organisation->getId(),
            EbsrSubmissionEntity::UPLOADED_STATUS
        );

        /** @var EbsrSubmissionEntity $ebsrSub */
        foreach ($ebsrSubmissions as $ebsrSub) {
            $queueItems[] = $this->createQueueCmd(
                $ebsrSub->getId(),
                $organisation->getId()
            );

            $ebsrSub->submit(
                $repo->getRefdataReference(EbsrSubmissionEntity::SUBMITTED_STATUS),
                $repo->getRefdataReference($command->getSubmissionType())
            );

            $repo->save($ebsrSub);
            $queuedPacks++;
        }

        if (empty($queueItems)) {
            throw new ValidationException(['There were no packs to queue']);
        }

        $result->merge($this->handleSideEffects($queueItems));
        $result->addMessage($queuedPacks . ' packs were queued for upload');

        return $result;
    }

    /**
     * Adds the EBSR submission to the queue
     *
     * @param int $ebsrId
     * @param int $organisationId
     *
     * @return CreateQueueCmd
     */
    private function createQueueCmd($ebsrId, $organisationId)
    {
        $options = [
            'id' => $ebsrId,
            'organisation' => $organisationId
        ];

        return $this->createQueue($ebsrId, Queue::TYPE_EBSR_PACK, $options);
    }
}
