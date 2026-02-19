<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceAppendix;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceAppendix\UpdateContent as Cmd;

/**
 * Update LetterInstanceAppendix edited content
 */
final class UpdateContent extends AbstractCommandHandler
{
    protected $repoServiceName = 'LetterInstanceAppendix';

    public function handleCommand(CommandInterface $command): Result
    {
        /** @var Cmd $command */

        /** @var \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceAppendix $letterInstanceAppendix */
        $letterInstanceAppendix = $this->getRepo()->fetchUsingId($command);

        if (!$letterInstanceAppendix->isEditable()) {
            throw new ValidationException(['This appendix is not editable']);
        }

        $decoded = json_decode($command->getEditedContent(), true);

        if (!is_array($decoded)) {
            throw new ValidationException(['editedContent must be valid JSON']);
        }

        $letterInstanceAppendix->setEditedContentFromArray($decoded);

        $this->getRepo()->save($letterInstanceAppendix);

        $this->result->addId('letterInstanceAppendix', $letterInstanceAppendix->getId());
        $this->result->addMessage('Appendix content updated successfully');

        return $this->result;
    }
}
