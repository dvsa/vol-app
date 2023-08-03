<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Licence\TaxiPhvController;
use ZfcRbac\Service\AuthorizationService;

class TaxiPhvControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return TaxiPhvController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TaxiPhvController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $licenceLvaAdapter = $container->get(LicenceLvaAdapter::class);

        return new TaxiPhvController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $tableFactory,
            $scriptFactory,
            $translationHelper,
            $licenceLvaAdapter
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return TaxiPhvController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): TaxiPhvController
    {
        return $this->__invoke($serviceLocator, TaxiPhvController::class);
    }
}
