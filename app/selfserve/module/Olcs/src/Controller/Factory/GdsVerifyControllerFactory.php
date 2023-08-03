<?php

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\Cache\Storage\Adapter\Redis;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\GdsVerifyController;
use ZfcRbac\Service\AuthorizationService;

class GdsVerifyControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return GdsVerifyController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GdsVerifyController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $redis = $container->get(Redis::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new GdsVerifyController(
            $niTextTranslationUtil,
            $authService,
            $redis,
            $formHelper,
            $scriptFactory,
            $flashMessengerHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return GdsVerifyController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): GdsVerifyController
    {
        return $this->__invoke($serviceLocator, GdsVerifyController::class);
    }
}
