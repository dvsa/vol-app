<?php

/**
 * Create Document
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Document\CreateDocument as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Document\CreateDocumentSpecific as CreateDocumentSpecificCmd;

/**
 * Create Document
 *
 * Determine whether document is internal/external then create document record
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class CreateDocument extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'Document';

    /**
     * @param Cmd $command
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $params = $command->getArrayCopy();

        if ($command->getIsExternal() === null) {
            $params['isExternal'] = $this->isGranted(Permission::SELFSERVE_USER);
        }

        return $this->handleSideEffect(CreateDocumentSpecificCmd::create($params));
    }
}
