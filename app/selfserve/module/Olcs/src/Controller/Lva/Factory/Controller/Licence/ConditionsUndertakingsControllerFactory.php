<?php

namespace Olcs\Controller\Lva\Factory\Controller\Licence;

use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Review\LicenceConditionsUndertakingsReviewService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Olcs\Controller\Lva\Licence\ConditionsUndertakingsController;
use ZfcRbac\Service\AuthorizationService;

class ConditionsUndertakingsControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ConditionsUndertakingsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConditionsUndertakingsController
    {
        $container = method_exists($container, 'getServiceLocator') ? $container->getServiceLocator() : $container;

        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $licenceCUReviewService = $container->get(LicenceConditionsUndertakingsReviewService::class);
        $guidanceHelper = $container->get(GuidanceHelperService::class);

        return new ConditionsUndertakingsController(
            $niTextTranslationUtil,
            $authService,
            $licenceCUReviewService,
            $guidanceHelper
        );
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ConditionsUndertakingsController
     */
    public function createService(ServiceLocatorInterface $serviceLocator): ConditionsUndertakingsController
    {
        return $this->__invoke($serviceLocator, ConditionsUndertakingsController::class);
    }
}
