<?php

/**
 * Update ProposeToRevoke
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\ProposeToRevoke;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Cases\ProposeToRevoke;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Transfer\Command\Cases\ProposeToRevoke\UpdateProposeToRevokeSla as UpdateProposeToRevokeCommandSla;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;

/**
 * Update ProposeToRevokeSla
 */
final class UpdateProposeToRevokeSla extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ProposeToRevoke';

    /**
     * Handle the command
     *
     * @param CommandInterface|UpdateProposeToRevokeCommandSla $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ProposeToRevoke $proposeToRevoke */
        $proposeToRevoke = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $proposeToRevoke->setIsSubmissionRequiredForApproval($command->getIsSubmissionRequiredForApproval());

        $proposeToRevoke->setApprovalSubmissionIssuedDate($command->getApprovalSubmissionIssuedDate());

        $proposeToRevoke->setApprovalSubmissionReturnedDate($command->getApprovalSubmissionReturnedDate());

        $proposeToRevoke->setApprovalSubmissionPresidingTc(
            $this->getRepo()
                ->getReference(
                    PresidingTc::class,
                    $command->getApprovalSubmissionPresidingTc()
                )
        );

        $proposeToRevoke->setIorLetterIssuedDate($command->getIorLetterIssuedDate());

        $proposeToRevoke->setOperatorResponseDueDate($command->getOperatorResponseDueDate());

        $proposeToRevoke->setOperatorResponseReceivedDate($command->getOperatorResponseReceivedDate());

        $proposeToRevoke->setIsSubmissionRequiredForAction($command->getIsSubmissionRequiredForAction());

        $proposeToRevoke->setFinalSubmissionIssuedDate($command->getFinalSubmissionIssuedDate());

        $proposeToRevoke->setFinalSubmissionReturnedDate($command->getFinalSubmissionReturnedDate());

        $proposeToRevoke->setFinalSubmissionPresidingTc(
            $this->getRepo()
                ->getReference(
                    PresidingTc::class,
                    $command->getFinalSubmissionPresidingTc()
                )
        );

        $proposeToRevoke->setActionToBeTaken($this->getRepo()->getRefdataReference($command->getActionToBeTaken()));

        $proposeToRevoke->setRevocationLetterIssuedDate($command->getRevocationLetterIssuedDate());

        $proposeToRevoke->setNfaLetterIssuedDate($command->getNfaLetterIssuedDate());

        $proposeToRevoke->setWarningLetterIssuedDate($command->getWarningLetterIssuedDate());

        $proposeToRevoke->setPiAgreedDate($command->getPiAgreedDate());

        $proposeToRevoke->setOtherActionAgreedDate($command->getOtherActionAgreedDate());

        $this->getRepo()->save($proposeToRevoke);

        $result = new Result();
        $result->addId('proposeToRevoke', $proposeToRevoke->getId());
        $result->addMessage('Revocation Sla updated successfully');

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
