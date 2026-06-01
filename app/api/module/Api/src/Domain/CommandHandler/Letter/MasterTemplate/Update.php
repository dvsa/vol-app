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

    #[\Override]
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

        // VOL-7305: optional chrome slot fields (EditorJS JSON). Null = leave alone.
        if ($command->getHeaderLeftContent() !== null) {
            $masterTemplate->setHeaderLeftContent($command->getHeaderLeftContent());
        }
        if ($command->getHeaderRightContent() !== null) {
            $masterTemplate->setHeaderRightContent($command->getHeaderRightContent());
        }
        if ($command->getSignoffContent() !== null) {
            $masterTemplate->setSignoffContent($command->getSignoffContent());
        }
        if ($command->getFooterContent() !== null) {
            $masterTemplate->setFooterContent($command->getFooterContent());
        }

        $this->getRepo()->save($masterTemplate);

        $this->result->addId('masterTemplate', $masterTemplate->getId());
        $this->result->addMessage("Master template '{$masterTemplate->getName()}' updated");

        return $this->result;
    }
}
