<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\BusinessDetailsController;
use ZfcRbac\Identity\IdentityProviderInterface;
use ZfcRbac\Service\AuthorizationService;

class BusinessDetailsControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return BusinessDetailsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): BusinessDetailsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $tableFactory = $container->get(TableFactory::class);
        $stringHelper = $container->get(StringHelperService::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $fileUploadHelper = $container->get(FileUploadHelperService::class);

        return new BusinessDetailsController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $identityProvider,
            $tableFactory,
            $stringHelper,
            $fileUploadHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return BusinessDetailsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): BusinessDetailsController
    {
        return $this->__invoke($serviceLocator, BusinessDetailsController::class);
    }
}
