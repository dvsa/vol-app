<?php

/**
 * Publish a publication
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Publication\Publish as PublishCommand;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPublication as SendPublicationEmailCmd;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreatePoliceDocument as CreatePoliceDocumentCmd;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;

/**
 * Publish a publication
 */
final class Publish extends AbstractCommandHandler implements TransactionedInterface
{
    use QueueAwareTrait;

    protected $repoServiceName = 'Publication';

    protected $extraRepos = ['Document'];

    /**
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var PublicationEntity $publication
         * @var PublishCommand $command
         * @var DocumentEntity $policeDocument
         * @var DocumentRepo $documentRepo
         * @var Result $result
         */
        $publication = $this->getRepo()->fetchUsingId($command);
        $refData = $this->getRepo()->getRefdataReference(PublicationEntity::PUB_PRINTED_STATUS);
        $publication->publish($refData);

        $pubId = $publication->getId();

        $result = $this->handleSideEffect(CreatePoliceDocumentCmd::create(['id' => $pubId]));

        $documentRepo = $this->getRepo('Document');
        $policeDocument = $documentRepo->fetchById($result->getId('document'));
        $publication->updatePublishedDocuments($policeDocument);

        //we need to do this before triggering the extra side effects
        $this->getRepo()->save($publication);

        $policeCmdData = [
            'id' => $pubId,
            'isPolice' => 'Y'
        ];

        $nonPoliceCmdData = [
            'id' => $pubId,
            'isPolice' => 'N'
        ];

        $queuePubDate = $publication->getPubDate() . ' 00:00:00';

        $policeEmail = $this->emailQueue(SendPublicationEmailCmd::class, $policeCmdData, $pubId, $queuePubDate);
        $nonPoliceEmail = $this->emailQueue(SendPublicationEmailCmd::class, $nonPoliceCmdData, $pubId, $queuePubDate);

        $result->merge($this->handleSideEffects([$policeEmail, $nonPoliceEmail]));
        $result->addId('Publication', $pubId);
        $result->addMessage('Publication was published');

        return $result;
    }
}
