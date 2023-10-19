<?php

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Application\Controller\OperatingCentresController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Service\AuthorizationService;

class OperatingCentresControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return OperatingCentresController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): OperatingCentresController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $formServiceManager = $container->get(FormServiceManager::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $variationLvaService = $container->get(VariationLvaService::class);
        $restrictionHelper = $container->get(RestrictionHelperService::class);
        $stringHelper = $container->get(StringHelperService::class);
        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new OperatingCentresController(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $translationHelper,
            $scriptFactory,
            $variationLvaService,
            $restrictionHelper,
            $stringHelper,
            $uploadHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OperatingCentresController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): OperatingCentresController
    {
        return $this->__invoke($serviceLocator, OperatingCentresController::class);
    }
}
