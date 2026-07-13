<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\MasterTemplate;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Service\EditorJs\EditorJsData;
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

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        $masterTemplate = new MasterTemplateEntity();
        $masterTemplate->setName($command->getName());
        $masterTemplate->setTemplateContent($command->getTemplateContent());
        $masterTemplate->setIsDefault($command->getIsDefault());
        $masterTemplate->setLocale($command->getLocale());

        // VOL-7305: optional chrome slot fields (EditorJS JSON)
        $masterTemplate->setHeaderLeftContent($this->prepareSlotContent('headerLeftContent', $command->getHeaderLeftContent()));
        $masterTemplate->setHeaderRightContent($this->prepareSlotContent('headerRightContent', $command->getHeaderRightContent()));
        $masterTemplate->setSignoffContent($this->prepareSlotContent('signoffContent', $command->getSignoffContent()));
        $masterTemplate->setFooterContent($this->prepareSlotContent('footerContent', $command->getFooterContent()));

        $this->getRepo()->save($masterTemplate);

        $this->result->addId('masterTemplate', $masterTemplate->getId());
        $this->result->addMessage("Master template '{$masterTemplate->getName()}' created");

        return $this->result;
    }

    /**
     * Reject data that isn't EditorJS-shaped at all, and fill in the envelope
     * fields (time / block ids) the parser mandates but hand-authored content omits.
     *
     * @throws ValidationException
     */
    private function prepareSlotContent(string $field, ?array $content): ?array
    {
        if ($content === null) {
            return null;
        }

        if (!EditorJsData::isValidShape($content)) {
            throw new ValidationException([$field => ['Not valid EditorJS content: expected a "blocks" list of {type, data} objects']]);
        }

        return EditorJsData::normalize($content);
    }
}
