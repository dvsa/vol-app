<?php

/**
 * Revive an application
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Licence\Grant as GrantCmd;
use Dvsa\Olcs\Api\Domain\Command\Licence\UnderConsideration;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Class ReviveApplication
 *
 * Revive a previously withdrawn, NTU or refused application.
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Application
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class ReviveApplication extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $licence */
        $application = $this->getRepo()->fetchById($command->getId());

        /** @var RefData $currentStatus */
        $currentStatus = $application->getStatus();

        $result = new Result();

        switch ($currentStatus->getId()) {
            case Application::APPLICATION_STATUS_NOT_TAKEN_UP:
                $application->setStatus(
                    $this->getRepo()
                        ->getRefdataReference(
                            Application::APPLICATION_STATUS_GRANTED
                        )
                );
                $result->merge(
                    $this->handleSideEffect(
                        GrantCmd::create(
                            [
                                'id' => $application->getLicence()->getId()
                            ]
                        )
                    )
                );
                break;
            case Application::APPLICATION_STATUS_WITHDRAWN:
            case Application::APPLICATION_STATUS_REFUSED:
                $application->setStatus(
                    $this->getRepo()
                        ->getRefdataReference(
                            Application::APPLICATION_STATUS_UNDER_CONSIDERATION
                        )
                );

                if (!$application->getIsVariation()) {
                    $result->merge(
                        $this->handleSideEffect(
                            UnderConsideration::create(
                                [
                                    'id' => $application->getLicence()->getId()
                                ]
                            )
                        )
                    );
                }
                break;
        }

        $this->getRepo()->save($application);

        $result->addMessage('Application ' . $application->getId() . ' has been revived');

        return $result;
    }
}
