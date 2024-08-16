<?php

/**
 * Delete Documents
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\DeleteDocument as DeleteDocumentCmd;

/**
 * Delete Documents
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeleteDocuments extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Document';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $id) {
            $this->handleSideEffect(DeleteDocumentCmd::create(['id' => $id]));
        }

        $result->addMessage(count($command->getIds()) . ' document(s) deleted');

        return $result;
    }
}
