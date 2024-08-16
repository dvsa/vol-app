<?php

/**
 * Update Stay
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Hearing;

use Doctrine\ORM\Query;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\Stay;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\UpdateStay as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Update Stay
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class UpdateStay extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Stay';

    /**
     * Updates Stay and associated entities
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
        $result->addMessage('Stay updated');
        $result->addId('stay', $stay->getId());

        return $result;
    }

    /**
     * Update the stay object
     *
     * @param Cmd $command DTO request
     *
     * @return Stay
     */
    private function createStayObject(Cmd $command)
    {
        /** @var Stay $stay */
        $stay = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        // We do this logic here because the Entity cannot be aware of any other
        // services.  We want to limit the processes we pass into Doctrine Entities
        // Repositories.   This is also in:
        // Api/src/Domain/CommandHandler/Cases/Hearing/CreateStay.php
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
