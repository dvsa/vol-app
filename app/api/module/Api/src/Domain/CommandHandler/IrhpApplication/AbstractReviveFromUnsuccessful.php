<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Abstract Revive Application from unsuccessful state
 */
abstract class AbstractReviveFromUnsuccessful extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'changeMe';

    protected $extraRepos = ['IrhpCandidatePermit'];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $applicationRepo = $this->getRepo();
        $irhpCandidatePermitRepo = $this->getRepo('IrhpCandidatePermit');

        $applicationId = $command->getId();
        $application = $applicationRepo->fetchById($applicationId);

        if (!$application->canBeRevivedFromUnsuccessful()) {
            throw new ForbiddenException('Application cannot be revived from unsuccessful');
        }

        $irhpCandidatePermits = $application->getFirstIrhpPermitApplication()->getIrhpCandidatePermits();
        foreach ($irhpCandidatePermits as $irhpCandidatePermit) {
            $irhpCandidatePermit->reviveFromUnsuccessful();
            $irhpCandidatePermitRepo->save($irhpCandidatePermit);
        }

        $application->reviveFromUnsuccessful(
            $this->refData(IrhpInterface::STATUS_UNDER_CONSIDERATION)
        );

        $applicationRepo->save($application);

        $this->result->addMessage('Application revived from unsuccessful state');
        $this->result->addId($this->repoServiceName, $applicationId);

        return $this->result;
    }
}
