<?php

/**
 * Update Previous Convictions
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCommand;

/**
 * Update Previous Convictions
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class UpdatePreviousConvictions extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $application->setPrevConviction($command->getPrevConviction());
        $application->setConvictionsConfirmation($command->getConvictionsConfirmation());

        $this->getRepo()->save($application);

        $update = $this->handleSideEffect(
            UpdateApplicationCompletionCommand::create(
                [
                    'id' => $application->getId(),
                    'section' => 'convictionsPenalties'
                ]
            )
        );

        $result->merge($update);

        $result->addMessage('Application saved successfully');
        return $result;
    }
}
