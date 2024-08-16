<?php

/**
 * Revoke a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Application\DeleteApplication;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeasePsvDiscs;
use Dvsa\Olcs\Api\Domain\Command\LicenceStatusRule\RemoveLicenceStatusRulesForLicence;
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
 * Revoke a licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class Revoke extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Licence';

    public function handleCommand(CommandInterface $command)
    {
        /** @var Licence $licence */
        $licence = $this->getRepo()->fetchUsingId($command);
        $licence->setStatus($this->getRepo()->getRefdataReference(Licence::LICENCE_STATUS_REVOKED));
        $licence->setRevokedDate(new \DateTime());

        if ($licence->getGoodsOrPsv()->getId() === Licence::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $commandCeaseDiscs = CeaseGoodsDiscs::create(['licence' => $licence->getId()]);
        } else {
            $commandCeaseDiscs = CeasePsvDiscs::create(['licence' => $licence->getId()]);
        }

        $licence->setDecisions($this->buildArrayCollection(DecisionEntity::class, $command->getDecisions()));

        $result = new Result();
        $result->merge($this->handleSideEffect($commandCeaseDiscs));

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

        if ($command->getDeleteLicenceStatusRules()) {
            $result->merge(
                $this->handleSideEffect(
                    RemoveLicenceStatusRulesForLicence::create(
                        [
                        'licence' => $licence
                        ]
                    )
                )
            );
        }

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
                                DeleteApplication::create(
                                    [
                                        'id' => $application->getId()
                                    ]
                                )
                            )
                        );
                        break;
                    case Application::APPLICATION_STATUS_UNDER_CONSIDERATION:
                        $result->merge(
                            $this->handleSideEffect(
                                RefuseApplication::create(
                                    [
                                        'id' => $application->getId()
                                    ]
                                )
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
                        'reason' => WithdrawableInterface::WITHDRAWN_REASON_PERMITS_REVOKED,
                        'context' => EndIrhpApplicationsAndPermits::CONTEXT_REVOKE,
                    ]
                )
            )
        );

        // Exclude PSV Special Restricted licences
        if (!($licence->isPsv() && $licence->isSpecialRestricted())) {
            $result->merge($this->publish($licence));
        }

        $result->merge(
            $this->clearLicenceCacheSideEffect($licence->getId())
        );

        $this->getRepo()->save($licence);

        $result->addMessage("Licence ID {$licence->getId()} revoked");

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
