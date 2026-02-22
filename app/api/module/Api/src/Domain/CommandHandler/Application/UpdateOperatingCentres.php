<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees as HandleOcVariationFeesCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Service\UpdateOperatingCentreHelper;
use Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\UpdateOperatingCentres as Cmd;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateTrafficArea;
use Dvsa\Olcs\Api\Domain\Service\VariationOperatingCentreHelper;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Psr\Container\ContainerInterface;

/**
 * @see \Dvsa\OlcsTest\Api\Domain\CommandHandler\Application\UpdateOperatingCentresTest
 * @see \Dvsa\Olcs\Transfer\Command\Application\UpdateOperatingCentres
 */
final class UpdateOperatingCentres extends AbstractCommandHandler implements TransactionedInterface
{
    public const string ERR_OC_TA_1 = 'ERR_OC_TA_1'; // select-traffic-area-error
    public const string ERR_OC_CL_1 = 'ERR_OC_CL_1'; //community-licences-too-many

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['ApplicationOperatingCentre'];

    /**
     * @var VariationOperatingCentreHelper
     */
    private $variationHelper;

    /**
     * @var UpdateOperatingCentreHelper
     */
    private $updateHelper;

    /**
     * @var \Dvsa\Olcs\Api\Domain\Service\TrafficAreaValidator
     */
    private $trafficAreaValidator;

    private $totals;

    /**
     * @param Cmd $command
     * @return Result
     * @throws ValidationException
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        assert($command instanceof Cmd);

        /** @var Application $application */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $this->validate($application, $command);

        if (! $application->isPsv()) {
            $application->setTotAuthTrailers($command->getTotAuthTrailers());
            $application->updateTotAuthLgvVehicles($command->getTotAuthLgvVehicles());
        }

        $application->updateTotAuthHgvVehicles($command->getTotAuthHgvVehicles());

        // For new apps we are also potentially updating the TA, EA and community licences
        if ($application->isNew()) {
            if ($application->canHaveCommunityLicences()) {
                $application->setTotCommunityLicences($command->getTotCommunityLicences());
            }

            // only set Traffic Area if not a partial OR partial action is add
            if (!$command->getPartial() || $command->getPartialAction() == 'add') {
                if ($command->getTrafficArea() !== null) {
                    $data = [
                        'id' => $application->getLicence()->getId(),
                        'version' => $application->getLicence()->getVersion(),
                        'trafficArea' => $command->getTrafficArea()
                    ];

                    $this->result->merge($this->handleSideEffect(UpdateTrafficArea::create($data)));
                }

                if ($command->getEnforcementArea() !== null) {
                    $application->getLicence()->setEnforcementArea(
                        $this->getRepo()->getReference(EnforcementArea::class, $command->getEnforcementArea())
                    );
                }
            }
        } elseif ($application->getTrafficArea() !== null) {
            $application->getLicence()->setEnforcementArea(
                $this->getRepo()->getReference(EnforcementArea::class, $command->getEnforcementArea())
            );
        }

        $this->getRepo()->save($application);
        $this->result->addMessage('Application record updated');

        $data = ['id' => $application->getId(), 'section' => 'operatingCentres'];
        $this->result->merge($this->handleSideEffect(UpdateApplicationCompletionCmd::create($data)));

        if ($application->isVariation()) {
            $this->result->merge(
                $this->handleSideEffect(HandleOcVariationFeesCmd::create(['id' => $application->getId()]))
            );
        }

