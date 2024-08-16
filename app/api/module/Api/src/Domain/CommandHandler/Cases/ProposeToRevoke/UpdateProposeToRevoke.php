<?php

/**
 * Update ProposeToRevoke
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ProposeToRevoke;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\Pi\Reason;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevoke as UpdateProposeToRevokeCommand;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;

/**
 * Update ProposeToRevoke
 */
final class UpdateProposeToRevoke extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ProposeToRevoke';

    /**
     * Handle the command
     *
     * @param CommandInterface|UpdateProposeToRevokeCommand $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ProposeToRevoke $proposeToRevoke */
        $proposeToRevoke = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $reasons = array_map(
            fn($reasonId) => $this->getRepo()->getReference(Reason::class, $reasonId),
            $command->getReasons()
        );
        $presidingTc = $this->getRepo()->getReference(PresidingTc::class, $command->getPresidingTc());
        $ptrAgreedDate = new \DateTime($command->getPtrAgreedDate());

        $assignedCaseworker = $this->getRepo()->getReference(User::class, $command->getAssignedCaseworker());
        $proposeToRevoke->update(
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

        $this->getRepo()->save($proposeToRevoke);

        $result = new Result();
        $result->addId('proposeToRevoke', $proposeToRevoke->getId());
        $result->addMessage('Revocation updated successfully');

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
}
