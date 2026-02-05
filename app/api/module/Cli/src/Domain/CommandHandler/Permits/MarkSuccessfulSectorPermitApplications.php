<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsFacade;
use Dvsa\Olcs\Cli\Domain\Command\MarkSuccessfulSectorPermitApplications
    as MarkSuccessfulSectorPermitApplicationsCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Set successful permit applications for each sector
 * See https://wiki.i-env.net/display/olcs/Batch+Process%3A+Identify+Successful+Permit+Applications+with+Restricted+Countries
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulSectorPermitApplications extends ScoringCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpPermitSectorQuota';

    protected $extraRepos = ['IrhpApplication'];

    /** @var SuccessfulCandidatePermitsFacade */
    private $successfulCandidatePermitsFacade;

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->profileMessage('mark successful sector permit applications...');

        $sectorQuotas = $this->getRepo()->fetchByNonZeroQuota(
            $command->getStockId()
        );

        $this->result->addMessage('STEP 2b:');
        $this->result->addMessage('  Sectors associated with stock where quota > 0: ' . count($sectorQuotas));

        $totalSuccessfulCandidatePermits = 0;

        foreach ($sectorQuotas as $sectorQuota) {
            $sectorCandidatePermits = $this->getRepo('IrhpApplication')->getScoreOrderedBySectorInScope(
                $command->getStockId(),
                $sectorQuota['sectorId']
            );

            $successfulCandidatePermits = $this->successfulCandidatePermitsFacade->generate(
                $sectorCandidatePermits,
                $command->getStockId(),
                $sectorQuota['quotaNumber']
            );

            $this->result->addMessage('    Sector with id ' . $sectorQuota['sectorId'] . ':');
            $this->result->addMessage('      Derived values:');
            $this->result->addMessage('      - #sectorQuota: ' . $sectorQuota['quotaNumber']);
            $this->result->addMessage('      Permits requesting this sector: ' . count($sectorCandidatePermits));
            $this->result->addMessage('      - adjusted for quota: ' . count($successfulCandidatePermits));
            $this->successfulCandidatePermitsFacade->log($successfulCandidatePermits, $this->result);

            $this->successfulCandidatePermitsFacade->write($successfulCandidatePermits);
            $totalSuccessfulCandidatePermits += count($successfulCandidatePermits);
        }

        $this->result->addMessage('  ' . $totalSuccessfulCandidatePermits . ' permits have been marked as successful');
        return $this->result;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->successfulCandidatePermitsFacade = $container->get(
            'PermitsScoringSuccessfulCandidatePermitsFacade'
        );
        return parent::__invoke($container, $requestedName, $options);
    }
}
