<?php

/**
 * Schedule41Refuse.php
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\DeleteApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\DeleteConditionUndertakingS4;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\RefuseS4;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Schedule41Refuse Command Handler
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class Schedule41Refuse extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchById($command->getId());

        $criteria = Criteria::create();
        $criteria->where(
            $criteria->expr()->eq('outcome', null)
        );

        $s4s = $application->getS4s()->matching($criteria);

        $result = new Result();

        /* @var $s4 \Dvsa\Olcs\Api\Entity\Application\S4 */
        foreach ($s4s as $s4) {
            $result->merge(
                $this->handleSideEffect(
                    RefuseS4::create(
                        [
                            'id' => $s4->getId(),
                        ]
                    )
                )
            );

            $result->merge(
                $this->handleSideEffect(
                    DeleteApplicationOperatingCentre::create(
                        [
                            's4' => $s4->getId()
                        ]
                    )
                )
            );

            $result->merge(
                $this->handleSideEffect(
                    DeleteConditionUndertakingS4::create(
                        [
                            's4' => $s4->getId()
                        ]
                    )
                )
            );
        }

        return $result;
    }
}
