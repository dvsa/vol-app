<?php

namespace Dvsa\Olcs\Cli\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Service\Permits\Scoring\SuccessfulCandidatePermitsFacade;
use Dvsa\Olcs\Cli\Domain\Command\Permits\MarkSuccessfulRemainingPermitApplications as MarkSuccessfulRemainingPermitApplicationsCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

/**
 * Set the remaining successful permit applications
 * See https://wiki.i-env.net/display/olcs/Batch+Process%3A+Identify+Successful+Permit+Applications+with+Restricted+Countries
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MarkSuccessfulRemainingPermitApplications extends ScoringCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpPermit';

    protected $extraRepos = ['IrhpPermitRange', 'IrhpApplication'];

    /** @var SuccessfulCandidatePermitsFacade */
    private $successfulCandidatePermitsFacade;

    /**
     * Handle command
     *
     * @param CommandInterface|MarkSuccessfulRemainingPermitApplicationsCommand $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->profileMessage('mark successful remaining permit applications...');

        $stockId = $command->getStockId();

        $availableStockCount = $this->getRepo('IrhpPermitRange')->getCombinedRangeSize($stockId);
        $validPermitCount = $this->getRepo()->getPermitCount($stockId);
        $allocationQuota = $availableStockCount - $validPermitCount;

        $irhpApplicationRepo = $this->getRepo('IrhpApplication');

        $successfulPaCount = $irhpApplicationRepo->getSuccessfulCountInScope($stockId);
        $remainingQuota = $allocationQuota - $successfulPaCount;

        $this->result->addMessage('STEP 2d:');
        $this->result->addMessage('  Derived values:');
        $this->result->addMessage('    - #availableStockCount: ' . $availableStockCount);
        $this->result->addMessage('    - #validPermitCount:    ' . $validPermitCount);
        $this->result->addMessage('    - #allocationQuota:     ' . $allocationQuota);
        $this->result->addMessage('    - #successfulPACount:   ' . $successfulPaCount);
        $this->result->addMessage('    - #remainingQuota:      ' . $remainingQuota);

        if ($remainingQuota > 0) {
            $remainingCandidatePermits = $irhpApplicationRepo->getUnsuccessfulScoreOrderedInScope($stockId);

            $successfulCandidatePermits = $this->successfulCandidatePermitsFacade->generate(
                $remainingCandidatePermits,
                $command->getStockId(),
                $remainingQuota
            );

            $this->successfulCandidatePermitsFacade->write($successfulCandidatePermits);

            $this->result->addMessage('  Unsuccessful remaining permits found in stock: ' . count($remainingCandidatePermits));
            $this->successfulCandidatePermitsFacade->log($successfulCandidatePermits, $this->result);
        } else {
            $this->result->addMessage('#remainingQuota < 1 - nothing to do');
        }

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
