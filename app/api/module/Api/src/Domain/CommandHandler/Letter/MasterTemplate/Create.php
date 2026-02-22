<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate as MasterTemplateEntity;
use Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate\Create as Cmd;

/**
 * Create MasterTemplate
 */
final class Create extends AbstractCommandHandler
{
    protected $repoServiceName = 'MasterTemplate';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        $masterTemplate = new MasterTemplateEntity();
        $masterTemplate->setName($command->getName());
        $masterTemplate->setTemplateContent($command->getTemplateContent());
        $masterTemplate->setIsDefault($command->getIsDefault());
        $masterTemplate->setLocale($command->getLocale());

        $this->getRepo()->save($masterTemplate);

        $this->result->addId('masterTemplate', $masterTemplate->getId());
        $this->result->addMessage("Master template '{$masterTemplate->getName()}' created");

        return $this->result;
    }
}
