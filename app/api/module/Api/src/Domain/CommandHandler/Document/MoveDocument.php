<?php

/**
 * Move document
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Document\CopyDocument as CopyDocumentCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Move document
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class MoveDocument extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Document';

    public function handleCommand(CommandInterface $command)
    {
        $params = [
            'ids' => $command->getIds(),
            'targetId' => $command->getTargetId(),
            'type' => $command->getType()
        ];
        $res = $this->handleSideEffect(CopyDocumentCmd::create($params));

        foreach ($command->getIds() as $id) {
            $this->getRepo()->delete($this->getRepo()->fetchById($id));
        }
        $result = new Result();
        $result->addMessage('Document(s) moved');

        foreach ($res->getIds() as $id) {
            $result->addId('document' . $id, $id);
        }
        return $result;
    }
}
