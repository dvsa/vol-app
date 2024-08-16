<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Schedule41\CreateS4;
use Dvsa\Olcs\Api\Domain\Command\LicenceOperatingCentre\AssociateS4;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\ApplicationOperatingCentre\CreateApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Command\Cases\ConditionUndertaking\CreateConditionUndertaking;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Class Schedule41
 *
 * Schedule 41 on an application, moves OC's from a licence to a new application.
 *
 * @package Dvsa\Olcs\Api\Domain\CommandHandler\Application
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class Schedule41 extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    /**
     * @param CommandInterface $command
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $licence = $this->getRepo()->getReference(Licence::class, $command->getLicence());
        $application = $this->getRepo()->getReference(Application::class, $command->getId());

        $result = new Result();

        $s4 = CreateS4::create(
            [
                'application' => $application,
                'licence' => $licence,
                'surrenderLicence' => $command->getSurrenderLicence(),
                'receivedDate' => new DateTime()
            ]
        );

        $s4Result = $this->handleSideEffect($s4);

        $result->merge($s4Result);

        $result->merge(
            $this->handleSideEffect(
                AssociateS4::create(
                    [
                        's4' => $s4Result->getId('s4'),
                        'licenceOperatingCentres' => $command->getOperatingCentres()
                    ]
                )
            )
        );

        foreach ($command->getOperatingCentres() as $operatingCentre) {
            /** @var LicenceOperatingCentre $licenceOperatingCentre */
            $licenceOperatingCentre = $this->getRepo()
                ->getReference(
                    LicenceOperatingCentre::class,
                    $operatingCentre
                );

            $result->merge(
                $this->handleSideEffect(
                    CreateApplicationOperatingCentre::create(
                        [
                            'application' => $application,
                            's4' => $s4Result->getId('s4'),
                            'operatingCentre' => $licenceOperatingCentre->getOperatingCentre(),
                            'action' => 'A',
                            'adPlaced' => ApplicationOperatingCentre::AD_POST,
                            'noOfVehiclesRequired' => $licenceOperatingCentre->getNoOfVehiclesRequired(),
                            'noOfTrailersRequired' => $licenceOperatingCentre->getNoOfTrailersRequired(),
                        ]
                    )
                )
            );
            $conditionUndertakings = $licenceOperatingCentre
                ->getOperatingCentre()
                ->getConditionUndertakings();

            /** @var ConditionUndertaking $conditionUndertaking */
            foreach ($conditionUndertakings as $conditionUndertaking) {
                if (is_null($conditionUndertaking->getLicence())) {
                    continue;
                }

                $result->merge(
                    $this->handleSideEffect(
                        CreateConditionUndertaking::create(
                            [
                                'licence' => null,
                                'application' => $application->getId(),
                                'operatingCentre' => $licenceOperatingCentre->getOperatingCentre()->getId(),
                                'conditionType' => $conditionUndertaking->getConditionType()->getId(),
                                'addedVia' => ConditionUndertaking::ADDED_VIA_APPLICATION,
                                'action' => 'A',
                                'attachedTo' => ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE,
                                'isDraft' => 'Y',
                                'isFulfilled' => 'N',
                                's4' => $s4Result->getId('s4'),
                                'notes' => $conditionUndertaking->getNotes()
                            ]
                        )
                    )
                );
            }
        }

        return $result;
    }
}