        return $this->result;
    }

    protected function validate(Application $application, Cmd $command)
    {
        // Check if we are missing the traffic area value, when it is applicable
        if (
            $application->isNew()
            && $command->getPartial()
            && $command->getPartialAction() == 'add'
            && $application->getLicence()->getTrafficArea() === null
        ) {
            $trafficArea = $command->getTrafficArea();

            if (empty($trafficArea) && $application->getOperatingCentres()->count() >= 1) {
                $this->updateHelper->addMessage('trafficArea', self::ERR_OC_TA_1);
            }
        }

        // if NOT partial or add, then validate the Traffic Area
        if (!$command->getPartial() || $command->getPartialAction() == 'add') {
            if ($application->isNew()) {
                $trafficAreaId = null;
                if ($command->getTrafficArea()) {
                    $trafficAreaId = $command->getTrafficArea();
                } elseif ($application->getTrafficArea()) {
                    $trafficAreaId = $application->getTrafficArea()->getId();
                }
                if ($trafficAreaId) {
                    $message = $this->trafficAreaValidator->validateForSameTrafficAreas($application, $trafficAreaId);
                    if (is_array($message)) {
                        $this->updateHelper->addMessage('trafficArea', key($message), current($message));
                    }
                }
            }
        }
        if (!$command->getPartial()) {
            if ($this->shouldValidateEnforcementArea($application)) {
                $this->updateHelper->validateEnforcementArea($application, $command);
            }

            $totals = $this->getTotals($application);

            if ($application->isPsv()) {
                $this->updateHelper->validatePsv($application, $command);
            } else {
                $this->updateHelper->validateTotalAuthTrailers($application, $command, $totals);
                $this->updateHelper->validateTotalAuthLgvVehicles($application, $command);
            }

            if (
                $application->canHaveCommunityLicences()
                && $command->getTotCommunityLicences() > ($command->getTotAuthHgvVehicles() + $command->getTotAuthLgvVehicles())
            ) {
                $this->updateHelper->addMessage('totCommunityLicences', self::ERR_OC_CL_1);
            }

            $this->updateHelper->validateTotalAuthHgvVehicles($application, $command, $totals);
        }

        $messages = $this->updateHelper->getMessages();

        if (!empty($messages)) {
            throw new ValidationException($messages);
        }
    }

    protected function shouldValidateEnforcementArea(Application $application)
    {
        if ($application->isVariation()) {
            return !$application->getOperatingCentres()->isEmpty()
                || !$application->getLicence()->getOperatingCentres()->isEmpty();
        }

        return !$application->getOperatingCentres()->isEmpty();
    }

    protected function getTotals(Application $application)
    {
        if ($this->totals !== null) {
            return $this->totals;
        }

        if ($application->isVariation()) {
            $aocs = $this->variationHelper->getListDataForApplication($application);
        } else {
            $aocs = $this->getRepo('ApplicationOperatingCentre')
                ->fetchByApplicationIdForOperatingCentres($application->getId());
        }

        $this->totals['noOfOperatingCentres'] = 0;
        $this->totals['minHgvVehicleAuth'] = 0;
        $this->totals['maxHgvVehicleAuth'] = 0;
        $this->totals['minTrailerAuth'] = 0;
        $this->totals['maxTrailerAuth'] = 0;

        foreach ($aocs as $aoc) {
            if (
                $application->isVariation()
                && in_array($aoc['action'], ['D', 'C'])
            ) {
                continue;
            }

            $this->totals['noOfOperatingCentres']++;

            $this->totals['minHgvVehicleAuth'] = max([$this->totals['minHgvVehicleAuth'], $aoc['noOfVehiclesRequired']]);
            $this->totals['minTrailerAuth'] = max([$this->totals['minTrailerAuth'], $aoc['noOfTrailersRequired']]);

            $this->totals['maxHgvVehicleAuth'] += (int) $aoc['noOfVehiclesRequired'];
            $this->totals['maxTrailerAuth'] += (int) $aoc['noOfTrailersRequired'];
        }

        return $this->totals;
    }
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $fullContainer = $container;

        $this->variationHelper = $container->get('VariationOperatingCentreHelper');
        $this->updateHelper = $container->get('UpdateOperatingCentreHelper');
        $this->trafficAreaValidator = $container->get('TrafficAreaValidator');
        return parent::__invoke($fullContainer, $requestedName, $options);
    }
}
