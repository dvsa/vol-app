<?php

/**
 * Create Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Workshop\CreateWorkshop as WorkshopCreateWorkshop;
use Dvsa\Olcs\Api\Entity\Application\Application;

/**
 * Create Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateWorkshop extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchById($command->getApplication());

        $data = $command->getArrayCopy();
        $data['licence'] = $application->getLicence()->getId();

        $this->result->merge($this->handleSideEffect(WorkshopCreateWorkshop::create($data)));

        $completionData = [
            'id' => $command->getApplication(),
            'section' => 'safety',
            'data' => [
                'hasChanged' => true
            ]
        ];
        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($completionData)));

        return $this->result;
    }
}
