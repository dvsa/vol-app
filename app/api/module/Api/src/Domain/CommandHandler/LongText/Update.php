<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LongText;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\LongText;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LongText\Update as Command;

final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LongText';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof Command);

        /** @var LongText $longText */
        $longText = $this->getRepo()->fetchById($command->getId());

        $longText->setPageName((string) $command->getPageName())
            ->setDescription($command->getDescription())
            ->setContent(LongTextContent::decode($command->getContent()));

        $this->getRepo()->save($longText);

        return $this->result
            ->addId('longText', $longText->getId())
            ->addMessage('Long Text updated');
    }
}
