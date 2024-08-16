<?php

/**
 * Update PublicationLink
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Transfer\Command\Publication\UpdatePublicationLink as UpdatePublicationLinkCmd;

/**
 * Update PublicationLink
 */
final class UpdatePublicationLink extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PublicationLink';

    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var UpdatePublicationLinkCmd $command
         * @var PublicationLinkEntity $publicationLink
         */
        $publicationLink = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());
        $publicationLink->updateText($command->getText1(), $command->getText2(), $command->getText3());
        $this->getRepo()->save($publicationLink);

        $result = new Result();
        $result->addId('PublicationLink', $publicationLink->getId());
        $result->addMessage('Publication entry updated successfully');

        return $result;
    }
}
