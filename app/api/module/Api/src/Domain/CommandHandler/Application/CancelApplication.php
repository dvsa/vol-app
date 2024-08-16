<?php

/**
 * Cancel application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Cancel application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CancelApplication extends AbstractCommandHandler implements TransactionedInterface
{
    public $repoServiceName = 'Application';

    public $extraRepos = ['Licence'];

    public function handleCommand(CommandInterface $command)
    {
        /* @var $application Application */
        $application = $this->getRepo()->fetchById($command->getId());

        $application->setStatus($this->getRepo()->getRefdataReference(Application::APPLICATION_STATUS_CANCELLED));
        $this->getRepo()->save($application);

        if (!$application->getIsVariation()) {
            $licence = $application->getLicence();
            $licence->setStatus($this->getRepo()->getRefdataReference(Licence::LICENCE_STATUS_CANCELLED));
            $this->getRepo('Licence')->save($licence);
        }

        $this->cancelOutstandingFees($application);

        $this->result->addMessage('Application cancelled');
        $this->result->addId('application', $application->getId());

        return $this->result;
    }

    /**
     * Cancel outstanding fees on the application
     */
    private function cancelOutstandingFees(Application $application)
    {
        $this->result->merge(
            $this->handleSideEffect(
                \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::create(
                    ['id' => $application->getId()]
                )
            )
        );
    }
}
