<?php

/**
 * Create a new publication based on the previous one
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreateNextPublication as CreateNextPublicationCmd;

/**
 * Create a new publication based on the previous one
 */
final class CreateNextPublication extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Publication';

    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var PublicationEntity $publication
         * @var CreateNextPublicationCmd
         */
        $publication = $this->getRepo()->fetchUsingId($command);

        $newPublication = new PublicationEntity(
            $publication->getTrafficArea(),
            $this->getRepo()->getRefdataReference(PublicationEntity::PUB_NEW_STATUS),
            $publication->getDocTemplate(),
            $publication->getNextPublicationDate(),
            $publication->getPubType(),
            $publication->getPublicationNo() + 1
        );

        $this->getRepo()->save($newPublication);

        $result = new Result();
        $result->addId('created_publication', $newPublication->getId());
        $result->addMessage('Publication created successfully');

        return $result;
    }
}
