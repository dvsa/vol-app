<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsFacade;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulDaPermitApplications as MarkSuccessfulDaPermitApplicationsCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Set successful permit applications for each devolved administration
 * See https://wiki.i-env.net/display/olcs/Batch+Process%3A+Identify+Successful+Permit+Applications+with+Restricted+Countries
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulDaPermitApplications extends ScoringCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpPermitJurisdictionQuota';

    protected $extraRepos = ['IrhpApplication'];

    /** @var SuccessfulCandidatePermitsFacade */
    private $successfulCandidatePermitsFacade;

    /**
     * Handle command
     *
     * @param CommandInterface|MarkSuccessfulDaPermitApplicationsCommand $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $irhpApplicationRepo = $this->getRepo('IrhpApplication');

        $this->profileMessage('mark successful da permit applications...');

        $stockId = $command->getStockId();
        $daQuotas = $this->getRepo()->fetchByNonZeroQuota($stockId);

        $this->result->addMessage('STEP 2c:');
        $this->result->addMessage('  DAs associated with stock where quota > 0: ' . count($daQuotas));

        $totalSuccessfulCandidatePermits = 0;

        foreach ($daQuotas as $daQuota) {
            $daSuccessCount = $irhpApplicationRepo->getSuccessfulDaCountInScope(
                $stockId,
                $daQuota['jurisdictionId']
            );

            $daRemainingQuota = $daQuota['quotaNumber'] - $daSuccessCount;

            $this->result->addMessage('    DA with id ' . $daQuota['jurisdictionId'] . ':');
            $this->result->addMessage('      Derived values:');
            $this->result->addMessage('      - #DAQuota:          ' . $daQuota['quotaNumber']);
            $this->result->addMessage('      - #DASuccessCount:   ' . $daSuccessCount);
            $this->result->addMessage('      - #DARemainingQuota: ' . $daRemainingQuota);

            if ($daRemainingQuota > 0) {
                $daCandidatePermits = $irhpApplicationRepo->getUnsuccessfulScoreOrderedInScope(
                    $stockId,
                    $daQuota['jurisdictionId']
                );

                $successfulCandidatePermits = $this->successfulCandidatePermitsFacade->generate(
                    $daCandidatePermits,
                    $command->getStockId(),
                    $daRemainingQuota
                );

                $this->result->addMessage('      Permits requesting this DA: ' . count($daCandidatePermits));
                $this->result->addMessage('      - adjusted for quota: ' . count($successfulCandidatePermits));
                $this->successfulCandidatePermitsFacade->log($successfulCandidatePermits, $this->result);

                $this->successfulCandidatePermitsFacade->write($successfulCandidatePermits);
                $totalSuccessfulCandidatePermits += count($successfulCandidatePermits);
            } else {
                $this->result->addMessage('      #DARemainingQuota < 1 - nothing to do');
            }
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
