<?php

namespace Olcs\Controller\Factory;

use Common\Service\Table\DataMapper\DashboardTmApplications;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\DashboardController;
use Olcs\Service\Processing\DashboardProcessingService;
use LmcRbacMvc\Service\AuthorizationService;

class DashboardControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return DashboardController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DashboardController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $session = $container->get(Session::class);
        $dashboardProcessing = $container->get(DashboardProcessingService::class);
        $dataMapper = $container->get(DashboardTmApplications::class);
        $tableFactory = $container->get(TableFactory::class);

        return new DashboardController(
            $niTextTranslationUtil,
            $authService,
            $session,
            $dashboardProcessing,
            $dataMapper,
            $tableFactory
        );
    }
}
