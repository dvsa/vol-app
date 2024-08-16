<?php

/**
 * Delete Link
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;

/**
 * Delete Link
 */
final class DeletePublicationLink extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'PublicationLink';

    public function handleCommand(CommandInterface $command)
    {
        /** @var PublicationLinkEntity $publicationLink */
        $publicationLink = $this->getRepo()->fetchUsingId($command);

        if (!$publicationLink->getPublication()->isNew()) {
            throw new ForbiddenException('Only unpublished entries may be deleted');
        }

        $publicationLink->getPoliceDatas()->clear();
        $this->getRepo()->delete($publicationLink);

        $result = new Result();
        $result->addId('PublicationLink', $publicationLink->getId());
        $result->addMessage('Publication entry deleted successfully');

        return $result;
    }
}
