<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Permits;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Permits\AllocateCandidatePermits as AllocateCandidatePermitsCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use DateTime;

/**
 * Allocate candidate permits for an IRHP Permit application
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AllocateCandidatePermits extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermitApplication';

    protected $extraRepos = ['IrhpPermit'];

    /**
     * Handle command
     *
     * @param AllocateCandidatePermitsCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var IrhpPermitApplication $irhpPermitApplication
         * @var IrhpPermitType        $irhpPermitType
         */
        $irhpPermitApplicationId = $command->getId();
        $irhpPermitApplication = $this->getRepo()->fetchById($irhpPermitApplicationId);
        $irhpPermitType = $irhpPermitApplication->getIrhpApplication()->getIrhpPermitType();

        $expiryDate = null;
        $issueDate = null;

        //for short term permits only, we add an expiry date on the permit
        if ($irhpPermitType->isEcmtShortTerm()) {
            $expiryDate = $irhpPermitApplication->generateExpiryDate();
            $issueDate = $irhpPermitApplication->generateIssueDate();
        }

        $candidatePermits = $irhpPermitApplication->getSuccessfulIrhpCandidatePermits(null, true);
        foreach ($candidatePermits as $candidatePermit) {
            $this->addIrhpPermit($candidatePermit, $expiryDate, $issueDate);
        }

        $this->result->addId('irhpPermitApplication', $irhpPermitApplicationId);
        $this->result->addMessage('IRHP permit records created');

        return $this->result;
    }

    /**
     * Derive an IrhpPermit entity from the IrhpCandidatePermit entity and save it to the repository
     *
     *
     * @return void
     */
    private function addIrhpPermit(IrhpCandidatePermit $candidatePermit, ?DateTime $expiryDate, ?DateTime $issueDate): void
    {
        $range = $candidatePermit->getIrhpPermitRange();
        $irhpPermit = IrhpPermit::createNew(
            $candidatePermit,
            $issueDate ?: new DateTime(),
            $this->refData(IrhpPermit::STATUS_PENDING),
            $this->getNextPermitNumber($range),
            $expiryDate
        );

        $this->getRepo('IrhpPermit')->save($irhpPermit);
        $range->addIrhpPermits($irhpPermit);
    }

    /**
     * Get the first available permit number from the specified range
     *
     *
     * @return int
     */
    private function getNextPermitNumber(IrhpPermitRange $range)
    {
        $permitMap = array_fill_keys(
            range($range->getFromNo(), $range->getToNo()),
            true
        );

        foreach ($range->getIrhpPermits() as $permit) {
            $permitMap[$permit->getPermitNumber()] = false;
        }

        $assignedPermitMap = array_filter($permitMap);
        return array_key_first($assignedPermitMap);
    }
}
