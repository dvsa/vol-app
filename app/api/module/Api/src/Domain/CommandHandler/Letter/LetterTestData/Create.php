<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTestData;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\LetterTestData as LetterTestDataEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTestData\Create as Cmd;

/**
 * Create LetterTestData
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterTestData';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        $letterTestData = new LetterTestDataEntity();
        $letterTestData->setName($command->getName());
        $letterTestData->setJson($command->getJson());

        $this->getRepo()->save($letterTestData);

        $this->result->addId('letterTestData', $letterTestData->getId());
        $this->result->addMessage("Letter test data '{$letterTestData->getName()}' created");

        return $this->result;
    }
}
