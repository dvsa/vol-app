<?php

/**
 * Surrender a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Application\DeleteApplication;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeasePsvDiscs;
use Dvsa\Olcs\Api\Domain\Command\LicenceVehicle\RemoveLicenceVehicle;
use Dvsa\Olcs\Api\Domain\Command\Licence\EndIrhpApplicationsAndPermits;
use Dvsa\Olcs\Api\Domain\Command\Licence\ReturnAllCommunityLicences as ReturnComLics;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Tm\DeleteTransportManagerLicence;
use Dvsa\Olcs\Api\Domain\Command\Variation\EndInterim;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Pi\Decision as DecisionEntity;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\Olcs\Transfer\Command\Application\RefuseApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Surrender a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class Surrender extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    /**
     * @see Dvsa\Olcs\Api\Domain\CommandHandler\Licence\ProcessContinuationNotSought::createDiscsCommand()
     */
    public function handleCommand(CommandInterface $command)
    {
        /* @var $licence Licence */
        $licence = $this->getRepo()->fetchUsingId($command);

        if ($command->isTerminated() == true) {
            $status = Licence::LICENCE_STATUS_TERMINATED;
        } else {
            $status = Licence::LICENCE_STATUS_SURRENDERED;
        }

        $licence->setStatus($this->getRepo()->getRefdataReference($status));
        $surrenderDate = $command->getSurrenderDate() !== null ? new \DateTime($command->getSurrenderDate()) : new \DateTime('now');
        $licence->setSurrenderedDate($surrenderDate);
        $licence->setDecisions($this->buildArrayCollection(DecisionEntity::class, $command->getDecisions()));

        $result = new Result();

        if ($licence->isGoods()) {
            $dto = CeaseGoodsDiscs::create(['licence' => $licence->getId()]);
        } else {
            $dto = CeasePsvDiscs::create(['licence' => $licence->getId()]);
        }

        $result->merge($this->handleSideEffect($dto));

        $result->merge($this->handleSideEffect(RemoveLicenceVehicle::create(['licence' => $licence->getId()])));

        $result->merge(
            $this->handleSideEffect(
                DeleteTransportManagerLicence::create(
                    [
                        'licence' => $licence
                    ]
                )
            )
        );

        $communityLicences = $licence->getCommunityLics()->toArray();
        if (!empty($communityLicences)) {
            $result->merge(
                $this->handleSideEffect(
                    ReturnComLics::create(
                        [
                            'id' => $licence->getId(),
                        ]
                    )
                )
            );
        }

        $result->merge($this->handleSideEffect(EndInterim::create(['licenceId' => $licence->getId()])));

        foreach ($licence->getApplications() as $application) {
            if ($application->getIsVariation()) {
                switch ($application->getStatus()->getId()) {
                    case Application::APPLICATION_STATUS_NOT_SUBMITTED:
                        $result->merge(
                            $this->handleSideEffect(
                                DeleteApplication::create(['id' => $application->getId()])
                            )
                        );
                        break;
                    case Application::APPLICATION_STATUS_UNDER_CONSIDERATION:
                        $result->merge(
                            $this->handleSideEffect(
                                RefuseApplication::create(['id' => $application->getId()])
                            )
                        );
                        break;
                    default:
                        break;
                }
            }
        }

        $result->merge(
            $this->handleSideEffect(
                EndIrhpApplicationsAndPermits::create(
                    [
                        'id' => $licence->getId(),
                        'reason' => WithdrawableInterface::WITHDRAWN_REASON_BY_USER,
                        'context' => EndIrhpApplicationsAndPermits::CONTEXT_SURRENDER,
                    ]
                )
            )
        );

        if (
            !($licence->getStatus()->getId() === Licence::LICENCE_STATUS_TERMINATED &&
                $licence->isPsv() &&
                $licence->isSpecialRestricted())
        ) {
            $result->merge($this->publish($licence));
        }

        $result->merge(
            $this->clearLicenceCacheSideEffect($licence->getId())
        );

        $this->getRepo()->save($licence);
        $result->addMessage("Licence ID {$licence->getId()} surrendered");

        return $result;
    }

    /**
     * Publish the licence
     *
     *
     * @return Result
     */
    private function publish(Licence $licence)
    {
        return $this->handleSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Publication\Licence::create(
                ['id' => $licence->getId()]
            )
        );
    }
}
