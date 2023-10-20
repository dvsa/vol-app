<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Licence\VariationController;
use Olcs\Service\Processing\CreateVariationProcessingService;
use ZfcRbac\Service\AuthorizationService;

class VariationControllerFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  $requestedName
     * @param  array|null         $options
     * @return VariationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): VariationController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $processingCreateVariation = $container->get(CreateVariationProcessingService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);

        return new VariationController(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $processingCreateVariation,
            $formHelper,
            $flashMessengerHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return VariationController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): VariationController
    {
        return $this->__invoke($serviceLocator, VariationController::class);
    }
}
