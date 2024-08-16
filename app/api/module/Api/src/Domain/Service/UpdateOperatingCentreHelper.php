<?php

namespace Dvsa\Olcs\Api\Domain\Service;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Transfer\Command\Licence\UpdateOperatingCentres as UpdateLicenceOperatingCentres;
use Dvsa\Olcs\Transfer\Command\Application\UpdateOperatingCentres as UpdateApplicationOperatingCentres;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Psr\Container\ContainerInterface;

/**
 * @see \Dvsa\OlcsTest\Api\Domain\Service\UpdateOperatingCentreHelperTest
 */
class UpdateOperatingCentreHelper implements FactoryInterface
{
    protected $messages = [];

    public const ERR_OC_R_1 = 'ERR_OC_R_1'; // restricted-too-many
    public const ERR_OC_P_1 = 'ERR_OC_P_1'; // psv-lgvs
    public const ERR_OC_V_1 = 'ERR_OC_V_1'; // 1-operating-centre
    public const ERR_OC_V_2 = 'ERR_OC_V_2'; // too-low
    public const ERR_OC_V_3 = 'ERR_OC_V_3'; // too-high
    public const ERR_OC_V_4 = 'ERR_OC_V_4'; // no-operating-centre
    public const ERR_OC_T_1 = 'ERR_OC_T_1'; // 1-operating-centre
    public const ERR_OC_T_2 = 'ERR_OC_T_2'; // too-low
    public const ERR_OC_T_3 = 'ERR_OC_T_3'; // too-high
    public const ERR_OC_T_4 = 'ERR_OC_T_4'; // no-operating-centre
    public const ERR_OC_EA_EMPTY = 'ERR_OC_EA_EMPTY';
    public const ERR_OC_LGV_1 = 'ERR_OC_LGV_1'; // lgvs-not-supported-on-licence-type
    public const ERR_OC_LGV_2 = 'ERR_OC_LGV_2'; // no-lgvs

    /**
     * @var AuthorizationService
     */
    protected $authService;

    public function getMessages()
    {
        return $this->messages;
    }

    public function validateEnforcementArea($entity, $command)
    {
        $ea = $command->getEnforcementArea();

        if (
            $this->authService->isGranted(Permission::INTERNAL_USER)
            && $entity->getTrafficArea() !== null
            && empty($ea)
        ) {
            $this->addMessage('enforcementArea', self::ERR_OC_EA_EMPTY);
        }
    }

    /**
     * @param Licence|Application $entity
     * @param UpdateLicenceOperatingCentres|UpdateApplicationOperatingCentres $command
     */
    public function validateTotalAuthHgvVehicles($entity, $command, array $totals)
    {
        assert($command instanceof UpdateLicenceOperatingCentres || $command instanceof UpdateApplicationOperatingCentres);
        assert($entity instanceof Licence || $entity instanceof Application);

        if ($entity->mustHaveOperatingCentre() && $totals['noOfOperatingCentres'] === 0) {
            $this->addMessage('totAuthHgvVehicles', self::ERR_OC_V_4);
        }

        if ($totals['noOfOperatingCentres'] === 1 && $command->getTotAuthHgvVehicles() != $totals['minHgvVehicleAuth']) {
            $this->addMessage('totAuthHgvVehicles', self::ERR_OC_V_1);
        }

        if ($totals['noOfOperatingCentres'] >= 2) {
            if ($command->getTotAuthHgvVehicles() < $totals['minHgvVehicleAuth']) {
                $this->addMessage('totAuthHgvVehicles', self::ERR_OC_V_2);
            }

            if ($command->getTotAuthHgvVehicles() > $totals['maxHgvVehicleAuth']) {
                $this->addMessage('totAuthHgvVehicles', self::ERR_OC_V_3);
            }
        }
    }

    /**
     * @param Licence|Application $entity
     * @param UpdateLicenceOperatingCentres|UpdateApplicationOperatingCentres $command
     */
    public function validateTotalAuthLgvVehicles($entity, $command)
    {
        assert($command instanceof UpdateLicenceOperatingCentres || $command instanceof UpdateApplicationOperatingCentres);
        assert($entity instanceof Licence || $entity instanceof Application);

        if ($command->getTotAuthLgvVehicles() && !$entity->canHaveLgv()) {
            $this->addMessage('totAuthLgvVehicles', UpdateOperatingCentreHelper::ERR_OC_LGV_1);
        }

        if ($entity->mustHaveLgv() && !$command->getTotAuthLgvVehicles()) {
            $this->addMessage('totAuthLgvVehicles', UpdateOperatingCentreHelper::ERR_OC_LGV_2);
        }
    }

    /**
     * @param Licence|Application $entity
     * @param UpdateLicenceOperatingCentres|UpdateApplicationOperatingCentres $command
     * @param array $totals
     */
    public function validateTotalAuthTrailers($entity, $command, $totals)
    {
        if ($entity->mustHaveOperatingCentre() && $totals['noOfOperatingCentres'] === 0) {
            $this->addMessage('totAuthTrailers', self::ERR_OC_T_4);
        }

        if ($totals['noOfOperatingCentres'] === 1 && $command->getTotAuthTrailers() != $totals['minTrailerAuth']) {
            $this->addMessage('totAuthTrailers', self::ERR_OC_T_1);
        }

        if ($totals['noOfOperatingCentres'] >= 2) {
            if ($command->getTotAuthTrailers() < $totals['minTrailerAuth']) {
                $this->addMessage('totAuthTrailers', self::ERR_OC_T_2);
            }

            if ($command->getTotAuthTrailers() > $totals['maxTrailerAuth']) {
                $this->addMessage('totAuthTrailers', self::ERR_OC_T_3);
            }
        }
    }

    /**
     * @param Licence|Application $entity
     * @param UpdateLicenceOperatingCentres|UpdateApplicationOperatingCentres $command
     */
    public function validatePsv($entity, $command)
    {
        assert($command instanceof UpdateLicenceOperatingCentres || $command instanceof UpdateApplicationOperatingCentres);
        assert($entity instanceof Licence || $entity instanceof Application);
        if ($entity->isRestricted() && $command->getTotAuthHgvVehicles() > 2) {
            $this->addMessage('totAuthHgvVehicles', self::ERR_OC_R_1);
        }

        if (null !== $command->getTotAuthLgvVehicles()) {
            $this->addMessage('totAuthLgvVehicles', self::ERR_OC_P_1);
        }
    }

    public function addMessage($field, $messageCode, $message = null)
    {
        if ($message === null) {
            $message = $messageCode;
        }

        $this->messages[$field][] = [$messageCode => $message];
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->authService = $container->get(AuthorizationService::class);
        return $this;
    }
}
