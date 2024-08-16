<?php

/**
 * Update Appeal
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Appeal as AppealEntity;
use Dvsa\Olcs\Api\Domain\Repository\Appeal as AppealRepo;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\UpdateAppeal as UpdateAppealCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Appeal
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class UpdateAppeal extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Appeal';

    /**
     * Updates Appeal and associated entities
     *
     * @param CommandInterface|UpdateAppealCmd $command command to update the appeal
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /**
         * @var AppealRepo   $repo
         * @var AppealEntity $appeal
         */
        $repo = $this->getRepo();
        $appeal = $repo->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $outcome = $command->getOutcome() !== null ? $repo->getRefdataReference($command->getOutcome()) : null;

        $appeal->update(
            $repo->getRefdataReference($command->getReason()),
            $command->getAppealDate(),
            $command->getAppealNo(),
            $command->getDeadlineDate(),
            $command->getOutlineGround(),
            $command->getHearingDate(),
            $command->getDecisionDate(),
            $command->getPapersDueDate(),
            $command->getPapersDueTcDate(),
            $command->getPapersSentDate(),
            $command->getPapersSentTcDate(),
            $command->getComment(),
            $outcome,
            $command->getIsWithdrawn(),
            $command->getWithdrawnDate(),
            $command->getDvsaNotified()
        );

        $this->getRepo()->save($appeal);
        $result->addMessage('Appeal updated');
        $result->addId('appeal', $appeal->getId());

        return $result;
    }
}
