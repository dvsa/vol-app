<?php

/**
 * Delete Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Workshop\DeleteWorkshop as WorkshopDeleteWorkshop;

/**
 * Delete Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DeleteWorkshop extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $this->result->merge(
            $this->handleSideEffect(
                WorkshopDeleteWorkshop::create(
                    [
                        'ids' => $command->getIds(),
                        'application' => $command->getApplication()
                    ]
                )
            )
        );

        $data = [
            'id' => $command->getApplication(),
            'section' => 'safety',
            'data' => [
                'hasChanged' => true
            ]
        ];

        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($data)));

        return $this->result;
    }
}
