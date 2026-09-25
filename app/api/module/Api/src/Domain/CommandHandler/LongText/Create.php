<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\LongText;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\System\LongText;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\LongText\Create as Command;

final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LongText';

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        assert($command instanceof Command);

        $longText = LongText::create(
            (string) $command->getReferenceKey(),
            (string) $command->getLocale(),
            (string) $command->getPageName(),
            $command->getDescription(),
            LongTextContent::decode($command->getContent()),
        );

        $this->getRepo()->save($longText);

        return $this->result
            ->addId('longText', $longText->getId())
            ->addMessage('Long Text created');
    }
}
