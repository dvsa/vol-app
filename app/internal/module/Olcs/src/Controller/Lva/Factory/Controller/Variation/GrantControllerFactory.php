<?php

namespace Olcs\Controller\Lva\Factory\Controller\Variation;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Variation\GrantController;
use ZfcRbac\Service\AuthorizationService;

class GrantControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return GrantController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): GrantController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $navigation = $container->get('Navigation');

        return new GrantController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formHelper,
            $scriptFactory,
            $translationHelper,
            $stringHelper,
            $navigation
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return GrantController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): GrantController
    {
        return $this->__invoke($serviceLocator, GrantController::class);
    }
}
