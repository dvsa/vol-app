<?php

/**
 * Update pi hearing
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as PiHearingEntity;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreateHearing as CreateHearingCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Publication\PiHearing as PublishHearingCmd;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask as CreateTaskCmd;
use Dvsa\Olcs\Api\Domain\Command\System\GenerateSlaTargetDate as GenerateSlaTargetDateCmd;
use Doctrine\ORM\Query;

/**
 * Update pi hearing
 */
final class UpdateHearing extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'PiHearing';

    /**
     * Update pi hearing
     *
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var CreateHearingCmd $command */
        $result = new Result();

        /** @var PiHearingEntity $piHearing */
        $piHearing = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        /** @var PresidingTcEntity $presidingTc */
        $presidingTc = $this->getRepo()->getReference(PresidingTcEntity::class, $command->getPresidingTc());

        /** @var RefData $presidingTcRole */
        $presidedByRole = $this->getRepo()->getRefdataReference($command->getPresidedByRole());

        $hearingDate = new \DateTime($command->getHearingDate());

        if ($command->getVenue() === null) {
            $venue = null;
        } else {
            /** @var VenueEntity $venue */
            $venue = $this->getRepo()->getReference(VenueEntity::class, $command->getVenue());
        }

        $isAdjourned = $command->getIsAdjourned();
        $isCancelled = $command->getIsCancelled();

        $piHearing->update(
            $presidingTc,
            $presidedByRole,
            $hearingDate,
            $venue,
            $command->getVenueOther(),
            $command->getWitnesses(),
            $command->getDrivers(),
            $isCancelled,
            $command->getCancelledDate(),
            $command->getCancelledReason(),
            $isAdjourned,
            $command->getAdjournedDate(),
            $command->getAdjournedReason(),
            $command->getDetails()
        );

        $piHearing->setIsFullDay($command->getIsFullDay());
        if ($command->getIsFullDay() !== 'N' && $command->getIsFullDay() !== 'Y') {
            $piHearing->setIsFullDay(null);
        }

        $this->getRepo()->save($piHearing);
        $id = $piHearing->getId();
        $result->addMessage('Pi Hearing updated');
        $result->addId('PiHearing', $id);

        if (($isAdjourned === 'N' || $isCancelled === 'N') && $command->getPublish() === 'Y') {
            $result->merge($this->handleSideEffect($this->createPublishCommand($id, $command)));
        }

        if ($isAdjourned === 'Y') {
            $result->merge($this->handleSideEffect($this->createTaskCommand($piHearing)));
        }

        // generate all related SLA Target Dates
        $result->merge(
            $this->handleSideEffect(
                GenerateSlaTargetDateCmd::create(
                    [
                        'pi' => $piHearing->getPi()->getId()
                    ]
                )
            )
        );

        return $result;
    }

    /**
     * @return CreateTaskCmd
     */
    private function createTaskCommand(PiHearingEntity $hearing)
    {
        $currentUser = $this->getCurrentUser();

        $actionDate = date(
            'Y-m-d',
            mktime(date("H"), date("i"), date("s"), date("n"), date("j") + 7, date("Y"))
        );

        $case = $hearing->getPi()->getCase();

        $data = [
            'category' => TaskEntity::CATEGORY_COMPLIANCE,
            'subCategory' => TaskEntity::SUB_CATEGORY_HEARINGS_APPEALS,
            'description' => 'Verify adjournment of case',
            'actionDate' => $actionDate,
            'urgent' => 'Y',
            'assignedToUser' => $currentUser->getId(),
            'assignedToTeam' => $currentUser->getTeam()->getId(),
            'case' => $case->getId(),
        ];

        if ($case->isTm()) {
            $data['transportManager'] = $case->getTransportManager()->getId();
        } else {
            $data['licence'] = $case->getLicence()->getId();
        }

        return CreateTaskCmd::create($data);
    }

    /**
     * @param int              $id
     * @param CreateHearingCmd $command
     * @return PublishHearingCmd
     */
    private function createPublishCommand($id, $command)
    {
        return PublishHearingCmd::create(
            [
                'id' => $id,
                'pubType' => [$command->getPubType()],
                'trafficAreas' => $command->getTrafficAreas(),
                'text2' => $command->getDetails()
            ]
        );
    }
}
