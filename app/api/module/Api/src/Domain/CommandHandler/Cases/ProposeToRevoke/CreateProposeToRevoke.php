<?php

/**
 * Create ProposeToRevoke
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ProposeToRevoke;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Pi\Reason;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\CreateProposeToRevoke as Cmd;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;

/**
 * Create ProposeToRevoke
 */
final class CreateProposeToRevoke extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ProposeToRevoke';

    /**
     * Handle the command
     *
     * @param CommandInterface|Cmd $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        // create and save a record
        $proposeToRevoke = $this->createProposeToRevokeObject($command);
        $this->getRepo()->save($proposeToRevoke);

        $result = new Result();
        $result->addId('proposeToRevoke', $proposeToRevoke->getId());
        $result->addMessage('Revocation created successfully');

        // generate all related SLA Target Dates
        $result->merge(
            $this->handleSideEffect(
                GenerateSlaTargetDateCmd::create(
                    [
                        'proposeToRevoke' => $proposeToRevoke->getId()
                    ]
                )
            )
        );

        return $result;
    }

    /**
     * Create the ProposeToRevoke object
     *
     * @param CommandInterface|Cmd $command command
     *
     * @return ProposeToRevoke
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    private function createProposeToRevokeObject(Cmd $command)
    {
        $case = $this->getRepo()->getReference(Cases::class, $command->getCase());
        $reasons = array_map(
            fn($reasonId) => $this->getRepo()->getReference(Reason::class, $reasonId),
            $command->getReasons()
        );
        $presidingTc = $this->getRepo()->getReference(PresidingTc::class, $command->getPresidingTc());
        $ptrAgreedDate = new \DateTime($command->getPtrAgreedDate());

        $assignedCaseworker = $this->getRepo()->getReference(User::class, $command->getAssignedCaseworker());
        $proposeToRevoke = new ProposeToRevoke(
            $case,
            $reasons,
            $presidingTc,
            $ptrAgreedDate,
            $assignedCaseworker
        );

        if ($command->getClosedDate() !== null) {
            $proposeToRevoke->setClosedDate(new \DateTime($command->getClosedDate()));
        }

        if ($command->getComment() !== null) {
            $proposeToRevoke->setComment($command->getComment());
        }

        return $proposeToRevoke;
    }
}
