<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterTestData;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterTestData\Update as Cmd;

/**
 * Update LetterTestData
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterTestData';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */
        
        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterTestData $letterTestData */
        $letterTestData = $this->getRepo()->fetchUsingId($command);
        
        $letterTestData->setName($command->getName());
        $letterTestData->setJson($command->getJson());

        $this->getRepo()->save($letterTestData);

        $this->result->addId('letterTestData', $letterTestData->getId());
        $this->result->addMessage("Letter test data '{$letterTestData->getName()}' updated");
        
        return $this->result;
    }
}
