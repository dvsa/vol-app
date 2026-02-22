<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\MasterTemplate\Update as Cmd;

/**
 * Update MasterTemplate
 */
final class Update extends AbstractCommandHandler
{
    protected $repoServiceName = 'MasterTemplate';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\MasterTemplate $masterTemplate */
        $masterTemplate = $this->getRepo()->fetchUsingId($command);

        $masterTemplate->setName($command->getName());

        if ($command->getTemplateContent() !== null) {
            $masterTemplate->setTemplateContent($command->getTemplateContent());
        }

        if ($command->getIsDefault() !== null) {
            $masterTemplate->setIsDefault($command->getIsDefault());
        }

        if ($command->getLocale() !== null) {
            $masterTemplate->setLocale($command->getLocale());
        }

        $this->getRepo()->save($masterTemplate);

        $this->result->addId('masterTemplate', $masterTemplate->getId());
        $this->result->addMessage("Master template '{$masterTemplate->getName()}' updated");

        return $this->result;
    }
}
