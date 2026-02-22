<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Cli\Domain\Command\InterimEndDateEnforcement as InterimEndDateEnforcementCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;
use Olcs\Logging\Log\Logger;

final class InterimEndDateEnforcement extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    private EventHistoryCreator $eventHistoryCreator;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->eventHistoryCreator = $container->get(EventHistoryCreator::class);

        return parent::__invoke($container, $requestedName, $options);
    }

    /**
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command): \Dvsa\Olcs\Api\Domain\Command\Result
    {
        Logger::debug('Handling InterimEndDateEnforcementCommand');
        assert($command instanceof InterimEndDateEnforcementCommand);

        Logger::info(($command->getDryRun()) ? 'Dryrun ENABLED; changes will NOT be made to the database' : 'Dryrun DISABLED; changes will be made to the database');
        $this->result->setFlag('dryrun', $command->getDryRun());

        $applicationRepo = $this->getRepo();
        assert($applicationRepo instanceof ApplicationRepo);

        Logger::info('Fetching applications matching criteria');
        $applicationsWithInterimInForceAndEndDateInPast = $applicationRepo->fetchOpenApplicationsWhereInterimInForceAndInterimEndDateIsPast();
        Logger::info('Found ' . count($applicationsWithInterimInForceAndEndDateInPast) . ' applications under-consideration with an in-force interim with an end date in the past');
        $this->result->setFlag('identified_count', count($applicationsWithInterimInForceAndEndDateInPast));

        Logger::debug('Fetching RefData for Interim Status Ended');
        $interimStatusEnded = $this->getRepo()->getRefdataReference(ApplicationEntity::INTERIM_STATUS_ENDED);

        foreach ($applicationsWithInterimInForceAndEndDateInPast as $application) {
            assert($application instanceof Application);
            if ($command->getDryRun()) {
                Logger::info('Interim for application with ID ' . $application->getId() . ' would have been set to ended');
                continue;
            }
            $application->setInterimStatus($interimStatusEnded);
            $applicationRepo->save($application);

            Logger::info('Ended interim for application with ID ' . $application->getId());

            $this->eventHistoryCreator->create(
                $application,
                EventHistoryType::INTERIM_END,
                'Automated end of interim'
            );
        }

        $this->result->addMessage('Complete');

        return $this->result;
    }
}
