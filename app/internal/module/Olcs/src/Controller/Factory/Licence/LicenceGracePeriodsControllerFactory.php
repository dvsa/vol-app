<?php

namespace Olcs\Controller\Factory\Licence;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Licence\LicenceGracePeriodsController;
use ZfcRbac\Service\AuthorizationService;

class LicenceGracePeriodsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return LicenceGracePeriodsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LicenceGracePeriodsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $navigation = $container->get('navigation');

        return new LicenceGracePeriodsController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $tableFactory,
            $formHelper,
            $scriptFactory,
            $navigation
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LicenceGracePeriodsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): LicenceGracePeriodsController
    {
        return $this->__invoke($serviceLocator, LicenceGracePeriodsController::class);
    }
}
