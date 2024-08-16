<?php

/**
 * Create ConditionUndertaking
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Application\S4;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\CreateConditionUndertaking as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;

/**
 * Create ConditionUndertaking
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
final class CreateConditionUndertaking extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ConditionUndertaking';

    protected $extraRepos = ['Cases', 'Licence', 'Application', 'OperatingCentre'];

    /**
     * Creates ConditionUndertaking
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $conditionUndertaking = $this->createConditionUndertakingObject($command);

        $this->getRepo()->save($conditionUndertaking);
        $result->addMessage('ConditionUndertaking created');

        return $result;
    }

    /**
     * Create the ConditionUndertaking object
     *
     * @param Cmd $command
     * @return ConditionUndertaking
     */
    private function createConditionUndertakingObject($command)
    {
        /* @var $command Cmd */
        $conditionUndertaking = new ConditionUndertaking(
            $this->getRepo()->getRefdataReference($command->getConditionType()),
            $command->getIsFulfilled(),
            (method_exists($command, 'getIsDraft') ? $command->getIsDraft() : "N")
        );

        if (!is_null($command->getCase())) {
            $case = $this->getRepo('Cases')->fetchById($command->getCase());
            $conditionUndertaking->setCase($case);
        }

        $application = $this->getRepo('Application')->fetchById($command->getApplication());
        $conditionUndertaking->setApplication($application);

        if (!is_null($command->getLicence())) {
            $licence = $this->getRepo('Licence')->fetchById($command->getLicence());
            $conditionUndertaking->setLicence($licence);
        }

        if (method_exists($command, 'getS4')) {
            $s4 = $this->getRepo()->getReference(S4::class, $command->getS4());
            $conditionUndertaking->setS4($s4);
        }

        $conditionUndertaking->setAttachedTo($this->getRepo()->getRefdataReference($command->getAttachedTo()));
        $conditionUndertaking->setAddedVia($this->getRepo()->getRefdataReference($command->getAddedVia()));
        $conditionUndertaking->setNotes($command->getNotes());
        $conditionUndertaking->setAction($command->getAction());

        $conditionUndertaking = $this->setAttachedToProperties($conditionUndertaking, $command);

        return $conditionUndertaking;
    }

    /**
     * Sets the AttachedTo and if required the Operating Centre
     *
     * @param Cmd $command
     * @return ConditionUndertaking
     */
    private function setAttachedToProperties(ConditionUndertaking $conditionUndertaking, $command)
    {
        if ($command->getAttachedTo() == ConditionUndertaking::ATTACHED_TO_LICENCE) {
            $conditionUndertaking->setAttachedTo(
                $this->getRepo()->getRefdataReference(
                    ConditionUndertaking::ATTACHED_TO_LICENCE
                )
            );
        } else {
            $operatingCentre = $this->getRepo()
                ->getReference(OperatingCentre::class, $command->getOperatingCentre());
            $conditionUndertaking->setOperatingCentre($operatingCentre);
            $conditionUndertaking->setAttachedTo(
                $this->getRepo()->getRefdataReference(
                    ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE
                )
            );
        }
        return $conditionUndertaking;
    }
}
