<?php

/**
 * Create Stay
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Stay;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\CreateStay as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;

/**
 * Create Stay
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CreateStay extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Stay';

    /**
     * Creates Stay and associated entities
     *
     * @param CommandInterface $command DTO request
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $stay = $this->createStayObject($command);

        $this->getRepo()->save($stay);
        $result->addMessage('Stay created');

        $result->addId('stay', $stay->getId());

        return $result;
    }

    /**
     * Create the stay object
     *
     * @param Cmd $command DTO request
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     *
     * @return Stay
     */
    private function createStayObject(Cmd $command)
    {
        /** @var Cases $case */
        $case = $this->getRepo()->getReference(
            Cases::class,
            $command->getCase()
        );

        // If case doesn't have an appeal, raise exception
        if (
            !($case->hasAppeal()
            )
        ) {
            throw new ValidationException(
                ['appeal' => 'An appeal must exist against the case before a stay can be added']
            );
        }

        // If stay type already exists raise exception
        if (
            $case->hasStayType(
                $this->getRepo()->getRefdataReference($command->getStayType())
            )
        ) {
            throw new ValidationException(['stayType' => 'Stay of this type already exists against this case']);
        }

        $stay = new Stay(
            $this->getRepo()->getReference(Cases::class, $command->getCase()),
            $this->getRepo()->getRefdataReference($command->getStayType())
        );

        // We do this logic here because the Entity cannot be aware of any other
        // services.  We want to limit the processes we pass into Doctrine Entities
        // Repositories.   This is also in:
        // Api/src/Domain/CommandHandler/Cases/Hearing/UpdateStay.php
        $outcome = null;
        if (! empty($command->getOutcome())) {
            $outcome = $this->getRepo()->getRefdataReference(
                $command->getOutcome()
            );
        }

        $stay->values(
            $command->getRequestDate(),
            $command->getDecisionDate(),
            $outcome,
            $command->getNotes(),
            $command->getIsWithdrawn(),
            $command->getWithdrawnDate(),
            $command->getDvsaNotified()
        );

        return $stay;
    }
}
