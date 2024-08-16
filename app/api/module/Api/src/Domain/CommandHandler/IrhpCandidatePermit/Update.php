<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpCandidatePermit;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Transfer\Command\IrhpCandidatePermit\Update as UpdateCandidateCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update candidate permit
 */
class Update extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'IrhpCandidatePermit';

    protected $extraRepos = ['IrhpPermitRange'];

    /**
     * Handle command
     *
     * @param UpdateCandidateCmd|CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $irhpCandidatePermitRepo \Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit */
        $irhpCandidatePermitRepo = $this->getRepo();

        /* @var $irhpCandidatePermit IrhpCandidatePermit */
        $irhpCandidatePermit = $irhpCandidatePermitRepo->fetchById($command->getId());

        /* @var $irhpPermitRange IrhpPermitRange */
        $irhpPermitRange = $this->getRepo('IrhpPermitRange')->fetchById($command->getIrhpPermitRange());

        if (
            $irhpPermitRange->getIrhpPermitStock()->getId()
            !== $irhpCandidatePermit->getIrhpPermitRange()->getIrhpPermitStock()->getId()
        ) {
            throw new ValidationException(['New range does not belong to same stock!']);
        }

        $irhpCandidatePermit->updateIrhpPermitRange($irhpPermitRange);

        $irhpCandidatePermitRepo->save($irhpCandidatePermit);

        $this->result->addId('irhpCandidatePermit', $irhpCandidatePermit->getId());
        $this->result->addMessage('IRHP Candidate Permit Updated');

        return $this->result;
    }
}
