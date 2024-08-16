<?php

/**
 * Create Appeal
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Appeal;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\CreateAppeal as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create Appeal
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CreateAppeal extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Appeal';

    /**
     * Creates Appeal and associated entities
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $appeal = $this->createAppealObject($command);

        $this->getRepo()->save($appeal);
        $result->addMessage('Appeal created');

        $result->addId('appeal', $appeal->getId());

        return $result;
    }

    /**
     * Create the appeal object
     *
     * @return Appeal
     */
    private function createAppealObject(Cmd $command)
    {
        // If an appeal already exists, raise exception
        if (
            $this->getRepo()->getReference(
                Cases::class,
                $command->getCase()
            )->hasAppeal()
        ) {
            throw new ValidationException(['appeal' => 'An appeal already exists against this case']);
        }
        $appeal = new Appeal($command->getAppealNo());
        $appeal->setCase($this->getRepo()->getReference(Cases::class, $command->getCase()));

        if ($command->getDeadlineDate() !== null) {
            $appeal->setDeadlineDate(new \DateTime($command->getDeadlineDate()));
        }

        $appeal->setAppealDate(new \DateTime($command->getAppealDate()));
        $appeal->setReason($this->getRepo()->getRefdataReference($command->getReason()));

        if ($command->getOutlineGround() !== null) {
            $appeal->setOutlineGround($command->getOutlineGround());
        }

        if ($command->getHearingDate() !== null) {
            $appeal->setHearingDate(new \DateTime($command->getHearingDate()));
        }

        if ($command->getDecisionDate() !== null) {
            $appeal->setDecisionDate(new \DateTime($command->getDecisionDate()));
        }

        if ($command->getPapersDueDate() !== null) {
            $appeal->setPapersDueDate(new \DateTime($command->getPapersDueDate()));
        }

        if ($command->getPapersDueTcDate() !== null) {
            $appeal->setPapersDueTcDate(new \DateTime($command->getPapersDueTcDate()));
        }

        if ($command->getPapersSentDate() !== null) {
            $appeal->setPapersSentDate(new \DateTime($command->getPapersSentDate()));
        }

        if ($command->getPapersSentTcDate() !== null) {
            $appeal->setPapersSentTcDate(new \DateTime($command->getPapersSentTcDate()));
        }

        if ($command->getComment() !== null) {
            $appeal->setComment($command->getComment());
        }

        if ($command->getOutcome() !== null) {
            $appeal->setOutcome($this->getRepo()->getRefdataReference($command->getOutcome()));
        }

        if ($command->getIsWithdrawn() === 'Y') {
            if ($command->getWithdrawnDate() !== null) {
                $appeal->setWithdrawnDate(new \DateTime($command->getWithdrawnDate()));
            }
        } else {
            $appeal->setWithdrawnDate(null);
        }

        $appeal->setDvsaNotified($command->getDvsaNotified());

        return $appeal;
    }
}
