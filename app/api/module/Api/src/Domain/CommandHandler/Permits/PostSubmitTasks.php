<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\StoreSnapshot as IrhpApplicationSnapshotCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\IrhpCandidatePermitsCreator;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Handles actions necessary once permit application is submitted.
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class PostSubmitTasks extends AbstractCommandHandler
{
    use QueueAwareTrait;

    protected $repoServiceName = 'IrhpApplication';

    /** @var IrhpCandidatePermitsCreator */
    private $irhpCandidatePermitsCreator;

    /**
     * Handles post-submission tasks for IRHP Applications
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $id = $command->getId();

        $sideEffects = [
            IrhpApplicationSnapshotCmd::create(['id' => $id]),
        ];

        /** @var IrhpApplication $irhpApplication */
        $irhpApplication = $this->getRepo()->fetchById($id);
        $this->irhpCandidatePermitsCreator->createIfRequired($irhpApplication);

        $appSubmittedEmailCommand = $irhpApplication->getAppSubmittedEmailCommand();
        if ($appSubmittedEmailCommand) {
            $sideEffects[] = $this->emailQueue($appSubmittedEmailCommand, ['id' => $id], $id);
        }

        $this->result->merge(
            $this->handleSideEffects($sideEffects)
        );

        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->irhpCandidatePermitsCreator = $container->get(
            'PermitsCandidatePermitsIrhpCandidatePermitsCreator'
        );
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
