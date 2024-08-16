<?php

/**
 * Update Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Workshop\UpdateWorkshop as WorkshopUpdateWorkshop;

/**
 * Update Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class UpdateWorkshop extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = $this->handleSideEffect(WorkshopUpdateWorkshop::create($command->getArrayCopy()));

        $this->result->merge($result);

        $completionData = [
            'id' => $command->getApplication(),
            'section' => 'safety',
            'data' => [
                'hasChanged' => $result->getFlag('hasChanged')
            ]
        ];
        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($completionData)));

        return $this->result;
    }
}
