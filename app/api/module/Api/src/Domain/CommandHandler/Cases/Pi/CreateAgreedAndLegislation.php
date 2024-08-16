<?php

/**
 * Creates Pi with agreed and legislation info
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\Pi\Reason as ReasonEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreateAgreedAndLegislation as CreateCmd;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;

/**
 * Creates Pi with agreed and legislation info
 */
final class CreateAgreedAndLegislation extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Pi';

    /**
     * Creates a Pi with agreed and legislation
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /** @var CreateCmd $command */
        $types = $this->processTypes($command->getPiTypes());
        $reasons = $this->processReasons($command->getReasons());

        /** @var PresidingTcEntity $agreedByTc */
        $agreedByTc = $this->getRepo()->getReference(PresidingTcEntity::class, $command->getAgreedByTc());

        /** @var UserEntity $assignedCaseworker */
        $assignedCaseworker = $this->getRepo()->getReference(UserEntity::class, $command->getAssignedCaseworker());

        /** @var CasesEntity $case */
        $case = $this->getRepo()->getReference(CasesEntity::class, $command->getCase());

        /** @var RefData $decidedByTcRole */
        $decidedByTcRole = $this->getRepo()->getRefdataReference($command->getAgreedByTcRole());

        /** @var RefData $piStatus */
        $piStatus = $this->getRepo()->getRefdataReference(PiEntity::STATUS_REGISTERED);

        $agreedDate = \DateTime::createFromFormat('Y-m-d', $command->getAgreedDate());

        $isEcmsCase = $command->getIsEcmsCase() === 'Y' ? 1 : 0;

        $ecmsFirstReceivedDate = $command->getEcmsFirstReceivedDate() !== null ?
            \DateTime::createFromFormat('Y-m-d', $command->getEcmsFirstReceivedDate()) : null;
        $ecmsFirstReceivedDateToStore = $isEcmsCase ? $ecmsFirstReceivedDate : null;

        $pi = new PiEntity(
            $case,
            $agreedByTc,
            $decidedByTcRole,
            $types,
            $reasons,
            $agreedDate,
            $piStatus,
            $command->getComment(),
            $isEcmsCase,
            $assignedCaseworker,
            $ecmsFirstReceivedDateToStore
        );

        $this->getRepo()->save($pi);
        $result->addMessage('Pi created');
        $result->addId('Pi', $pi->getId());

        // generate all related SLA Target Dates
        $result->merge(
            $this->handleSideEffect(
                GenerateSlaTargetDateCmd::create(
                    [
                        'pi' => $pi->getId()
                    ]
                )
            )
        );

        return $result;
    }

    /**
     * Returns collection of reasons.
     *
     * @param array $reasons Reasons
     *
     * @return ArrayCollection
     */
    private function processReasons($reasons)
    {
        $result = new ArrayCollection();

        if (!empty($reasons)) {
            foreach ($reasons as $reason) {
                $result->add($this->getRepo()->getReference(ReasonEntity::class, $reason));
            }
        }

        return $result;
    }

    /**
     * Returns collection of types.
     *
     * @param array $types Types
     *
     * @return ArrayCollection
     */
    private function processTypes($types)
    {
        $result = new ArrayCollection();

        if (!empty($types)) {
            foreach ($types as $type) {
                $result->add($this->getRepo()->getRefdataReference($type));
            }
        }

        return $result;
    }
}
