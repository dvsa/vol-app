<?php

/**
 * Schedule41Cancel.php
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\DeleteApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\DeleteConditionUndertakingS4;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\CancelS4;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\S4;

/**
 * Schedule41Cancel Command Handler
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Schedule41Cancel extends AbstractCommandHandler
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Application $application */
        $application = $this->getRepo()->fetchById($command->getId());

        /* @var $s4 S4 */
        foreach ($application->getS4s() as $s4) {
            // if NOT empty or approved then continue
            if (!($s4->getOutcome() === null || $s4->getOutcome()->getId() === S4::STATUS_APPROVED)) {
                continue;
            }

            $this->result->merge(
                $this->handleSideEffect(
                    CancelS4::create(
                        [
                            'id' => $s4->getId(),
                        ]
                    )
                )
            );

            $this->result->merge(
                $this->handleSideEffect(
                    DeleteApplicationOperatingCentre::create(
                        [
                            's4' => $s4->getId()
                        ]
                    )
                )
            );

            $this->result->merge(
                $this->handleSideEffect(
                    DeleteConditionUndertakingS4::create(
                        [
                            's4' => $s4->getId()
                        ]
                    )
                )
            );
        }

        return $this->result;
    }
}
